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

namespace SmartCat\Connector\Model;


class ProjectProduct extends \Magento\Framework\Model\AbstractModel
{
    const PROJECT_ID = 'project_id';
    const PRODUCT_ID = 'product_id';
    const ID = 'id';
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\SmartCat\Connector\Model\ResourceModel\ProjectProduct::class);
    }

    /**
     * Get project_id
     * @return string
     */
    public function getProjectId()
    {
        return $this->getData(self::PROJECT_ID);
    }

    /**
     * Set project_id
     * @param string $projectId
     * @return $this
     */
    public function setProjectId($projectId)
    {
        return $this->setData(self::PROJECT_ID, $projectId);
    }

    /**
     * Get product_id
     * @return string
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Set product_id
     * @param string $projectId
     * @return $this
     */
    public function setProductId($projectId)
    {
        return $this->setData(self::PRODUCT_ID, $projectId);
    }

    /**
     * Get id
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }
}