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

namespace SmartCat\Connector\Block\Adminhtml\Profile\Edit;

use Magento\Backend\Block\Widget\Context;
use SmartCat\Connector\Helper\SmartCatFacade;

class NewButton extends GenericButton
{
    private $smartCatService;

    /**
     * NewButton constructor.
     * @param Context $context
     * @param SmartCatFacade $smartCatService
     */
    public function __construct(Context $context, SmartCatFacade $smartCatService)
    {
        $this->smartCatService = $smartCatService;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [
            'label' => __('New Profile'),
            'class' => 'primary' . $this->isDisabled(),
            'on_click' => sprintf("location.href = '%s';", $this->getNewUrl()),
            'sort_order' => 10,
        ];

        return $data;
    }

    /**
     * If smartcat credentials are wrong - disable button
     *
     * @return string
     */
    public function isDisabled()
    {
        if (!$this->smartCatService->checkCredentials()) {
            return ' disabled';
        }

        return '';
    }

    /**
     * @return string
     */
    public function getNewUrl()
    {
        return $this->getUrl('*/*/new');
    }
}
