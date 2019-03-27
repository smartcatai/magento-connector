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

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\Strategy\StrategyLoader;

class ElementColumn extends Column
{
    private $strategyLoader;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StrategyLoader $strategyLoader
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StrategyLoader $strategyLoader,
        array $components = [],
        array $data = []
    ) {
        $this->strategyLoader = $strategyLoader;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($this->getData('name') == ProjectEntity::ENTITY_ID) {
                    $item[$this->getData('name')] = $this->getColumnHtml($item);
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param $item
     * @return string
     */
    private function getColumnHtml($item)
    {
        $strategy = $this->strategyLoader->getStrategyByType($item[ProjectEntity::ENTITY]);

        if (!$strategy) {
            return '';
        }

        $url = $strategy->getUrlToEntity($item[ProjectEntity::ENTITY_ID]);
        $text = $strategy->getEntityName($item[ProjectEntity::ENTITY_ID]);

        return "<a href='{$url}' target='_blank'>{$text}</a>";
    }
}
