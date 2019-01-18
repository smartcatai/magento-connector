<?php
/**
 * Created by PhpStorm.
 * User: medic84
 * Date: 18.01.19
 * Time: 12:11
 */

namespace SmartCat\Connector\Block\Adminhtml\Category;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Localize implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Localize All'),
            'class' => 'primary',
            'on_click' => '',
            'data_attribute' => [
                'mage-init' => [
                    'profiles-modal' => ['target' => '#modal-content'],
                ],
            ],
            'sort_order' => 29,
        ];
    }
}
