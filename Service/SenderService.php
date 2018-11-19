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

namespace SmartCat\Connector\Service;

use Magento\Catalog\Model\Product;
use SmartCat\Client\Model\BilingualFileImportSettingsModel;
use SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel;
use SmartCat\Client\Model\CreateProjectModel;
use SmartCat\Client\Model\ProjectChangesModel;
use SmartCat\Connector\Exception\SmartCatHttpException;
use SmartCat\Connector\Helper\ErrorHandler;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\ProjectProductRepository;
use SmartCat\Connector\Model\ProjectRepository;
use \DateTime;
use \Throwable;

class SenderService
{
    private $connectorService;
    private $fileService;
    private $projectRepository;
    private $projectProductRepository;
    private $errorHandler;

    private $excludedAttributes = [
        'required_options',
        'sku',
        'has_options'
    ];

    public function __construct(
        ConnectorService $connectorService,
        FileService $fileService,
        ProjectRepository $projectRepository,
        ProjectProductRepository $projectProductRepository,
        ErrorHandler $errorHandler
    ) {
        $this->connectorService = $connectorService;
        $this->fileService = $fileService;
        $this->projectRepository = $projectRepository;
        $this->projectProductRepository = $projectProductRepository;
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param Product[] $products
     * @param Profile $profile
     * @throws SmartCatHttpException
     */
    public function sendProduct(array $products, Profile $profile)
    {
        $name = $this->getProjectName($products);
        $projectManager = $this->connectorService->getService()->getProjectManager();

        // Create and send project model to smartcat api
        $newProjectModel = (new CreateProjectModel())
            ->setName($name)
            ->setDescription('Magento SmartCat Connector. Product: ' . md5($name))
            ->setSourceLanguage($profile->getSourceLang())
            ->setTargetLanguages($profile->getTargetLangArray())
            ->setUseMT(false)
            ->setPretranslate(false)
            ->setWorkflowStages($profile->getStagesArray())
            ->setAssignToVendor(false);

        try {
            $projectModel = $projectManager->projectCreateProject($newProjectModel);
        } catch (Throwable $e) {
            $message = $this->errorHandler->handleError($e, "SmartCat create project error");
            throw new SmartCatHttpException($message, $e->getCode(), $e->getPrevious());
        }

        $project = $this->projectRepository->create();
        $project
            ->setGuid($projectModel->getId())
            ->setProfileId($profile->getProfileId())
            ->setStatus($projectModel->getStatus())
            ->setElement($name)
            ->setTranslate($profile->getSourceLang() . ' -> ' . $profile->getTargetLang())
            ->setDeadline($projectModel->getDeadline());

        try {
            $this->projectRepository->save($project);
        } catch (Throwable $e) {
            $message = $this->errorHandler->handleError($e, "Error save project to db");
            throw new SmartCatHttpException($message, $e->getCode(), $e->getPrevious());
        }

        try {
            foreach ($products as $product) {
                $this->writeAttributesToFiles(
                    $product,
                    $projectModel->getId(),
                    $profile->getSourceLang(),
                    $profile->getExcludedAttributesArray()
                );

                $projectProduct = $this->projectProductRepository->create();
                $projectProduct
                    ->setProductId($product->getId())
                    ->setProjectId($project->getProjectId());
                $this->projectProductRepository->save($projectProduct);
            }

            $projectManager->projectAddDocument([
                'projectId' => $projectModel->getId(),
                'documentModel' => $this->getDocumentModels(
                    $this->fileService->getAbsolutePath("{$projectModel->getId()}/{$profile->getSourceLang()}")
                )
            ]);
        } catch (Throwable $e) {
            $message = $this->errorHandler->handleProjectError($e, $project, "SmartCat adding documents error");
            throw new SmartCatHttpException($message, $e->getCode(), $e->getPrevious());
        }

        // If Vendor ID exists - update project and set vendor
        if ($profile->getVendor()) {
            $projectChanges = (new ProjectChangesModel())
                ->setName($projectModel->getName())
                ->setDescription($projectModel->getDescription())
                ->setDeadline((new DateTime('now'))->modify(' + 1 day'))
                ->setVendorAccountId($profile->getVendor());
            try {
                $projectManager->projectUpdateProject($projectModel->getId(), $projectChanges);
            } catch (Throwable $e) {
                $message = $this->errorHandler->handleProjectError($e, $project, "SmartCat error update project to vendor");
                throw new SmartCatHttpException($message, $e->getCode(), $e->getPrevious());
            }
        }
    }

    /**
     * @param $filePath
     * @param $fileName
     * @return CreateDocumentPropertyWithFilesModel
     */
    private function getDocumentModel($filePath, $fileName)
    {
        $bilingualFileImportSettings = new BilingualFileImportSettingsModel();
        $bilingualFileImportSettings
            ->setConfirmMode('none')
            ->setLockMode('none')
            ->setTargetSubstitutionMode('all');

        $documentModel = new CreateDocumentPropertyWithFilesModel();
        $documentModel->setBilingualFileImportSettings($bilingualFileImportSettings);
        $documentModel->attachFile($filePath, $fileName);

        return $documentModel;
    }

    /**
     * @param Product $product
     * @param string $projectGuid
     * @param $sourceLang
     * @param array $except
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function writeAttributesToFiles(Product $product, $projectGuid, $sourceLang ,array $except = [])
    {
        $exceptAttributes = array_merge($this->excludedAttributes, $except);

        foreach ($product->getAttributes() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (in_array($attribute->getFrontendInput(), ['text', 'textarea']) && !in_array($attributeCode, $exceptAttributes)) {
                $data = $product->getData($attributeCode);

                if (is_array($data) || !trim($data)) {
                    continue;
                }

                $this->fileService->writeFile(
                    "{$projectGuid}/{$sourceLang}/{$attributeCode}({$product->getSku()}).html",
                    $data
                );
            }
        }
    }


    /**
     * @param string $filePath
     * @return array
     */
    private function getDocumentModels($filePath)
    {
        $documentModels = [];

        $files = $this->fileService->getDirectoryFiles($filePath);

        foreach ($files as $file) {
            $documentModels[] = $this->getDocumentModel(
                $file->getPathname(),
                $file->getFilename()
            );
        }

        return $documentModels;
    }

    private function getProjectName(array $products)
    {
        $name = null;
        
        foreach ($products as $product) {
            if ($product instanceof Product) {
                if (strlen($name) < 80 ) {
                    $name .= $product->getName();
                } else {
                    break;
                }

                $name .= ', ';
            }
        }

        return substr($name, 0, -2);
    }
}
