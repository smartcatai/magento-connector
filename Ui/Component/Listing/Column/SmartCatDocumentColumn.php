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
use SmartCat\Connector\Helper\ConfigurationHelper;
use SmartCat\Connector\Model\ProjectEntity;

class SmartCatDocumentColumn extends Column
{
    private $configurationHelper;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ConfigurationHelper $configurationHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ConfigurationHelper $configurationHelper,
        array $components = [],
        array $data = []
    ) {
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
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[ProjectEntity::DOCUMENT_ID])) {
                    $item[$this->getData('name')] = $this->getHtml($item[ProjectEntity::DOCUMENT_ID]);
                }
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

    /**
     * @param $documentId
     * @return string
     */
    private function getHtml($documentId)
    {
        $text = __('Go to Smartcat');
        $url = $this->getProjectUrl($documentId);

        if ($url) {
            return "<a href='{$url}' target='_blank'>{$text}</a>";
        }

        return '';
    }
}
