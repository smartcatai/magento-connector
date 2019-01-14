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

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Message\Manager;
use Psr\Log\LoggerInterface;
use SmartCat\Connector\Helper\SmartCatFacade;

class SaveConfigObserver implements ObserverInterface
{
    private $logger;
    private $messageManager;
    private $smartCat;

    /**
     * @param LoggerInterface $logger
     * @param SmartCatFacade $smartCat
     * @param Manager $messageManager
     */
    public function __construct(
        LoggerInterface $logger,
        SmartCatFacade $smartCat,
        Manager $messageManager
    ) {
        $this->logger = $logger;
        $this->smartCat = $smartCat;
        $this->messageManager = $messageManager;
    }

    public function execute(EventObserver $observer)
    {
        try {
            $this->smartCat->getAccountManager()->accountGetAccountInfo();
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Smartcat credentials are wrong. Please check. Login failed'));
            $this->logger->info('Smartcat credentials are wrong. Please check. Login failed');
        }
    }
}