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
use \DateTime;

class Profile extends \Magento\Framework\Model\AbstractModel implements ProfileInterface
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
     * Get excluded attributes
     * @return string
     */
    public function getExcludedAttributes()
    {
        return $this->getData(self::EXCLUDED_ATTRIBUTES);
    }

    /**
     * Get excluded attributes array
     * @return array
     */
    public function getExcludedAttributesArray()
    {
        return explode(',', $this->getExcludedAttributes());
    }

    /**
     * Set excluded attributes
     * @param string|array $excludedAttributes
     * @return ProfileInterface
     */
    public function setExcludedAttributes($excludedAttributes)
    {
        if (is_array($excludedAttributes)) {
            $excludedAttributes = implode(',', $excludedAttributes);
        }
        
        return $this->setData(self::EXCLUDED_ATTRIBUTES, $excludedAttributes);
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
     * @return string
     */
    public function getTargetLang()
    {
        return $this->getData(self::TARGET_LANG);
    }

    /**
     * Get target_lang
     * @return array
     */
    public function getTargetLangArray()
    {
        return explode(',', $this->getTargetLang());
    }

    /**
     * Set target_lang
     * @param string|array $targetLang
     * @return ProfileInterface
     */
    public function setTargetLang($targetLang)
    {
        if (is_array($targetLang)) {
            $targetLang = implode(',', $targetLang);
        }

        return $this->setData(self::TARGET_LANG, $targetLang);
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
     * Set batch send
     * @param string $batchSend
     * @return ProfileInterface
     */
    public function setBatchSend($batchSend)
    {
        return $this->setData(self::BATCH_SEND, $batchSend);
    }

    /**
     * Get batch send
     * @return string|null
     */
    public function getBatchSend()
    {
        return $this->getData(self::BATCH_SEND);
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
