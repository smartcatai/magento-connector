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
use SmartCat\Connector\Module;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();
        $this->initTable($installer, $context, Module::PROJECT_TABLE_NAME, $this->getProjectColumns());
        $this->initTable($installer, $context, Module::PROFILE_TABLE_NAME, $this->getProfileColumns());
        $this->initTable($installer, $context, Module::PROJECT_PRODUCT_TABLE_NAME, $this->getProjectProductColumns());

        $this->setForeignKey(
            $installer,
            Module::PROJECT_TABLE_NAME,
            'profile_id',
            Module::PROFILE_TABLE_NAME,
            'profile_id'
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
            'project_id'
        );

        $installer->endSetup();
    }

    private function setForeignKey(SchemaSetupInterface $installer, $priTableName, $priColumnName, $refTableName, $refColumnName)
    {
        $installer->getConnection()->addForeignKey(
            $installer->getFkName($priTableName, $priColumnName, $refTableName, $refColumnName),
            $installer->getTable($priTableName),
            $priColumnName,
            $installer->getTable($refTableName),
            $refColumnName,
            Table::ACTION_NO_ACTION
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @param $tableName
     * @param array $columns
     * @throws \Zend_Db_Exception
     */
    private function initTable(SchemaSetupInterface $setup, ModuleContextInterface $context, $tableName, array $columns)
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
     * @return array
     */
    private function getProjectColumns()
    {
        return [
            'project_id' => [
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
            'guid' => [
                'type' => Table::TYPE_TEXT,
                'size' => 40,
                'options' => [
                    'nullable' => true,
                ],
                'comment' => 'Project GUID',
            ],
            'element' => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Element',
            ],
            'profile_id' => [
                'type' => Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'comment' => 'Profile',
            ],
            'translate' => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Translate',
            ],
            'status' => [
                'type' => Table::TYPE_TEXT,
                'size' => 15,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Status',
            ],
            'price' => [
                'type' => Table::TYPE_FLOAT,
                'size' => null,
                'options' => [],
                'comment' => 'Price',
            ],
            'deadline' => [
                'type' => Table::TYPE_TIMESTAMP,
                'size' => null,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Deadline Date',
            ],
            'comment' => [
                'type' => Table::TYPE_TEXT,
                'size' => null,
                'options' => [],
                'comment' => 'Comment',
            ],
            'is_stats_builded' => [
                'type' => Table::TYPE_BOOLEAN,
                'size' => null,
                'options' => [
                    'default' => false
                ],
                'comment' => 'Is Statistics Builded',
            ],
            'created_at' => [
                'type' => Table::TYPE_TIMESTAMP,
                'size' => null,
                'options' => [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT
                ],
                'comment' => 'Created At',
            ],
            'updated_at' => [
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
            'profile_id' => [
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
            'vendor' => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => [
                    'nullable' => true,
                ],
                'comment' => 'Vendor',
            ],
            'stages' => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Stages',
            ],
            'source_lang' => [
                'type' => Table::TYPE_TEXT,
                'size' => null,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Source Language',
            ],
            'target_lang' => [
                'type' => Table::TYPE_TEXT,
                'size' => null,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Target Language',
            ],
            'name' => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Profile Name',
            ],
            'project_id' => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => [
                    'nullable' => true,
                ],
                'comment' => 'Project id to send in',
            ],
            'auto_send_new' => [
                'type' => Table::TYPE_BOOLEAN,
                'size' => null,
                'options' => [
                    'nullable' => false,
                    'default' => false
                ],
                'comment' => 'Auto Send New',
            ],
            'auto_send_save' => [
                'type' => Table::TYPE_BOOLEAN,
                'size' => null,
                'options' => [
                    'nullable' => false,
                    'default' => false
                ],
                'comment' => 'Auto Send Save',
            ],
            'batch_send' => [
                'type' => Table::TYPE_BOOLEAN,
                'size' => null,
                'options' => [
                    'nullable' => false,
                    'default' => false
                ],
                'comment' => 'Auto Send Save',
            ],
            'excluded_attributes' => [
                'type' => Table::TYPE_TEXT,
                'size' => null,
                'options' => [
                    'nullable' => true,
                ],
                'comment' => 'Excluded attributes',
            ],
            'created_at' => [
                'type' => Table::TYPE_TIMESTAMP,
                'size' => null,
                'options' => [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT
                ],
                'comment' => 'Created At',
            ],
            'updated_at' => [
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
}
