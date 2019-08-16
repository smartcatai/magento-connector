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

namespace SmartCat\Connector\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), "1.2.0", "<")) {
            $this->ver120($setup);
        }

        if (version_compare($context->getVersion(), "1.2.2", "<")) {
            $this->ver122($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function ver120(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->query(
            "UPDATE smartcat_connector_project_entity SET target_lang = (SELECT SUBSTRING_INDEX(type, '|', -1));"
        );

        $setup->getConnection()->query(
            "UPDATE smartcat_connector_project_entity SET type = (SELECT SUBSTRING_INDEX(type, '|', 2));"
        );

        $setup->getConnection()->query(
            "UPDATE smartcat_connector_project_entity SET entity = (SELECT SUBSTRING_INDEX(type, '|', 1));"
        );

        $setup->getConnection()->query(
            "UPDATE smartcat_connector_project_entity SET type = (SELECT SUBSTRING_INDEX(type, '|', -1));"
        );
    }

    private function ver122(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->query(
            "UPDATE smartcat_connector_project_entity SET source_lang = 'en';"
        );
    }
}
