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

interface ProjectInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const TRANSLATE = 'translate';
    const DEADLINE = 'deadline';
    const COMMENT = 'comment';
    const IS_STATS_BUILDED = 'is_stats_builded';
    const CREATED_AT = 'created_at';
    const PROJECT_ID = 'project_id';
    const UPDATED_AT = 'updated_at';
    const ELEMENT = 'element';
    const PRICE = 'price';
    const ORDER_ID = 'order_id';
    const STATUS = 'status';
    const PROFILE_ID = 'profile_id';
    const GUID = 'guid';

    const STATUS_CREATED = 'created';
    const STATUS_IN_PROGRESS = 'inProgress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELED = 'canceled';

    /**
     * Get project_id
     * @return string|null
     */
    public function getProjectId();

    /**
     * Set project_id
     * @param string $projectId
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setProjectId($projectId);

    /**
     * Get element
     * @return string|null
     */
    public function getElement();

    /**
     * Set element
     * @param string $element
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setElement($element);

    /**
     * Get GUID
     * @return string|null
     */
    public function getGuid();

    /**
     * Set GUID
     * @param string $guid
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setGuid($guid);

    /**
     * Get profile_id
     * @return string|null
     */
    public function getProfileId();

    /**
     * Set profile_id
     * @param string $profileId
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setProfileId($profileId);

    /**
     * Get translate
     * @return string|null
     */
    public function getTranslate();

    /**
     * Set translate
     * @param string $translate
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setTranslate($translate);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setStatus($status);

    /**
     * Get price
     * @return string|null
     */
    public function getPrice();

    /**
     * Set price
     * @param string $price
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setPrice($price);

    /**
     * Get deadline
     * @return string|null
     */
    public function getDeadline();

    /**
     * Set deadline
     * @param string $deadline
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setDeadline($deadline);

    /**
     * Get comment
     * @return string|null
     */
    public function getComment();

    /**
     * Set comment
     * @param string $comment
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setComment($comment);

    /**
     * Get is builded statistics
     * @return bool
     */
    public function getIsStatisticsBuilded();

    /**
     * Set builded statistics
     * @param bool $isStatsBuilded
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setIsStatisticsBuilded($isStatsBuilded);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \SmartCat\Connector\Api\Data\ProjectInterface
     */
    public function setUpdatedAt($updatedAt);
}
