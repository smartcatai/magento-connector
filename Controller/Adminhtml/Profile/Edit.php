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

namespace SmartCat\Connector\Controller\Adminhtml\Profile;

use SmartCat\Connector\Helper\SmartCatFacade;
use SmartCat\Connector\Controller\Adminhtml\Profile as AbstractProfile;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\ProfileRepository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

class Edit extends AbstractProfile
{
    private $resultPageFactory;
    private $profileRepository;
    private $smartCatService;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param ProfileRepository $profileRepository
     * @param SmartCatFacade $smartCatService
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        ProfileRepository $profileRepository,
        SmartCatFacade $smartCatService
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->smartCatService = $smartCatService;
        $this->profileRepository = $profileRepository;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(Profile::ID);
        $model = null;
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        if (!$this->smartCatService->checkCredentials()) {
            $this->messageManager->addErrorMessage(__('Smartcat API error: Wrong credentials. Please check config.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            if ($id) {
                $model = $this->profileRepository->getById($id);
            }
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('This profile no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }

        $this->_coreRegistry->register('smartcat_connector_profile', $model);

        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Profile') : __('New Profile'),
            $id ? __('Edit Profile') : __('New Profile')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Profiles'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Profile "%1"', $model->getName()) : __('New Profile')
        );

        return $resultPage;
    }
}
