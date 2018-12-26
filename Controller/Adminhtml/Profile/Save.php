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

use Magento\Framework\Exception\LocalizedException;
use SmartCat\Connector\Model\Profile;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use SmartCat\Connector\Model\ProfileRepository;
use SmartCat\Connector\Service\ProfileService;
use SmartCat\Connector\Service\StoreService;

class Save extends \Magento\Backend\App\Action
{
    private $dataPersistor;
    private $profileService;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        ProfileService $profileService
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->profileService = $profileService;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();

        if ($data) {
            try {
                $modelId = $this->profileService->createFromData($data);
                $this->messageManager->addSuccessMessage(__('You successfully saved the profile.'));
                $this->dataPersistor->clear('smartcat_connector_profile');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [Profile::ID => $modelId]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the profile.'));
            }
        
            $this->dataPersistor->set('smartcat_connector_profile', $data);
            return $resultRedirect->setPath('*/*/edit', [Profile::ID => $this->getRequest()->getParam(Profile::ID)]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
