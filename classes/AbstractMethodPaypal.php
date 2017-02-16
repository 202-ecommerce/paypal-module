<?php
abstract class AbstractMethodPaypal
{
    // Force les classes filles à définir cette méthode
    abstract public function init($params);
    abstract public function validation();
    abstract public function confirmCapture();
    abstract public function check();
    abstract public function refund();
    abstract public function setConfig($params);

    public static function load($method)
    {

        if (file_exists(_PS_MODULE_DIR_.'paypal/classes/Method'.$method.'.php')) {
            include_once _PS_MODULE_DIR_.'paypal/classes/Method'.$method.'.php';
            return new MethodEC();
        }
    }
}