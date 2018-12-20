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

namespace SmartCat\Connector\Service\Strategy;

use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProjectEntityService;

abstract class AbstractStrategy implements StrategyInterface
{
    protected $projectEntityService;

    /**
     * AbstractStrategy constructor.
     * @param ProjectEntityService $projectEntityService
     */
    public function __construct(ProjectEntityService $projectEntityService)
    {
        $this->projectEntityService = $projectEntityService;
    }

    /**
     * @param $data
     * @param $fileName
     * @param ProjectEntity $entity
     * @return \SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel
     */
    public function getDocumentFile($data, $fileName, ProjectEntity $entity)
    {
        $file = fopen("php://temp", "r+");
        fputs($file, $data);
        rewind($file);

        $fileName = $entity->getEntity() . "_" . $fileName;

        return $this->projectEntityService->getDocumentCreateModel($file, $fileName, $entity);
    }

    /**
     * @param Project $project
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDocumentModels(Project $project)
    {
        $documentModels = [];
        $entities = $this->projectEntityService->getProjectEntities($project, self::getType());

        foreach ($entities as $entity) {
            $documentModel = $this->getDocumentModel($entity);

            if ($documentModel) {
                $documentModels[] = $documentModel;
            }
        }

        return $documentModels;
    }
}
