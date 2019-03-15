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

use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;

interface StrategyInterface
{
    /**
     * @return string[]
     */
    public static function getAppliedClasses();

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @param Project $project
     * @param Profile $profile
     * @return void
     */
    public function attach($model, Project $project, Profile $profile);

    /**
     * @param Project $project
     * @return mixed
     */
    public function getDocumentModels(Project $project);

    /**
     * @param ProjectEntity $entity
     * @return mixed
     */
    public function getDocumentModel(ProjectEntity $entity);

    /**
     * @param \Magento\Framework\Model\AbstractModel[] $models
     * @return string
     */
    public function getName(array $models);

    /**
     * @return string
     */
    public static function getType();

    /**
     * @param string $content
     * @param ProjectEntity $entity
     * @return bool
     */
    public function setContent($content, ProjectEntity $entity): bool;

    /**
     * @param $entityId
     * @return string
     */
    public function getEntityName($entityId);

    /**
     * @param $entityId
     * @return string
     */
    public function getUrlToEntity($entityId);
}
