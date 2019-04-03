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
use SmartCat\Connector\Service\Strategy\StrategyLoader;

class EntityList implements ArrayInterface
{
    private $strategyLoader;

    /**
     * ProfilesList constructor.
     * @param StrategyLoader $strategyLoader
     */
    public function __construct(StrategyLoader $strategyLoader)
    {
        $this->strategyLoader = $strategyLoader;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $entities = [];

        $entityList = $this->strategyLoader->getEntityNames();

        if (!empty($entityList)) {
            foreach ($entityList as $entity) {
                $entities[] = [
                    'label' => ucfirst($entity),
                    'value' => $entity
                ];
            }
        }

        return $entities;
    }
}
