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

namespace SmartCat\Connector\Block\Adminhtml\Product;

use SmartCat\Connector\Block\Adminhtml\Category\LocalizeButton;
use SmartCat\Connector\Service\ProfileService;
use Magento\Backend\Block\Widget\Container;

class AttributeBlock extends Container
{
    private $profileService;
    private $localizeButton;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param ProfileService $profileService
     * @param LocalizeButton $localizeButton
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        ProfileService $profileService,
        LocalizeButton $localizeButton,
        array $data = []
    ) {
        $this->localizeButton = $localizeButton;
        $this->profileService = $profileService;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_controller = 'adminhtml_product_attribute';
        $this->_blockGroup = 'Magento_Catalog';
        $this->_template = 'SmartCat_Connector::profiles_modal.phtml';

        $this->addButton(
            'localize_all',
            $this->localizeButton->getButtonData()
        );

        parent::_construct();
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $data = [];

        foreach ($this->profileService->getAllProfiles() as $profile) {
            $data = array_merge($data, [
                ["id" => $profile->getId(), "name" => $profile->getName()]
            ]);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        return $this->getUrl(
            'smartcat_connector/localize/attributes',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
