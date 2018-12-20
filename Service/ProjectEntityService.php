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
use Magento\Framework\Exception\CouldNotSaveException;
use SmartCat\Client\Model\BilingualFileImportSettingsModel;
use SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel;
use SmartCat\Connector\Helper\ErrorHandler;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Model\ProjectEntityRepository;

class ProjectEntityService
{
    private $projectEntityRepository;
    private $searchCriteriaBuilder;
    private $errorHandler;

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
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @param string $type
     */
    public function create(Project $project, $entity, Profile $profile, $type)
    {
        foreach ($profile->getTargetLangArray() as $targetLang) {
            $projectProduct = $this->projectEntityRepository->create();
            $projectProduct
                ->setEntityId($entity->getId())
                ->setType($type . "|" . $targetLang)
                ->setStatus(ProjectEntity::STATUS_NEW)
                ->setProjectId($project->getId());

            try {
                $this->projectEntityRepository->save($projectProduct);
            } catch (CouldNotSaveException $e) {
                $this->errorHandler->logError("Could not save: " . $e->getMessage());
            }
        }
    }

    /**
     * @param Project $project
     * @param string $type
     * @return ProjectEntity[]
     */
    public function getProjectEntities(Project $project, $type = null)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ProjectEntity::PROJECT_ID, $project->getId())
            ->addFilter(ProjectEntity::STATUS, ProjectEntity::STATUS_NEW);

        if ($type) {
            $searchCriteria->addFilter(ProjectEntity::TYPE, $type . '|%', 'like');
        }

        $list = $this->projectEntityRepository->getList($searchCriteria->create())->getItems();

        return $list;
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
            ->setTargetLanguages([$entity->getLanguage()]);
        $documentModel->attachFile($filePath, $fileName);

        return $documentModel;
    }
}
