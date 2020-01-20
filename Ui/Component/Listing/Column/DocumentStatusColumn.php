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

namespace SmartCat\Connector\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use SmartCat\Connector\Model\Config\Source\ProjectEntityStatusList;
use Magento\Framework\UrlInterface;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\Strategy\AttributesStrategy;
use SmartCat\Connector\Service\Strategy\BlockStrategy;
use SmartCat\Connector\Service\Strategy\CategoryStrategy;
use SmartCat\Connector\Service\Strategy\PageStrategy;
use SmartCat\Connector\Service\Strategy\ProductStrategy;

class DocumentStatusColumn extends Column
{
    private $statusList;
    private $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ProjectEntityStatusList $statusList
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ProjectEntityStatusList $statusList,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->statusList = $statusList;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $statusList = $this->statusList->toOptionArray();

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['original_items'] as $key => $item) {
                if ($this->getData('name') == ProjectEntity::STATUS) {
                    $index = array_search(
                        $item[ProjectEntity::STATUS],
                        array_column($statusList, 'value')
                    );
                    $dataSource['data']['items'][$key][ProjectEntity::STATUS] =
                      $this->getStoreUrl($item, $statusList[$index]['label']);
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param $item
     * @param $label
     *
     * @return string
     */
    public function getStoreUrl($item, $label)
    {
        $scope = [];
        $route = null;

        if ($item[ProjectEntity::STATUS] !== ProjectEntity::STATUS_SAVED) {
            return $label;
        }

        switch ($item[ProjectEntity::ENTITY]) {
            case ProductStrategy::getEntityName():
                $scope = [
                    'store' => $item[ProjectEntity::TARGET_STORE],
                    'id' => $item[ProjectEntity::TARGET_ENTITY_ID],
                ];
                $route = 'catalog/product/edit';
                break;
            case BlockStrategy::getEntityName():
                $scope['block_id'] = $item[ProjectEntity::TARGET_ENTITY_ID];
                $route = 'cms/block/edit';
                break;
            case PageStrategy::getEntityName():
                $scope['page_id'] = $item[ProjectEntity::TARGET_ENTITY_ID];
                $route = 'cms/page/edit';
                break;
            case CategoryStrategy::getEntityName():
                $scope['store'] = $item[ProjectEntity::TARGET_STORE];
                $route = 'catalog/category/index';
                break;
            case AttributesStrategy::getEntityName():
                $scope['store'] = $item[ProjectEntity::TARGET_STORE];
                $route = 'catalog/product_attribute/index';
                break;
            default:
                break;
        }

        if ($route) {
            $href = $this->urlBuilder->getUrl($route, $scope);
            return sprintf('<a href="%s" target="_blank">%s</a>', $href, $label);
        } else {
            return $label;
        }
    }

}
