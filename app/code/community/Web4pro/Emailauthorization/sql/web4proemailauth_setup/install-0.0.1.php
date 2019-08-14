<?php

$installer = $this;
$tableEmailAuth = $installer->getTable('web4proemailauth/emailauthorization');

$installer->startSetup();

$installer->getConnection()->dropTable($tableEmailAuth);
$table = $installer->getConnection()
    ->newTable($tableEmailAuth)
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
    ))
    ->addColumn('token', Varien_Db_Ddl_Table::TYPE_TEXT, '64', array(
        'nullable' => false,
    ))
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, '', array(
        'nullable' => false,
    ))
    ->addColumn('expiration', Varien_Db_Ddl_Table::TYPE_TIME, '', array(
        'nullable' => false,
    ));
$installer->getConnection()->createTable($table);

$installer->endSetup();