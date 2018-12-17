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
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use SmartCat\Connector\Model\ProjectEntity;
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

        if (version_compare($context->getVersion(), "1.1.0", "<")) {
            $this->ver110($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function ver102(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->dropForeignKey(
            $setup->getTable(Module::PROJECT_TABLE_NAME),
            $setup->getFkName(
                Module::PROJECT_TABLE_NAME,
                Project::PROFILE_ID,
                Module::PROFILE_TABLE_NAME,
                Profile::ID
            )
        );

        $this->setForeignKey(
            $setup,
            Module::PROJECT_TABLE_NAME,
            Project::PROFILE_ID,
            Module::PROFILE_TABLE_NAME,
            Profile::ID,
            Table::ACTION_CASCADE
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    private function ver110(SchemaSetupInterface $setup)
    {
        if ($setup->tableExists(Module::MANUFACTURER . '_project_product')) {
            $setup->getConnection()->dropTable(Module::MANUFACTURER . '_project_product');
        }

        $this->initTable($setup, Module::PROJECT_ENTITY_TABLE_NAME, $this->getProjectEntityColumns());

        $this->setForeignKey(
            $setup,
            Module::PROJECT_ENTITY_TABLE_NAME,
            ProjectEntity::PROJECT_ID,
            Module::PROJECT_TABLE_NAME,
            Project::ID,
            Table::ACTION_CASCADE
        );
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

            $indexesArray = array_keys(array_filter($columns, function ($val) {
                return $val['type'] == Table::TYPE_TEXT;
            }));

            $connection->createTable($table);

            if (!empty($indexesArray)) {
                $indexName = $setup->getIdxName($localTableName, $indexesArray, AdapterInterface::INDEX_TYPE_FULLTEXT);
                $connection->addIndex(
                    $localTableName,
                    $indexName,
                    $indexesArray,
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                );
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

    /**
     * @return array
     */
    private function getProjectEntityColumns()
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
            'project_id' => [
                'type' => Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'comment' => 'Project ID',
            ],
            'type' => [
                'type' => Table::TYPE_TEXT,
                'size' => null,
                'options' => [
                    'nullable' => true,
                ],
                'comment' => 'Type',
            ],
            'entity_id' => [
                'type' => Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'comment' => 'Entity ID',
            ],
            'status' => [
                'type' => Table::TYPE_TEXT,
                'size' => null,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Document status',
            ],
            'document_id' => [
                'type' => Table::TYPE_TEXT,
                'size' => null,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Smartcat Document ID',
            ],
            'task_id' => [
                'type' => Table::TYPE_TEXT,
                'size' => null,
                'options' => [
                    'nullable' => false,
                ],
                'comment' => 'Export Task ID',
            ],
        ];
    }
}
