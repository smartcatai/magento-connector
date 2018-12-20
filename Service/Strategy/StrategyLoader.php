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

namespace SmartCat\Connector\Service\Strategy;

class StrategyLoader
{
    private $strategies = [];

    public function __construct(PageStrategy $pageStrategy, ProductStrategy $productStrategy)
    {
        $this->strategies[] = $pageStrategy;
        $this->strategies[] = $productStrategy;
    }

    /**
     * @param string $modelClass
     * @return null|StrategyInterface
     */
    public function getStrategyByModel($modelClass)
    {
        /** @var StrategyInterface $strategy */
        foreach ($this->strategies as $strategy) {
            if (in_array($modelClass, $strategy::getAppliedClasses())) {
                return $strategy;
            }
        }

        return null;
    }

    public function getStrategyByType($type)
    {
        /** @var StrategyInterface $strategy */
        foreach ($this->strategies as $strategy) {
            if ($type === $strategy::getType()) {
                return $strategy;
            }
        }

        return null;
    }
}
