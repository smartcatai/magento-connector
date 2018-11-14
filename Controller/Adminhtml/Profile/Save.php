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
use SmartCat\Connector\Service\StoreService;

class Save extends \Magento\Backend\App\Action
{
    protected $dataPersistor;
    protected $profileRepository;
    protected $storeService;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        ProfileRepository $profileRepository,
        StoreService $storeService
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->profileRepository = $profileRepository;
        $this->storeService = $storeService;
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
            $id = $this->getRequest()->getParam('profile_id');

            /** @var Profile $model */
            $model = $this->profileRepository->getModelById($id);

            if ($id && !$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Profile no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            if (in_array($data['source_lang'], $data['target_lang'])) {
                $this->messageManager->addErrorMessage(__('Source Language and Target Language are identical'));
                return $resultRedirect->setPath('*/*/');
            }

            foreach ($data['target_lang'] as $language) {
                try {
                    $this->storeService->createStoreByCode($language);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('Something went wrong while creating store. Error: ' . $e->getMessage()));
                }
            }

            $data['target_lang'] = implode(',', $data['target_lang']);
            $data['stages'] = implode(',', $data['stages']);
            $data['excluded_attributes'] = implode(',', $data['excluded_attributes']);

            $model->setData($data);

            if (!trim($model->getName())) {
                $model->setName(__('Translate:') . ' ' . $data['source_lang'] . ' -> ' . $data['target_lang']);
            }
        
            try {
                $this->profileRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the Profile.'));
                $this->dataPersistor->clear('smartcat_connector_profile');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['profile_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Profile.'));
            }
        
            $this->dataPersistor->set('smartcat_connector_profile', $data);
            return $resultRedirect->setPath('*/*/edit', ['profile_id' => $this->getRequest()->getParam('profile_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
