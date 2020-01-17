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

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use SmartCat\Connector\Exception\SmartCatHttpException;
use SmartCat\Connector\Logger\Logger;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\ProfileRepository;
use SmartCat\Connector\Service\ProjectService;

/**
 * Class AddModelAfterSaveObserver
 *
 * @package SmartCat\Connector\Observer
 */
class AddModelAfterSaveObserver implements ObserverInterface
{
    private $senderService;
    private $profileRepository;
    private $searchCriteriaBuilder;
    private $logger;

    /**
     * AddModelAfterSaveObserver constructor.
     *
     * @param \SmartCat\Connector\Service\ProjectService $senderService
     * @param \SmartCat\Connector\Model\ProfileRepository $profileRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \SmartCat\Connector\Logger\Logger $logger
     */
    public function __construct(
        ProjectService $senderService,
        ProfileRepository $profileRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Logger $logger
    ) {
        $this->senderService = $senderService;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->profileRepository = $profileRepository;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\Model\AbstractModel $model */
        $model = $observer->getData('data_object');

        if ($model->isObjectNew()) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(Profile::AUTO_SEND_NEW, true);
        } else {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(Profile::AUTO_SEND_SAVE, true);
        }

        try {
            $profiles = $this->profileRepository->getList($searchCriteria->create())->getItems();
        } catch (LocalizedException $e) {
            $this->logger->addError("Can't get profiles list in events: {$e->getMessage()}");
        }

        /** @var Profile $profile */
        foreach ($profiles as $profile) {
            try {
                $this->senderService->createByModels([$model], $profile);
            } catch (SmartCatHttpException $e) {
                $this->logger->addError("Can't create models: {$e->getMessage()}");
            }
        }
    }
}
