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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use SmartCat\Connector\Module;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), "1.0.2", "<")) {
            $this->ver102($setup);
        }
    }

    private function ver102(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->dropForeignKey(
            $setup->getTable(Module::PROJECT_TABLE_NAME),
            $setup->getFkName(
                Module::PROJECT_TABLE_NAME,
                Project::PROFILE_ID,
                Module::PROFILE_TABLE_NAME,
                Profile::ID)
        );

        $this->setForeignKey(
            $setup,
            Module::PROJECT_TABLE_NAME,
            Project::PROFILE_ID,
            Module::PROFILE_TABLE_NAME,
            Profile::ID,
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->dropForeignKey(
            $setup->getTable(Module::PROJECT_PRODUCT_TABLE_NAME),
            $setup->getFkName(
                Module::PROJECT_PRODUCT_TABLE_NAME,
                'project_id',
                Module::PROJECT_TABLE_NAME,
                Project::ID)
        );

        $this->setForeignKey(
            $setup,
            Module::PROJECT_PRODUCT_TABLE_NAME,
            'project_id',
            Module::PROJECT_TABLE_NAME,
            Project::ID,
            Table::ACTION_CASCADE
        );
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param $priTableName
     * @param $priColumnName
     * @param $refTableName
     * @param $refColumnName
     * @param string $onDelete
     */
    private function setForeignKey(
        SchemaSetupInterface $installer,
        $priTableName,
        $priColumnName,
        $refTableName,
        $refColumnName,
        $onDelete = Table::ACTION_NO_ACTION
    ) {
        $installer->getConnection()->addForeignKey(
            $installer->getFkName($priTableName, $priColumnName, $refTableName, $refColumnName),
            $installer->getTable($priTableName),
            $priColumnName,
            $installer->getTable($refTableName),
            $refColumnName,
            $onDelete
        );
    }
}
