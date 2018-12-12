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

namespace SmartCat\Connector\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use SmartCat\Connector\Exception\SmartCatHttpException;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\ProfileRepository;
use SmartCat\Connector\Service\ProjectService;

class AddProductObserver implements ObserverInterface
{
    private $senderService;
    private $profileRepository;
    private $searchCriteriaBuilder;

    public function __construct(
        ProjectService $senderService,
        ProfileRepository $profileRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->senderService = $senderService;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->profileRepository = $profileRepository;
    }

    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getData('product');

        if (!($product instanceof ProductInterface)) {
            return;
        }

        if ($product->isObjectNew()) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(Profile::AUTO_SEND_NEW, true);
        } else {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(Profile::AUTO_SEND_SAVE, true);
        }

        try {
            $profiles = $this->profileRepository->getList($searchCriteria->create())->getItems();
        } catch (LocalizedException $e) {
        }

        /** @var Profile $profile */
        foreach ($profiles as $profile) {
            try {
                $this->senderService->create([$product], $profile);
            } catch (SmartCatHttpException $e) {
            }
        }
    }
}
