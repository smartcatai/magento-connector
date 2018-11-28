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

namespace SmartCat\Connector\Service;


use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\StoreFactory;
use Psr\Log\LoggerInterface;
use SmartCat\Connector\Helper\LanguageDictionary;

class StoreService
{
    private $storeResourceModel;
    private $storeFactory;
    private $logger;

    public function __construct(
        StoreFactory $storeFactory,
        Store $storeResourceModel,
        LoggerInterface $logger
    ) {
        $this->storeFactory = $storeFactory;
        $this->storeResourceModel = $storeResourceModel;
        $this->logger = $logger;
    }

    /**
     * @param $languageCode
     */
    public function createStoreByCode($languageCode)
    {
        $store = $this->storeFactory->create();
        $name = LanguageDictionary::getNameByCode($languageCode);

        $store
            ->setCode($languageCode)
            ->setIsActive(true)
            ->setWebsiteId(1)
            ->setGroupId(1)
            ->setName($name);

        try {
            $this->storeResourceModel->save($store);
        } catch (AlreadyExistsException $e) {
        } catch (\Exception $e) {
            $this->logger->error("SmartCat Store Service error: {$e->getMessage()}");
        }
    }
}