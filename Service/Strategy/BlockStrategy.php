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

use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockRepository;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProjectEntityService;

class BlockStrategy extends AbstractStrategy
{
    private $parametersTag = 'parameters';
    private $blockRepository;

    /**
     * BlockStrategy constructor.
     * @param ProjectEntityService $projectEntityService
     * @param BlockRepository $blockRepository
     */
    public function __construct(ProjectEntityService $projectEntityService, BlockRepository $blockRepository)
    {
        $this->blockRepository = $blockRepository;
        parent::__construct($projectEntityService);
    }

    /**
     * @return string[]
     */
    public static function getAppliedClasses()
    {
        return [Block::class];
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @param Project $project
     * @param Profile $profile
     * @return void
     */
    public function attach($model, Project $project, Profile $profile)
    {
        if (trim($model->getData(Block::CONTENT))) {
            $this->projectEntityService->create($project, $model, $profile, self::getType() . '|' . Block::CONTENT);
        }

        $this->projectEntityService->create($project, $model, $profile, self::getType() . '|' . $this->parametersTag);
    }

    /**
     * @param ProjectEntity $entity
     * @return mixed
     */
    public function getDocumentModel(ProjectEntity $entity)
    {
        // TODO: Implement getDocumentModel() method.
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel[] $models
     * @return string
     */
    public function getName(array $models)
    {
        $name = null;

        foreach ($models as $model) {
            if ($model instanceof Block) {
                if (strlen($name) < 80) {
                    $name .= $model->getTitle();
                } else {
                    break;
                }
                $name .= ', ';
            }
        }

        if (strlen($name) > 99) {
            $name = substr($name, 0, 99);
        } else {
            $name = substr($name, 0, -2);
        }

        return str_replace(['*', '|', '\\', ':', '"', '<', '>', '?', '/'], ' ', $name);
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return 'block';
    }
}