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

namespace SmartCat\Connector\Api\Data;

interface ProjectSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get Project list.
     * @return \SmartCat\Connector\Api\Data\ProjectInterface[]
     */
    public function getItems();

    /**
     * Set project_id list.
     * @param \SmartCat\Connector\Api\Data\ProjectInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
