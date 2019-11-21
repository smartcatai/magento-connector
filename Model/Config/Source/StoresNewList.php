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

namespace SmartCat\Connector\Model\Config\Source;

use SmartCat\Connector\Service\StoreService;
use Magento\Framework\Data\OptionSourceInterface;

class StoresNewList implements OptionSourceInterface
{
    /**
     * @var StoreService
     */
    private $storeService;

    /**
     * StoresList constructor.
     * @param StoreService $storeService
     */
    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    public function toOptionArray()
    {
        $stores = [];

        foreach ($this->storeService->getAllStores() as $store) {
            if ($store->getCode() === 'admin') {
                continue;
            }

            $stores = array_merge($stores, [
                ['value' => $store->getId(), 'label' => "{$store->getName()} ({$store->getCode()})"]
            ]);
        }

        usort($stores, function ($a, $b) {
            return strnatcmp($a['label'], $b['label']);
        });

        array_unshift(
            $stores,
            [
                'value' => 0,
                'label' => __('New Store View')
            ]
        );

        return $stores;
    }
}