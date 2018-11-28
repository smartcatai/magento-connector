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

namespace SmartCat\Connector\Magento\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use SmartCat\Connector\Magento\Model\Project;

class ProjectStatusList implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => Project::STATUS_WAITING,'label' => __('Waiting')],
            ['value' => Project::STATUS_CREATED,'label' => __('Created')],
            ['value' => Project::STATUS_IN_PROGRESS,'label' => __('In Progress')],
            ['value' => Project::STATUS_COMPLETED,'label' => __('Completed')],
            ['value' => Project::STATUS_REJECTED,'label' => __('Rejected')],
            ['value' => Project::STATUS_CANCELED,'label' => __('Canceled')],
            ['value' => Project::STATUS_FAILED,'label' => __('Failed')],
        ];
    }
}