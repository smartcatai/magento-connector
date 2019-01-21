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

namespace SmartCat\Connector\Block\Adminhtml\Modal;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template;
use SmartCat\Connector\Service\ProfileService;

class CategoryProfilesModal extends \Magento\Framework\View\Element\Template
{
    private $profileService;
    private $formKey;

    /**
     * CategoryProfilesModal constructor.
     * @param Template\Context $context
     * @param ProfileService $profileService
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ProfileService $profileService,
        FormKey $formKey,
        array $data = []
    ) {
        $this->formKey = $formKey;
        $this->profileService = $profileService;
        parent::__construct($context, $data);
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
            'smartcat_connector/localize/category',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
