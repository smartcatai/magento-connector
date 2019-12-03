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

namespace SmartCat\Connector\Controller\Adminhtml\Entity;

use Magento\Backend\App\Action;
use SmartCat\Connector\Service\ProfileService;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\ProjectService;

class Sync extends Action
{
    private $projectEntityService;

    /**
     * Category constructor.
     * @param Action\Context $context
     * @param ProfileService $profileService
     * @param ProjectService $projectService
     */
    public function __construct(
        Action\Context $context,
        ProjectEntityService $projectEntityService
    ) {
        $this->projectEntityService = $projectEntityService;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $redirectFactory = $this->resultRedirectFactory->create();

        $entityId = $this->getRequest()->getParam('id');

        try {
            $this->projectEntityService->sync($entityId);

            $this->messageManager->addSuccessMessage(__('All items are sent to sync'));
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('An a error occurred: ') . $e->getMessage());
        }

        return $redirectFactory->setPath('smartcat_connector/dashboard/index');
    }
}
