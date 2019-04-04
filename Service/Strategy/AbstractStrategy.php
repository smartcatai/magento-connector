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

use Magento\Framework\UrlInterface;
use SmartCat\Connector\Helper\StringHelper;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\StoreService;

abstract class AbstractStrategy implements StrategyInterface
{
    protected $projectEntityService;
    protected $storeService;
    protected $urlManager;

    /**
     * AbstractStrategy constructor.
     * @param ProjectEntityService $projectEntityService
     * @param StoreService $storeService
     * @param UrlInterface $urlManager
     */
    public function __construct(
        ProjectEntityService $projectEntityService,
        StoreService $storeService,
        UrlInterface $urlManager
    ) {
        $this->projectEntityService = $projectEntityService;
        $this->storeService = $storeService;
        $this->urlManager = $urlManager;
    }

    /**
     * @param string[] $strings
     * @return string
     */
    public function getElementNames(array $strings)
    {
        if (empty($strings)) {
            $strings = [uniqid()];
        }

        $stringNames = StringHelper::limitImplode($strings);

        return StringHelper::whitespaceSpecChars($stringNames);
    }

    /**
     * @param string $json
     * @return array
     */
    public function decodeJsonParameters($json)
    {
        return json_decode($json, true);
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
     */
    public function getDocumentModels(Project $project)
    {
        $documentModels = [];
        $entities = $this->projectEntityService->getNewProjectEntities($project, self::getEntityName());

        foreach ($entities as $entity) {
            $documentModel = $this->getDocumentModel($entity);

            if ($documentModel) {
                $documentModels[] = $documentModel;
            }
        }

        return $documentModels;
    }
}
