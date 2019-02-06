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

interface ProfileInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const ID = 'id';
    const CREATED_AT = 'created_at';
    const STAGES = 'stages';
    const SOURCE_LANG = 'source_lang';
    const UPDATED_AT = 'updated_at';
    const VENDOR = 'vendor';
    const NAME = 'name';
    const TARGET_LANG = 'target_lang';
    const AUTO_SEND_NEW = 'auto_send_new';
    const AUTO_SEND_SAVE = 'auto_send_save';
    const EXCLUDED_ATTRIBUTES = 'excluded_attributes';
    const PROJECT_GUID = 'project_guid';

    /**
     * Get profile_id
     * @return string|null
     */
    public function getProfileId();

    /**
     * Set profile_id
     * @param string $profileId
     * @return \SmartCat\Connector\Api\Data\ProfileInterface
     */
    public function setProfileId($profileId);

    /**
     * Get vendor
     * @return string|null
     */
    public function getVendor();

    /**
     * Set vendor
     * @param string $vendor
     * @return \SmartCat\Connector\Api\Data\ProfileInterface
     */
    public function setVendor($vendor);

    /**
     * Get source_lang
     * @return string|null
     */
    public function getSourceLang();

    /**
     * Set source_lang
     * @param string $sourceLang
     * @return \SmartCat\Connector\Api\Data\ProfileInterface
     */
    public function setSourceLang($sourceLang);

    /**
     * Get target_lang
     * @return string|null
     */
    public function getTargetLang();

    /**
     * Set target_lang
     * @param string $targetLang
     * @return \SmartCat\Connector\Api\Data\ProfileInterface
     */
    public function setTargetLang($targetLang);

    /**
     * Get stages
     * @return string|null
     */
    public function getStages();

    /**
     * Set stages
     * @param string $stages
     * @return \SmartCat\Connector\Api\Data\ProfileInterface
     */
    public function setStages($stages);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \SmartCat\Connector\Api\Data\ProfileInterface
     */
    public function setName($name);

    /**
     * Get project guid
     * @return string|null
     */
    public function getProjectGuid();

    /**
     * Set project guid
     * @param string $projectGuid
     * @return \SmartCat\Connector\Api\Data\ProfileInterface
     */
    public function setProjectGuid($projectGuid);

    /**
     * Get auto send
     * @return string|null
     */
    public function getAutoSendNew();

    /**
     * Set auto send new
     * @param string $autoSendNew
     * @return \SmartCat\Connector\Api\Data\ProfileInterface
     */
    public function setAutoSendNew($autoSendNew);

    /**
     * Get auto send
     * @return string|null
     */
    public function getAutoSendSave();

    /**
     * Set auto send new
     * @param string $autoSendNew
     * @return \SmartCat\Connector\Api\Data\ProfileInterface
     */
    public function setAutoSendSave($autoSendNew);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \SmartCat\Connector\Api\Data\ProfileInterface
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
     * @return \SmartCat\Connector\Api\Data\ProfileInterface
     */
    public function setUpdatedAt($updatedAt);
}
