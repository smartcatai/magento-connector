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
use Magento\Framework\UrlInterface;
use SmartCat\Connector\Helper\ConfigurationHelper;
use SmartCat\Connector\Model\ProjectEntity;

class ProjectEntityActions extends Column
{
    const URL_PATH_SYNC = 'smartcat_connector/entity/sync';

    private $urlBuilder;
    private $configurationHelper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ConfigurationHelper $configurationHelper
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ConfigurationHelper $configurationHelper,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->configurationHelper = $configurationHelper;
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
            foreach ($dataSource['data']['items'] as $key => &$item) {
                $actions = [];
                if (!empty($item[ProjectEntity::DOCUMENT_ID])) {
                    $smartcatProject = [
                        'smartcat_project' => [
                            'href' => $this->getProjectUrl($item[ProjectEntity::DOCUMENT_ID]),
                            'label' => __('Go to Smartcat'),
                            'target' => '_blank',
                        ],
                    ];
                    $actions = array_merge($actions, $smartcatProject);
                }
                if ($dataSource['data']['original_items'][$key]['status'] === ProjectEntity::STATUS_SAVED) {
                    $sync = [
                        'sync' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_SYNC,
                                [ProjectEntity::ID => $item[ProjectEntity::ID]]
                            ),
                            'label' => __('Sync'),
                        ],
                    ];
                    $actions = array_merge($actions, $sync);
                }

                if ($dataSource['data']['original_items'][$key]['status'] === ProjectEntity::STATUS_FAILED) {
                    $sync = [
                        'sync' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_SYNC,
                                [ProjectEntity::ID => $item[ProjectEntity::ID]]
                            ),
                            'label' => __('Sync'),
                        ],
                    ];
                    $actions = array_merge($actions, $sync);
                }

                $item[$this->getData('name')] = $actions;
            }
        }
        
        return $dataSource;
    }

    /**
     * @param $documentId
     * @return string
     */
    private function getProjectUrl($documentId)
    {
        $doc = explode('_', $documentId);

        if (count($doc) != 2) {
            return null;
        }

        return "https://{$this->configurationHelper->getServer()}/Editor?DocumentId={$doc[0]}&LanguageId={$doc[1]}";
    }
}
