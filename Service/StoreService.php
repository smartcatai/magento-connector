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
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\StoreManager;
use SmartCat\Connector\Logger\Logger;
use SmartCat\Connector\Helper\LanguageDictionary;

class StoreService
{
    private $storeResourceModel;
    private $storeFactory;
    private $storeManager;
    private $logger;

    const BAD_SYMBOLS = ['*', '|', '\\', ':', '"', '<', '>', '?', '/', '-'];

    public function __construct(
        StoreFactory $storeFactory,
        StoreManager $storeManager,
        Store $storeResourceModel,
        Logger $logger
    ) {
        $this->storeFactory = $storeFactory;
        $this->storeManager = $storeManager;
        $this->storeResourceModel = $storeResourceModel;
        $this->logger = $logger;
    }

    /**
     * @param $languageCode
     * @return int
     */
    public function createStoreByCode($languageCode)
    {
        /** @var  \Magento\Store\Model\Store $store */
        $store = $this->storeFactory->create();

        $store
            ->setCode($this->getStoreCode($languageCode))
            ->setIsActive(true)
            ->setWebsiteId(1)
            ->setGroupId(1)
            ->setName(LanguageDictionary::getNameByCode($languageCode));

        try {
            $this->storeResourceModel->save($store);

            return $this->getStoreIdByCode($this->getStoreCode($languageCode));
        } catch (AlreadyExistsException $e) {
        } catch (\Exception $e) {
            $this->logger->error("SmartCat Store Service error: {$e->getMessage()}");
        }

        return 0;
    }

    /**
     * @param $storeCode
     * @return StoreInterface|null
     */
    public function getStoreByCode($storeCode)
    {
        /** @var StoreInterface[] $stores */
        $stores = $this->storeManager->getStores(true, true);

        if (!isset($stores[self::getStoreCode($storeCode)])) {
            $this->logger->error("SmartCat Store Service error: Store with code {$storeCode} not exists");

            return null;
        }

        return $stores[self::getStoreCode($storeCode)];
    }

    /**
     * @return StoreInterface|null
     */
    public function getDefaultStore()
    {
        return $this->storeManager->getDefaultStoreView();
    }

    /**
     * @return array|StoreInterface[]
     */
    public function getAllStores()
    {
        return $this->storeManager->getStores(true, true);
    }

    /**
     * @param $id
     * @return StoreInterface|string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreById($id)
    {
        return $this->storeManager->getStore($id);
    }

    /**
     * @param $storeCode
     * @return int|null
     */
    public function getStoreIdByCode($storeCode)
    {
        $store = $this->getStoreByCode($storeCode);

        if ($store !== null) {
            return $store->getId();
        }

        return null;
    }

    /**
     * @param $languageCode
     * @return string
     */
    public static function getStoreCode($languageCode)
    {
        return strtolower(str_replace(self::BAD_SYMBOLS, '_', $languageCode));
    }
}
