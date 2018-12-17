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
use SmartCat\Connector\Api\Data\ProjectInterface;
use SmartCat\Connector\Helper\ErrorHandler;
use SmartCat\Connector\Helper\SmartCatFacade;
use SmartCat\Connector\Model\ProfileRepository;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Model\ProjectRepository;
use SmartCat\Connector\Module;
use Magento\Catalog\Model\ProductRepository;
use SmartCat\Connector\Model\ProjectEntityRepository;
use SmartCat\Connector\Service\FileService;
use SmartCat\Connector\Service\StoreService;
use \Throwable;

class ProjectsRetrieve
{
    private $smartCatService;
    private $profileRepository;
    private $projectRepository;
    private $searchCriteriaBuilder;
    private $productRepository;
    private $projectProductRepository;
    private $storeManager;
    private $errorHandler;
    private $projectEntityRepository;

    public function __construct(
        ErrorHandler $errorHandler,
        ProfileRepository $profileRepository,
        ProjectRepository $projectRepository,
        ProductRepository $productRepository,
        ProjectEntityRepository $projectProductRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SmartCatFacade $smartCatService,
        StoreManager $storeManager,
        ProjectEntityRepository $projectEntityRepository
    ) {
        $this->errorHandler = $errorHandler;
        $this->smartCatService = $smartCatService;
        $this->projectRepository = $projectRepository;
        $this->profileRepository = $profileRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->projectProductRepository = $projectProductRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->projectEntityRepository = $projectEntityRepository;
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
        } catch (Throwable $e) {
            $this->errorHandler->handleError($e, "Error occurred");
            return;
        }

        /** @var Project $project */
        foreach ($projects as $project) {
            try {
                $smartCatProject = $projectManager->projectGet($project->getGuid());
            } catch (Throwable $e) {
                $this->errorHandler->handleProjectError($e, $project, "SmartCat API Error");
                continue;
            }

            $this->requestExport($smartCatProject);
            $this->exportDocuments($smartCatProject);

            $project
                ->setStatus($smartCatProject->getStatus());

            if ($smartCatProject->getDeadline()) {
                $project->setDeadline($smartCatProject->getDeadline()->format('U'));
            }

            try {
                $this->projectRepository->save($project);
            } catch (Throwable $e) {
                $this->errorHandler->handleError($e, "Error occurred");
                return;
            }
        }
    }

    /**
     * @param ProjectModel $smartCatProject
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function requestExport(ProjectModel $smartCatProject)
    {
        foreach ($smartCatProject->getDocuments() as $document) {
            $projectEntity = $this->projectEntityRepository->getById($document->getExternalId());

            if (in_array($projectEntity->getStatus(), [ProjectEntity::STATUS_EXPORT, ProjectEntity::STATUS_FAILED])) {
                continue;
            }

            $projectEntity->setStatus($document->getStatus());

            if ($document->getStatus() == ProjectEntity::STATUS_COMPLETED) {
                $export = $this->smartCatService
                    ->getDocumentExportManager()
                    ->documentExportRequestExport(['documentIds' => [$document->getId()]]);
                $projectEntity
                    ->setTaskId($export->getId())
                    ->setStatus(ProjectEntity::STATUS_EXPORT);
            }
            $this->projectEntityRepository->save($projectEntity);
        }
    }

    /**
     * @param ProjectModel $smartCatProject
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function exportDocuments(ProjectModel $smartCatProject)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ProjectEntity::STATUS, ProjectEntity::STATUS_EXPORT)->create();
        $entities = $this->projectEntityRepository->getList($searchCriteria)->getItems();

        foreach ($entities as $entity) {
            try {
                $response = $this->smartCatService
                    ->getDocumentExportManager()
                    ->documentExportDownloadExportResult($entity->getTaskId());
            } catch (Throwable $e) {
                $this->errorHandler
                    ->logError("SmartCat API Error: An error occurred on document export " . $e->getMessage());
                $entity->setStatus(ProjectEntity::STATUS_FAILED);
                continue;
            }

            if ($response->getStatusCode() == 200) {
                $content = $response->getBody()->getContents();
                $this->setContent($content, $entity, $smartCatProject);
                $entity
                    ->setStatus(ProjectEntity::STATUS_SAVED)
                    ->setTaskId(null);
            }
            $this->projectEntityRepository->save($entity);
        }
    }

    /**
     * @param $content
     * @param ProjectEntity $entity
     * @param ProjectModel $smartCatProject
     */
    private function setContent($content, ProjectEntity $entity, ProjectModel $smartCatProject)
    {
        /** @var StoreInterface[] $stores */
        $stores = $this->storeManager->getStores(true, true);

        foreach ($smartCatProject->getTargetLanguages() as $index => $targetLanguage) {
            if (!isset($stores[StoreService::getStoreCode($targetLanguage)])) {
                $this->errorHandler->logError("StoreView with code '$targetLanguage' not exists. Continue.");
                continue;
            }

            $entityAttribute = explode('|', $entity->getType());

            try {
                $product = $this->productRepository->getById(
                    $entity->getEntityId(),
                    false,
                    $stores[StoreService::getStoreCode($targetLanguage)]->getId()
                );
                $product->setData($entityAttribute[1], $content);
                $this->productRepository->save($product);
            } catch (Throwable $e) {
                $this->errorHandler->handleError($e, "SmartCat Product Error");
                continue;
            }
        }
    }
}
