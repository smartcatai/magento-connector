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

use SmartCat\Connector\Helper\SmartCatFacade;
use SmartCat\Connector\Model\Profile;

class ProfileActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    const URL_PATH_DELETE = 'smartcat_connector/profile/delete';
    const URL_PATH_EDIT = 'smartcat_connector/profile/edit';
    const URL_PATH_DETAILS = 'smartcat_connector/profile/details';

    private $urlBuilder;
    private $smartCatService;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param SmartCatFacade $smartCatService
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        SmartCatFacade $smartCatService,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
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
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[Profile::ID])) {
                    $item[$this->getData('name')] = [
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    Profile::ID => $item[Profile::ID]
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete "${ $.$data.'. Profile::NAME .' }"'),
                                'message' => __(
                                    'Are you sure you want to delete "${ $.$data.'. Profile::NAME .' }" profile?' .
                                    '<br /> All projects that use this profile will be deleted!'
                                )
                            ]
                        ]
                    ];

                    if ($this->smartCatService->checkCredentials()) {
                        $edit = [
                            'edit' => [
                                'href' => $this->urlBuilder->getUrl(
                                    static::URL_PATH_EDIT,
                                    [
                                        Profile::ID => $item[Profile::ID]
                                    ]
                                ),
                                'label' => __('Edit')
                            ],
                        ];
                        $item[$this->getData(Profile::NAME)] =
                            array_merge($edit, $item[$this->getData(Profile::NAME)]);
                    }
                }
            }
        }
        
        return $dataSource;
    }
}
