<?php
/**
 * Created by PhpStorm.
 * User: medic84
 * Date: 29.11.18
 * Time: 16:49
 */

namespace SmartCat\Сonnector\Helper;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class SetupHelper
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @param $tableName
     * @param array $columns
     * @throws \Zend_Db_Exception
     */
    public function initTable(SchemaSetupInterface $setup, $tableName, array $columns)
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
     */
    public function setForeignKey(
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