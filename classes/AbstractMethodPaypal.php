<?php
abstract class AbstractMethodPaypal
{
    // Force les classes filles à définir cette méthode
    abstract public function init();
    abstract public function validation();
    abstract public function confirmCapture();
    abstract public function check();
    abstract public function refund();

    public static function load($method)
    {

        if (file_exists(_PS_MODULE_DIR_.'paypal/classes/Method'.$method.'.php')) {
            return new MethodEC();
        }
    }
}