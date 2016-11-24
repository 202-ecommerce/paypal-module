<?php

include_once('../../config/config.inc.php');
include_once('../../init.php');

$log = 'Appel du ' . date('d/m/Y à H:i:s') . "\n" . 'accessToken : ' . $_POST['accessToken'] . "\n" . 'expiresAt : ' . $_POST['expiresAt'] . "\n" . 'refreshToken : ' . $_POST['refreshToken'] . "\n";

//$log = 'Appel du ' . date('d/m/Y à H:i') . "\n" . 'accessToken : ' . $_GET['accessToken'] . "\n" . 'expiresAt : ' . $_GET['expiresAt'] . "\n" . 'refreshToken : ' . $_GET['refreshToken'] . "\n";

file_put_contents(dirname(__FILE__).'/log/log.txt', $log, FILE_APPEND);

Configuration::updateValue('PAYPAL_BRAINTREE_ACCESS_TOKEN', Tools::getValue('accessToken'));
Configuration::updateValue('PAYPAL_BRAINTREE_EXPIRES_AT', Tools::getValue('expiresAt'));
Configuration::updateValue('PAYPAL_BRAINTREE_REFRESH_TOKEN', Tools::getValue('refreshToken'));

//Tools::redirect('modules/paypal/');