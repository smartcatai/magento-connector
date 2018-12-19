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
     * @return ProjectEntity[]
     */
    public function getProjectEntities(Project $project)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ProjectEntity::PROJECT_ID, $project->getId())
            ->addFilter(ProjectEntity::STATUS, ProjectEntity::STATUS_NEW)
            ->create();
        $list = $this->projectEntityRepository->getList($searchCriteria)->getItems();

        return $list;
    }
}
