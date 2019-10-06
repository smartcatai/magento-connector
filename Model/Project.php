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

use SmartCat\Connector\Api\Data\ProjectInterface;

class Project extends \Magento\Framework\Model\AbstractModel implements ProjectInterface
{
    protected $_eventPrefix = 'smartcat_connector_project';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(\SmartCat\Connector\Model\ResourceModel\Project::class);
    }

    /**
     * Get project_id
     * @return string
     */
    public function getProjectId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set project_id
     * @param string $projectId
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setProjectId($projectId)
    {
        return $this;
    }

    /**
     * Get guid
     * @return string
     */
    public function getGuid()
    {
        return $this->getData(self::GUID);
    }

    /**
     * Set guid
     * @param string $guid
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setGuid($guid)
    {
        return $this->setData(self::GUID, $guid);
    }

    /**
     * Get element
     * @return string
     */
    public function getElement()
    {
        return $this->getData(self::ELEMENT);
    }

    /**
     * Set element
     * @param string $element
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setElement($element)
    {
        return $this->setData(self::ELEMENT, $element);
    }

    /**
     * Get profile_id
     * @return string
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * Set profile_id
     * @param string $profileId
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setProfileId($profileId)
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    /**
     * Get translate
     * @return string
     */
    public function getTranslate()
    {
        return $this->getData(self::TRANSLATE);
    }

    /**
     * Set translate
     * @param string $translate
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setTranslate($translate)
    {
        return $this->setData(self::TRANSLATE, $translate);
    }

    /**
     * Get status
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get price
     * @return string
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * Set price
     * @param string $price
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * Get deadline
     * @return string
     */
    public function getDeadline()
    {
        return $this->getData(self::DEADLINE);
    }

    /**
     * Set deadline
     * @param string $deadline
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setDeadline($deadline)
    {
        return $this->setData(self::DEADLINE, $deadline);
    }

    /**
     * Get is builded statistics
     * @return bool
     */
    public function getIsStatisticsBuilded()
    {
        return $this->getData(self::IS_STATS_BUILDED);
    }

    /**
     * Set builded statistics
     * @param bool $isStatsBuilded
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setIsStatisticsBuilded($isStatsBuilded)
    {
        return $this->setData(self::IS_STATS_BUILDED, $isStatsBuilded);
    }

    /**
     * Get created_at
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
