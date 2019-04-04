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

namespace SmartCat\Connector\Controller\Adminhtml\Localize;

use Magento\Backend\App\Action;
use SmartCat\Connector\Service\ProfileService;
use SmartCat\Connector\Service\ProjectService;
use SmartCat\Connector\Service\Strategy\CategoryStrategy;

class Category extends \Magento\Backend\App\Action
{
    private $profileService;
    private $projectService;

    /**
     * Category constructor.
     * @param Action\Context $context
     * @param ProfileService $profileService
     * @param ProjectService $projectService
     */
    public function __construct(
        Action\Context $context,
        ProfileService $profileService,
        ProjectService $projectService
    ) {
        $this->profileService = $profileService;
        $this->projectService = $projectService;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $redirectFactory = $this->resultRedirectFactory->create();

        $profilesIds = $this->getRequest()->getParam('profiles');

        try {
            $profile = $this->profileService->getProfileById($profilesIds);
            $this->projectService->createByKey(CategoryStrategy::getEntityName(), $profile);

            $this->messageManager->addSuccessMessage(__('All categories were sent to localization'));
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('An a error occurred: %s', $e->getMessage()));
        }

        return $redirectFactory->setPath('catalog/category/index');
    }
}
