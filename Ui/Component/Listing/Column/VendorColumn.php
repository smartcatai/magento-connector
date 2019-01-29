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
use SmartCat\Connector\Helper\SmartCatFacade;

class VendorColumn extends Column
{
    private $smartCatService;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SmartCatFacade  $smartCatService
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SmartCatFacade $smartCatService,
        array $components = [],
        array $data = []
    ) {
        $this->smartCatService = $smartCatService;
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
        $vendors = [];

        if (isset($dataSource['data']['items'])) {
            try {
                $vendorsList = $this->smartCatService->getDirectoriesManager()
                    ->directoriesGet(['type' => 'vendor'])
                    ->getItems();

                foreach ($vendorsList as $vendor) {
                    $vendors[] = [
                        'id' => $vendor->getId(),
                        'name' => $vendor->getName()
                    ];
                }
            } catch (\Throwable $e) {
            }

            foreach ($dataSource['data']['items'] as &$item) {
                if ($this->getData('name') == 'vendor') {
                    if (trim($item['vendor']) === 0 || !trim($item['vendor'])) {
                        $item[$this->getData('name')] = __('Translate internally');
                    } else {
                        $vendorId = array_search($item['vendor'], array_column($vendors, 'id'));
                        if ($vendorId !== false) {
                            $item[$this->getData('name')] = $vendors[$vendorId]['name'];
                        }
                    }
                }
            }
        }

        return $dataSource;
    }
}
