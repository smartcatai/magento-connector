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

use SmartCat\Connector\Model\Profile;
use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;
use SmartCat\Connector\Model\ProfileRepository;

class Delete extends \SmartCat\Connector\Controller\Adminhtml\Profile
{
    private $profileRepository;

    public function __construct(Context $context, Registry $coreRegistry, ProfileRepository $profileRepository)
    {
        $this->profileRepository = $profileRepository;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam(Profile::ID);
        if ($id) {
            try {
                $this->profileRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the profile.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', [Profile::ID => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a profile to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
