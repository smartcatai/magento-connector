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

namespace SmartCat\Connector\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use SmartCat\Connector\Helper\LanguageDictionary;
use SmartCat\Connector\Helper\SmartCatFacade;
use Magento\Framework\Message\ManagerInterface;

class LanguagesList implements ArrayInterface
{
    private $smartCatService;
    private $messageManager;

    /**
     * LanguagesList constructor.
     * @param SmartCatFacade $smartCatService
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        SmartCatFacade $smartCatService,
        ManagerInterface $messageManager
    ) {
        $this->smartCatService = $smartCatService;
        $this->messageManager = $messageManager;
    }

    public function toOptionArray()
    {
        $languages = [];

        try {
            $languageList = $this->smartCatService->getDirectoriesManager()
                ->directoriesGet(['type' => 'language'])
                ->getItems();
        } catch (\Throwable $e) {
            return $languages;
        }

        if (isset($languageList)) {
            foreach ($languageList as $language) {
                $label = LanguageDictionary::getNameByCode($language->getId());

                if ($label === false) {
                    continue;
                }

                $languages[] = [
                    'label' => $label . ' - ' . $language->getId(),
                    'value' => $language->getId()
                ];
            }
        } else {
            $this->messageManager->addWarningMessage(__('Languages not found'));
        }

        usort($languages, function ($a, $b) {
            return strnatcmp($a['label'], $b['label']);
        });

        return $languages;
    }
}
