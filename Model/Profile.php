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

use SmartCat\Connector\Api\Data\ProfileInterface;
use Magento\Framework\Model\AbstractModel;
use \DateTime;

class Profile extends AbstractModel implements ProfileInterface
{
    protected $_eventPrefix = 'smartcat_connector_profile';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\SmartCat\Connector\Model\ResourceModel\Profile::class);
    }

    /**
     * Get profile_id
     * @return string
     */
    public function getProfileId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set profile_id
     * @param string $profileId
     * @return ProfileInterface
     */
    public function setProfileId($profileId)
    {
        return $this;
    }

    /**
     * Get vendor
     * @return string
     */
    public function getVendor()
    {
        return $this->getData(self::VENDOR);
    }

    /**
     * Set vendor
     * @param string $vendor
     * @return ProfileInterface
     */
    public function setVendor($vendor)
    {
        return $this->setData(self::VENDOR, $vendor);
    }

    /**
     * Get vendor
     * @return string
     */
    public function getVendorName()
    {
        return $this->getData(self::VENDOR_NAME);
    }

    /**
     * Set vendor
     * @param string $vendorName
     * @return ProfileInterface
     */
    public function setVendorName($vendorName)
    {
        return $this->setData(self::VENDOR_NAME, $vendorName);
    }

    /**
     * Get source_lang
     * @return string
     */
    public function getSourceLang()
    {
        return $this->getData(self::SOURCE_LANG);
    }

    /**
     * Set source_lang
     * @param string $sourceLang
     * @return ProfileInterface
     */
    public function setSourceLang($sourceLang)
    {
        return $this->setData(self::SOURCE_LANG, $sourceLang);
    }

    /**
     * Get target_lang
     * @return array
     */
    public function getTargets()
    {
        return json_decode($this->getData(self::TARGETS), true);
    }

    /**
     * Set target_lang
     * @param string|array $targets
     * @return ProfileInterface
     */
    public function setTargets($targets)
    {
        if (is_array($targets)) {
            $targets = json_encode($targets);
        }

        return $this->setData(self::TARGETS, $targets);
    }

    /**
     * Get target_lang
     * @return array
     */
    public function getTargetLangs()
    {
        return array_column($this->getTargets(), self::TARGET_LANG);
    }

    /**
     * Get target store
     * @return array
     */
    public function getTargetStores()
    {
        return array_column($this->getTargets(), self::TARGET_STORE);
    }

    /**
     * Get source store
     * @return int
     */
    public function getSourceStore()
    {
        return $this->getData(self::SOURCE_STORE);
    }

    /**
     * Set source store
     * @param $sourceStore
     * @return $this
     */
    public function setSourceStore($sourceStore)
    {
        return $this->setData(self::SOURCE_STORE, $sourceStore);
    }

    /**
     * Get stages
     * @return string
     */
    public function getStages()
    {
        return $this->getData(self::STAGES);
    }

    /**
     * Get stages array
     * @return string[]
     */
    public function getStagesArray()
    {
        return explode(',', $this->getStages());
    }

    /**
     * Set stages
     * @param string|array $stages
     * @return ProfileInterface
     */
    public function setStages($stages)
    {
        if (is_array($stages)) {
            $stages = implode(',', $stages);
        }

        return $this->setData(self::STAGES, $stages);
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set name
     * @param string $name
     * @return ProfileInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get project id
     * @return string|null
     */
    public function getProjectGuid()
    {
        return $this->getData(self::PROJECT_GUID);
    }

    /**
     * Set project guid
     * @param string $projectGuid
     * @return \SmartCat\Connector\Api\Data\ProfileInterface
     */
    public function setProjectGuid($projectGuid)
    {
        return $this->setData(self::PROJECT_GUID, $projectGuid);
    }

    /**
     * Get auto send new
     * @return string|null
     */
    public function getAutoSendNew()
    {
        return $this->getData(self::AUTO_SEND_NEW);
    }

    /**
     * Set auto send new
     * @param string $autoSendNew
     * @return ProfileInterface
     */
    public function setAutoSendNew($autoSendNew)
    {
        return $this->setData(self::AUTO_SEND_NEW, $autoSendNew);
    }

    /**
     * Get auto send save
     * @return string|null
     */
    public function getAutoSendSave()
    {
        return $this->getData(self::AUTO_SEND_SAVE);
    }

    /**
     * Set auto send save
     * @param string $autoSendSave
     * @return ProfileInterface
     */
    public function setAutoSendSave($autoSendSave)
    {
        return $this->setData(self::AUTO_SEND_SAVE, $autoSendSave);
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
     * @return ProfileInterface
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
     * @param string|DateTime $updatedAt
     * @return ProfileInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        if ($updatedAt instanceof DateTime) {
            $updatedAt = $updatedAt->format('U');
        }

        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
