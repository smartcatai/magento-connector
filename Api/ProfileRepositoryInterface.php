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

namespace SmartCat\Connector\Magento\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ProfileRepositoryInterface
{

    /**
     * Save Profile
     * @param \SmartCat\Connector\Magento\Api\Data\ProfileInterface $profile
     * @return \SmartCat\Connector\Magento\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \SmartCat\Connector\Magento\Api\Data\ProfileInterface $profile
    );

    /**
     * Retrieve Profile
     * @param string $profileId
     * @return \SmartCat\Connector\Magento\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($profileId);

    /**
     * Retrieve Profile matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \SmartCat\Connector\Magento\Api\Data\ProfileSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Profile
     * @param \SmartCat\Connector\Magento\Api\Data\ProfileInterface $profile
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \SmartCat\Connector\Magento\Api\Data\ProfileInterface $profile
    );

    /**
     * Delete Profile by ID
     * @param string $profileId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($profileId);
}
