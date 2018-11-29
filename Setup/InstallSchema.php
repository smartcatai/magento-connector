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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Module;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $installer
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();
        $this->initTable($installer, Module::PROJECT_TABLE_NAME, $this->getProjectColumns());
        $this->initTable($installer, Module::PROFILE_TABLE_NAME, $this->getProfileColumns());
        $this->initTable($installer, Module::PROJECT_PRODUCT_TABLE_NAME, $this->getProjectProductColumns());

        $this->setForeignKey(
            $installer,
            Module::PROJECT_TABLE_NAME,
            Project::PROFILE_ID,
            Module::PROFILE_TABLE_NAME,
            Profile::ID
        );

        $this->setForeignKey(
            $installer,
            Module::PROJECT_PRODUCT_TABLE_NAME,
            'product_id',
            'catalog_product_entity',
            'entity_id'
        );

        $this->setForeignKey(
            $installer,
            Module::PROJECT_PRODUCT_TABLE_NAME,
            'project_id',
            Module::PROJECT_TABLE_NAME,
            Project::ID
        );

        $installer->getConnection()->addIndex(
            $installer->getTable(Module::PROJECT_PRODUCT_TABLE_NAME),
            $installer->getIdxName(
                $installer->getTable(Module::PROJECT_PRODUCT_TABLE_NAME),
                ['project_id', 'product_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['project_id', 'product_id'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );

        $installer->endSetup();
    }

    /**
     * @return array
     */
    private function getProjectColumns()
    {
        return [
            Project::ID => [
                'type' => Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'comment' => 'Project ID',
            ],
            Project::GUID => [
                'type' => Table::TYPE_TEXT,
                'size' => 40,
                'options' => [
                    'nullable' => true,
                ],
                'comment' => 'Project GUID',
            ],
            Project::ELEMENT => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Element',
            ],
            Project::PROFILE_ID => [
                'type' => Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'comment' => 'Profile',
            ],
            Project::TRANSLATE => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Translate',
            ],
            Project::STATUS => [
                'type' => Table::TYPE_TEXT,
                'size' => 15,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Status',
            ],
            Project::PRICE => [
                'type' => Table::TYPE_FLOAT,
                'size' => null,
                'options' => [],
                'comment' => 'Price',
            ],
            Project::DEADLINE => [
                'type' => Table::TYPE_TIMESTAMP,
                'size' => null,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Deadline Date',
            ],
            Project::COMMENT => [
                'type' => Table::TYPE_TEXT,
                'size' => null,
                'options' => [],
                'comment' => 'Comment',
            ],
            Project::IS_STATS_BUILDED => [
                'type' => Table::TYPE_BOOLEAN,
                'size' => null,
                'options' => [
                    'default' => false
                ],
                'comment' => 'Is Statistics Builded',
            ],
            Project::CREATED_AT => [
                'type' => Table::TYPE_TIMESTAMP,
                'size' => null,
                'options' => [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT
                ],
                'comment' => 'Created At',
            ],
            Project::UPDATED_AT => [
                'type' => Table::TYPE_TIMESTAMP,
                'size' => null,
                'options' => [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT_UPDATE
                ],
                'comment' => 'Updated At',
            ],
        ];
    }

    /**
     * @return array
     */
    private function getProfileColumns()
    {
        return [
            Profile::ID => [
                'type' => Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'comment' => 'Profile ID',
            ],
            Profile::VENDOR => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => [
                    'nullable' => true,
                ],
                'comment' => 'Vendor',
            ],
            Profile::STAGES => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Stages',
            ],
            Profile::SOURCE_LANG => [
                'type' => Table::TYPE_TEXT,
                'size' => null,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Source Language',
            ],
            Profile::TARGET_LANG => [
                'type' => Table::TYPE_TEXT,
                'size' => null,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Target Language',
            ],
            Profile::NAME => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Profile Name',
            ],
            Profile::PROJECT_GUID => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => [
                    'nullable' => true,
                ],
                'comment' => 'Project id to send in',
            ],
            Profile::AUTO_SEND_NEW => [
                'type' => Table::TYPE_BOOLEAN,
                'size' => null,
                'options' => [
                    'nullable' => false,
                    'default' => false
                ],
                'comment' => 'Auto Send New',
            ],
            Profile::AUTO_SEND_SAVE => [
                'type' => Table::TYPE_BOOLEAN,
                'size' => null,
                'options' => [
                    'nullable' => false,
                    'default' => false
                ],
                'comment' => 'Auto Send Save',
            ],
            Profile::BATCH_SEND => [
                'type' => Table::TYPE_BOOLEAN,
                'size' => null,
                'options' => [
                    'nullable' => false,
                    'default' => false
                ],
                'comment' => 'Auto Send Save',
            ],
            Profile::EXCLUDED_ATTRIBUTES => [
                'type' => Table::TYPE_TEXT,
                'size' => null,
                'options' => [
                    'nullable' => true,
                ],
                'comment' => 'Excluded attributes',
            ],
            Profile::CREATED_AT => [
                'type' => Table::TYPE_TIMESTAMP,
                'size' => null,
                'options' => [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT
                ],
                'comment' => 'Created At',
            ],
            Profile::UPDATED_AT => [
                'type' => Table::TYPE_TIMESTAMP,
                'size' => null,
                'options' => [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT_UPDATE
                ],
                'comment' => 'Updated At',
            ],
        ];
    }

    /**
     * @return array
     */
    private function getProjectProductColumns()
    {
        return [
            'id' => [
                'type' => Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'comment' => 'ID',
            ],
            'product_id' => [
                'type' => Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'comment' => 'Product ID',
            ],
            'project_id' => [
                'type' => Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'comment' => 'Project ID',
            ],
        ];
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @param $tableName
     * @param array $columns
     * @throws \Zend_Db_Exception
     */
    private function initTable(SchemaSetupInterface $setup, $tableName, array $columns)
    {
        $connection = $setup->getConnection();

        if (!$setup->tableExists($tableName)) {
            $localTableName = $setup->getTable($tableName);

            $table = $connection->newTable($localTableName);

            foreach ($columns as $name => $values) {
                $table->addColumn(
                    $name,
                    $values['type'],
                    $values['size'],
                    $values['options'],
                    $values['comment']
                );
            }

            $indexesArray = array_keys(array_filter($columns, function($val) {
                return $val['type'] == Table::TYPE_TEXT;
            }));

            $connection->createTable($table);

            if (count($indexesArray) > 0) {
                $indexName = $setup->getIdxName($localTableName, $indexesArray, AdapterInterface::INDEX_TYPE_FULLTEXT);
                $connection->addIndex($localTableName, $indexName, $indexesArray, AdapterInterface::INDEX_TYPE_FULLTEXT);
            }
        }
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
