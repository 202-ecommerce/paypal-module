<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_0_1($object)
{

    if (!$object->registerHook('displayAdminOrderTabOrder') || !$object->registerHook('actionValidateOrder')) {
        return false;
    }


    $sql = 'SELECT column_name
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE table_name = "'._DB_PREFIX_.'paypal_order"
                AND table_schema = "'._DB_NAME_.'"
                AND column_name = "total_prestashop"';
    $column = Db::getInstance()->getRow($sql);

    if (!$column) {
        $sql = 'ALTER TABLE '._DB_PREFIX_.'paypal_order ADD total_prestashop FLOAT(11)';
    }

    if (!Db::getInstance()->execute($sql)) {
        return false;
    }

    return true;
}
