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

namespace SmartCat\Connector\Block\Adminhtml\Attribute;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use SmartCat\Connector\Service\ProfileService;

class LocalizeButton implements ButtonProviderInterface
{
    private $profileService;
    private $urlManager;

    /**
     * LocalizeButton constructor.
     * @param ProfileService $profileService
     * @param UrlInterface $urlManager
     */
    public function __construct(ProfileService $profileService, UrlInterface $urlManager)
    {
        $this->profileService = $profileService;
        $this->urlManager = $urlManager;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Localize All'),
            'class' => 'primary',
            'sort_order' => 9,
            'id' => 'localize_all',
            'button_class' => '',
            'class_name' => \Magento\Backend\Block\Widget\Button\SplitButton::class,
            'options' => $this->getAddProductButtonOptions(),
        ];
    }

    /**
     * @return array
     */
    private function getAddProductButtonOptions()
    {
        $splitButtonOptions = [];

        $profiles = $this->profileService->getAllProfiles();

        foreach ($profiles as $profile) {
            $splitButtonOptions[$profile->getId()] = [
                'label' => $profile->getName(),
                'onclick' => "setLocation('" . $this->getUrl($profile->getId()) . "')",
                'default' => false,
            ];
        }

        return $splitButtonOptions;
    }

    /**
     * @param $profileId
     * @return string
     */
    private function getUrl($profileId)
    {
        return $this->urlManager->getUrl(
            'smartcat_connector/localize/attributes',
            ['profiles' => $profileId]
        );
    }
}
