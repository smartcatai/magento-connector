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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use SmartCat\Connector\Helper\SmartCatFacade;

class Index extends Action
{
    private $resultPageFactory;
    private $smartCatService;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param SmartCatFacade $smartCatService
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SmartCatFacade $smartCatService
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->smartCatService = $smartCatService;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->smartCatService->checkCredentials()) {
            $this->messageManager->addErrorMessage(__(
                "Smartcat credentials are wrong. Login failed. Please check it on %1",
                "<a href='{$this->getConfigUrl()}'>" . __("this page") . "</a>"
            ));
        }

        $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__("Profiles"));
            return $resultPage;
    }

    /**
     * @return string
     */
    private function getConfigUrl()
    {
        return $this->getUrl('*/system_config/edit/section/smartcat_localization/');
    }
}
