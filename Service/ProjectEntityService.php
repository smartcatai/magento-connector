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

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use SmartCat\Client\Model\BilingualFileImportSettingsModel;
use SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel;
use SmartCat\Connector\Helper\ErrorHandler;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Model\ProjectEntityRepository;
use \Throwable;

class ProjectEntityService
{
    private $projectEntityRepository;
    private $searchCriteriaBuilder;
    private $errorHandler;

    /**
     * ProjectEntityService constructor.
     * @param ProjectEntityRepository $projectEntityRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ErrorHandler $errorHandler
     */
    public function __construct(
        ProjectEntityRepository $projectEntityRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ErrorHandler $errorHandler
    ) {
        $this->projectEntityRepository = $projectEntityRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param Project $project
     * @param AbstractModel $entity
     * @param Profile $profile
     * @param string $type
     * @param $entityName
     */
    public function create(Project $project, $entity, Profile $profile, $entityName, $type)
    {
        foreach ($profile->getTargets() as $target) {
            $projectEntity = $this->projectEntityRepository->create();
            $projectEntity
                ->setType($type)
                ->setEntity($entityName)
                ->setStatus(ProjectEntity::STATUS_NEW)
                ->setTargetLang($target[Profile::TARGET_LANG])
                ->setSourceLang($profile->getSourceLang())
                ->setSourceStore($profile->getSourceStore())
                ->setTargetStore($target[Profile::TARGET_STORE])
                ->setProjectId($project->getId());

            if ($entity instanceof AbstractModel) {
                $projectEntity->setEntityId($entity->getId());
            } else {
                $projectEntity->setEntityId(null);
            }

            $this->update($projectEntity);
        }
    }

    /**
     * @param $entityId
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sync($entityId)
    {
        $entity = $this->projectEntityRepository->getById($entityId);

        if ($entity->getStatus() === ProjectEntity::STATUS_SAVED) {
            $entity->setStatus(ProjectEntity::STATUS_COMPLETED);
            $this->update($entity);
        } else {
           throw new LocalizedException(__('Document is not completed'));
        }
    }

    /**
     * @return array|ProjectEntity[]
     */
    public function getExportingEntities()
    {
        return $this->getEntitiesByStatus(ProjectEntity::STATUS_EXPORT);
    }

    /**
     * @return array|ProjectEntity[]
     */
    public function getCompletedEntities()
    {
        return $this->getEntitiesByStatus(ProjectEntity::STATUS_COMPLETED);
    }

    /**
     * @param $status
     * @return array|ProjectEntity[]
     */
    private function getEntitiesByStatus($status)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ProjectEntity::STATUS, $status)->create();

        try {
            $entities = $this->projectEntityRepository->getList($searchCriteria)->getItems();
        } catch (Throwable $e) {
            $this->errorHandler->logError("An error occurred on getExportingEntities: {$e->getMessage()}");
            return [];
        }

        return $entities;
    }

    /**
     * @param ProjectEntity $entity
     * @return bool
     */
    public function update(ProjectEntity $entity)
    {
        try {
            if ($entity->hasDataChanges()) {
                $this->projectEntityRepository->save($entity);
            }
        } catch (Throwable $e) {
            $this->errorHandler->logError("An error occurred on projectEntity update: {$e->getMessage()}");
            return false;
        }

        return true;
    }

    /**
     * @param Project $project
     * @param string $type
     * @return ProjectEntity[]
     */
    public function getNewProjectEntities(Project $project, $type = null)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ProjectEntity::PROJECT_ID, $project->getId())
            ->addFilter(ProjectEntity::STATUS, ProjectEntity::STATUS_NEW);

        if ($type) {
            $searchCriteria->addFilter(ProjectEntity::TYPE, $type);
        }

        $list = $this->projectEntityRepository->getList($searchCriteria->create())->getItems();

        return $list;
    }

    /**
     * @param $entityId
     * @return ProjectEntity|null
     */
    public function getEntityById($entityId)
    {
        try {
            $projectEntity = $this->projectEntityRepository->getById($entityId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $projectEntity;
    }

    /**
     * @param Project $project
     * @return ProjectEntity[]|null
     */
    public function getEntitiesByProject(Project $project)
    {
        return $this->projectEntityRepository->getItemsByProject($project);
    }

    /**
     * @return array|ProjectEntity[]
     */
    public function getAllEntities()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();

        try {
            /** @var ProjectEntity[] $entities */
            $entities = $this->projectEntityRepository->getList($searchCriteria)->getItems();
        } catch (\Throwable $e) {
            return [];
        }

        return $entities;
    }

    /**
     * @param $filePath
     * @param $fileName
     * @param ProjectEntity $entity
     * @return CreateDocumentPropertyWithFilesModel
     */
    public function getDocumentCreateModel($filePath, $fileName, ProjectEntity $entity)
    {
        $bilingualFileImportSettings = new BilingualFileImportSettingsModel();
        $bilingualFileImportSettings
            ->setConfirmMode('none')
            ->setLockMode('none')
            ->setTargetSubstitutionMode('all');

        $documentModel = new CreateDocumentPropertyWithFilesModel();
        $documentModel
            ->setBilingualFileImportSettings($bilingualFileImportSettings)
            ->setExternalId($entity->getId())
            ->setTargetLanguages([$entity->getTargetLang()]);
        $documentModel->attachFile($filePath, $fileName);

        return $documentModel;
    }
}
