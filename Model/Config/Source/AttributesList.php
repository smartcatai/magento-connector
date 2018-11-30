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

use Magento\Catalog\Model\Category\AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\Message\ManagerInterface;

class AttributesList implements ArrayInterface
{
    private $attributeRepository;
    private $messageManager;
    private $searchCriteriaBuilder;

    public function __construct(
        AttributeRepository $attributeRepository,
        ManagerInterface $messageManager,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->messageManager = $messageManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function toOptionArray()
    {
        $attributes = [];

        try {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $attributesList = $this->attributeRepository->getList($searchCriteria)->getItems();
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Error occurred: ') . $e->getMessage());
            return $attributes;
        }

        if (isset($attributesList)) {
            foreach ($attributesList as $attribute) {
                if ($attribute->getAttributeCode() == 'url_key') {
                    continue;
                }
                if (in_array($attribute->getFrontendInput(), ['text', 'textarea'])) {
                    $attributes[] = [
                        'label' => $attribute->getDefaultFrontendLabel(),
                        'value' => $attribute->getAttributeCode()
                    ];
                }
            }
        } else {
            $this->messageManager->addWarningMessage(__('Attributes not found'));
        }

        return $attributes;
    }
}
