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

namespace SmartCat\Connector\Model\ResourceModel\ProjectEntity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\ResourceModel\ProjectEntity as ProjectEntityResourceModel;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Module;

/**
 * Class Collection
 *
 * @method getItems() SmartCat\Connector\Model\ProjectEntity[]
 *
 * @package SmartCat\Connector\Model\ResourceModel\ProjectEntity
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(ProjectEntity::class, ProjectEntityResourceModel::class);
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->joinLeft(
                ['projectTable' => $this->getTable(Module::PROJECT_TABLE_NAME)],
                'main_table.' . ProjectEntity::PROJECT_ID . ' = projectTable.' . Project::ID,
                ['comment', 'profile_id', 'deadline']
            )->joinLeft(
                ['profileTable' => $this->getTable(Module::PROFILE_TABLE_NAME)],
                'projectTable.' . Project::PROFILE_ID . ' = profileTable.' . Profile::ID,
                ['source_lang', 'name']
            );

        $this
            ->addFilterToMap('comment', 'projectTable.comment')
            ->addFilterToMap('deadline', 'projectTable.deadline')
            ->addFilterToMap('source_lang', 'profileTable.source_lang')
            ->addFilterToMap('target_lang', 'main_table.target_lang')
            ->addFilterToMap('status', 'main_table.status')
            ->addFilterToMap('name', 'profileTable.name');
    }
}
