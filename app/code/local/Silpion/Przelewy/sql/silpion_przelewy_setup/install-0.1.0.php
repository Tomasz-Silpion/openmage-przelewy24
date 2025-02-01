<?php

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();


$tableName = $installer->getTable('przelewy24/payment');
if (!$installer->getConnection()->isTableExists($tableName)) {
    // Define table
    $table = $installer->getConnection()
        ->newTable($tableName)
        ->addColumn(
            'payment_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
            ],
            'Payment ID'
        )
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            11,
            [
                'nullable' => true,
            ],
            'Entity ID'
        )
        ->addColumn(
            'entity_type',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            32,
            [
                'nullable' => false,
                'default'  => '',
            ],
            'Entity Type'
        )
        ->addColumn(
            'session_id',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            64,
            [
                'nullable' => true,
            ],
            'Session Identifier'
        )
        ->addColumn(
            'amount',
            Varien_Db_Ddl_Table::TYPE_DECIMAL,
            '12,4',
            [
                'nullable' => false,
                'default'  => '0.0000',
            ],
            'Amount To Pay'
        )
        ->addColumn(
            'currency_code',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            3,
            [
                'nullable' => false,
            ],
            'Currency Code'
        )
        ->addColumn(
            'transaction_id',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            64,
            [
                'nullable' => true,
            ],
            'Transaction Identifier'
        )
        ->addColumn(
            'status',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            20,
            [
                'nullable' => true,
            ],
            'Payment Status'
        )
        ->addColumn(
            'created_at',
            Varien_Db_Ddl_Table::TYPE_DATETIME,
            null,
            array(),
            'Created At'
        )
        ->addColumn(
            'updated_at',
            Varien_Db_Ddl_Table::TYPE_DATETIME,
            null,
            array(),
            'Updated At'
        )
        ->setComment('Przelewy Payment Table');

    $installer->getConnection()->createTable($table);
}

$installer->endSetup();
