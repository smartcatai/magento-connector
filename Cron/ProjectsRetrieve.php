<?php
/**
 * SmartCat Translate Connector
 * Copyright (C) 2017 SmartCat
 * 
 * This file is part of SmartCat/Connector.
 * 
 * SmartCat/Connector is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace SmartCat\Connector\Cron;

use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManager;
use SmartCat\Client\Model\ProjectModel;
use SmartCat\Client\SmartCat;
use SmartCat\Connector\Api\Data\ProjectInterface;
use SmartCat\Connector\Helper\ErrorHandler;
use SmartCat\Connector\Model\ProfileRepository;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectRepository;
use SmartCat\Connector\Module;
use SmartCat\Connector\Service\ConnectorService;
use Magento\Catalog\Model\ProductRepository;
use SmartCat\Connector\Model\ProjectProductRepository;
use SmartCat\Connector\Service\FileService;
use \Throwable;

class ProjectsRetrieve
{
    /** @var SmartCat */
    private $smartCatService;
    private $profileRepository;
    private $projectRepository;
    private $searchCriteriaBuilder;
    private $fileService;
    private $productRepository;
    private $projectProductRepository;
    private $storeManager;
    private $errorHandler;

    public function __construct(
        ErrorHandler $errorHandler,
        ProfileRepository $profileRepository,
        ProjectRepository $projectRepository,
        ProductRepository $productRepository,
        ProjectProductRepository $projectProductRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ConnectorService $connectorService,
        StoreManager $storeManager,
        FileService $fileService
    ) {
        $this->errorHandler = $errorHandler;
        $this->smartCatService = $connectorService->getService();
        $this->projectRepository = $projectRepository;
        $this->profileRepository = $profileRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->fileService = $fileService;
        $this->projectProductRepository = $projectProductRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $smartCatProject = null;
        $projectManager = $this->smartCatService->getProjectManager();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(Project::STATUS, [Project::STATUS_CREATED, Project::STATUS_IN_PROGRESS], "in")
            ->create();

        try {
            $projects = $this->projectRepository->getList($searchCriteria)->getItems();
        } catch (LocalizedException $e) {
            $this->errorHandler->handleError($e, "Error occurred");
            return;
        }

        /** @var Project $project */
        foreach ($projects as $project) {
            try {
                $smartCatProject = $projectManager->projectGet($project->getGuid());
            } catch (Throwable $e) {
                $this->errorHandler->handleProjectError($e, $project,"SmartCat API Error");
                continue;
            }

            if ($smartCatProject->getStatus() == Project::STATUS_COMPLETED) {
                try {
                    $exportDocumentTaskModel = $this->getRequestDocuments($smartCatProject);
                    $zipPath = $this->saveDocuments($exportDocumentTaskModel->getId(), $project);

                    if (!$zipPath) {
                        continue;
                    }
                    
                    $this->extractDocuments($project->getGuid(), $zipPath);
                } catch (Throwable $e) {
                    $this->errorHandler->handleProjectError($e, $project,"SmartCat API Error");
                    continue;
                }

                /** @var StoreInterface[] $stores */
                $stores = $this->storeManager->getStores(true, true);

                foreach ($smartCatProject->getTargetLanguages() as $index => $targetLanguage) {
                    if (!isset($stores[$targetLanguage])) {
                        $this->errorHandler->logError("StoreView with code '$targetLanguage' not exists. Continue.");
                        continue;
                    }

                    $projectProductSearchCriteria = $this->searchCriteriaBuilder
                        ->addFilter(Project::PROJECT_ID, $project->getProjectId())
                        ->create();

                    $projectProducts = $this->projectProductRepository->getList($projectProductSearchCriteria)->getItems();

                    foreach ($projectProducts as $projectProduct) {
                        try {
                            $product = $this->productRepository->getById(
                                $projectProduct->getProductId(),
                                false,
                                $stores[$targetLanguage]->getId()
                            );
                            $this->setAttributes($product, $project, $targetLanguage);
                            $this->productRepository->save($product);
                        } catch (Throwable $e) {
                            $this->errorHandler->handleError($e, "SmartCat Product Error");
                            continue;
                        }
                    }
                }
            }

            $project->setStatus($smartCatProject->getStatus());

            try {
                $this->projectRepository->save($project);
            } catch (LocalizedException $e) {
                $this->errorHandler->handleError($e, "Error occurred");
                return;
            }
        }
    }

    /**
     * @param ProjectModel $model
     * @return \Psr\Http\Message\ResponseInterface|\SmartCat\Client\Model\ExportDocumentTaskModel
     * @throws \Exception
     */
    private function getRequestDocuments(ProjectModel $model)
    {
        $documentIds = [];
        $documents = $model->getDocuments();

        foreach ($documents as $document)
        {
            $documentIds[] = $document->getId();
        }

        return $this->smartCatService
            ->getDocumentExportManager()
            ->documentExportRequestExport(['documentIds' => $documentIds]);
    }

    /**
     * @param int $taskId
     * @param Project $project
     * @param int $attempt
     * @return null|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function saveDocuments($taskId, Project $project, int $attempt = 1)
    {
        try {
            $response = $this->smartCatService
                ->getDocumentExportManager()
                ->documentExportDownloadExportResult($taskId);
        } catch (Throwable $e) {
            $this->errorHandler->handleProjectError($e, $project,"SmartCat API Error");
            return null;
        }

        switch ($response->getStatusCode()) {
            case 204:
                if (($attempt % 50) == 0) {
                    $this->errorHandler->logInfo("$attempt to get documents archive by project id = {$project->getGuid()}");
                }
                sleep(1);

                return $this->saveDocuments($taskId, $project, ++$attempt);
            case 200:
                $fileName = "{$project->getGuid()}/zip/file_" . uniqid() . uniqid() . ".zip";

                if (in_array($response->getHeaderLine('Content-Type'), Module::TEXT_MIME_TYPES)) {
                    $matches = [];

                    preg_match(
                        '/filename\s*=\s*"?(?P<attribute>.*?)\((?P<sku>.*?)\)\((?P<languageCode>.*?)\)\.\w+/',
                        $response->getHeaderLine('Content-Disposition'),
                        $matches
                    );
                    $fileName = "{$project->getGuid()}/completed/{$matches['languageCode']}/{$matches['sku']}/{$matches['attribute']}";
                }
                $this->fileService->writeFile($fileName, $response->getBody()->getContents());

                return $this->fileService->getAbsolutePath($fileName);
        }

        return null;
    }

    /**
     * @param $projectId
     * @param $filePath
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function extractDocuments($projectId, $filePath)
    {
        if (is_null($filePath)) {
            throw new \Magento\Framework\Exception\FileSystemException(
                __('Error is occurred: Zip file not exists')
            );
        }

        $completedPath = $this->fileService->getAbsolutePath("{$projectId}/completed/");

        if (in_array(mime_content_type($filePath), Module::TEXT_MIME_TYPES)) {
            return;
        }

        $result = $this->fileService->unZip($filePath, $completedPath);
        $files = $this->fileService->getDirectoryFiles($completedPath);

        foreach ($files as $file) {
            $matches = [];

            preg_match(
                '/(?P<attribute>.*?)\((?P<sku>.*?)\)\((?P<languageCode>.*?)\)\.\w+/',
                $file->getFilename(),
                $matches
            );

            if (empty($matches) || is_dir($completedPath . $file->getFilename())) {
                continue;
            }

            @mkdir($completedPath . $matches['languageCode'] . '/' . $matches['sku'], 0755, true);
            @rename(
                $completedPath . $file->getFilename(),
                "$completedPath{$matches['languageCode']}/{$matches['sku']}/{$matches['attribute']}"
            );
            @unlink($file->getPathname());
        }

        if ($result) {
            @unlink($filePath);
        }
    }

    /**
     * @param Product $product
     * @param ProjectInterface $project
     * @param $languageCode
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function setAttributes(Product &$product, ProjectInterface $project, $languageCode)
    {
        $completedPath = $this->fileService->getAbsolutePath(
            "{$project->getGuid()}/completed/{$languageCode}/{$product->getSku()}/"
        );
        $files = $this->fileService->getDirectoryFiles($completedPath);

        foreach ($files as $file) {
            $product->setData(
                $file->getFilename(),
                file_get_contents($completedPath . $file->getFilename())
            );
        }
    }
}
