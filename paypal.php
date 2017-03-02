<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2017 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}
include_once(_PS_MODULE_DIR_.'paypal/sdk/PaypalSDK.php');
include_once 'classes/AbstractMethodPaypal.php';
include_once 'classes/PaypalCapture.php';
include_once 'classes/PaypalOrder.php';


class PayPal extends PaymentModule
{
    public static $dev = true;
    public $express_checkout;
    public $message;
    public $amount_paid_paypal;

    public function __construct()
    {
        $this->name = 'paypal';
        $this->tab = 'payments_gateways';
        $this->version = '4.1.0';
        $this->author = 'PrestaShop';
        $this->is_eu_compatible = 1;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->controllers = array('payment', 'validation');
        $this->bootstrap = true;

        $this->currencies = true;
        $this->currencies_mode = 'radio';

        parent::__construct();

        $this->displayName = $this->l('PayPal');
        $this->description = $this->l('Accepts payments by credit cards (CB, Visa, MasterCard, Amex, Aurore, Cofinoga, 4 stars) with PayPal.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
        $this->express_checkout = $this->l('PayPal Express Checkout ');
    }

    public function install()
    {
        // Install default
        if (!parent::install()) {
            return false;
        }
        // install DataBase
        if (!$this->installSQL()) {
            return false;
        }
        // Registration hook
        if (!$this->registrationHook()) {
            return false;
        }

        if (!Configuration::updateValue('PAYPAL_MERCHANT_ID', '')
            || !Configuration::updateValue('PAYPAL_API_CREDENTIAL', '')
            || !Configuration::updateValue('PAYPAL_API_USERNAME', '')
            || !Configuration::updateValue('PAYPAL_API_PSWD', '')
            || !Configuration::updateValue('PAYPAL_API_SIGNATURE', '')
            || !Configuration::updateValue('PAYPAL_SANDBOX', 0)
            || !Configuration::updateValue('PAYPAL_API_INTENT', 'sale')
            || !Configuration::updateValue('PAYPAL_API_ADVANTAGES', 1)
            || !Configuration::updateValue('PAYPAL_API_CARD', 0)
            || !Configuration::updateValue('PAYPAL_SANDBOX_CLIENTID', '')
            || !Configuration::updateValue('PAYPAL_SANDBOX_SECRET', '')
            || !Configuration::updateValue('PAYPAL_LIVE_CLIENTID', '')
            || !Configuration::updateValue('PAYPAL_LIVE_SECRET', '')
            || !Configuration::updateValue('PAYPAL_METHOD', '')
        ) {
            return false;
        }

        return true;
    }
    
    /**
     * Install DataBase table
     * @return boolean if install was successfull
     */
    private function installSQL()
    {
        $sql = array();

        $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."paypal_order` (
              `id_paypal_order` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
              `id_order` INT(11),
              `id_cart` INT(11),
              `id_transaction` VARCHAR(55),
              `id_payment` VARCHAR(55),
              `client_token` VARCHAR(255),
              `payment_method` VARCHAR(255),
              `currency` VARCHAR(21),
              `total_paid` FLOAT(11),
              `payment_status` VARCHAR(255),
              `total_prestashop` FLOAT(11),
              `date_add` DATETIME,
              `date_upd` DATETIME
        ) ENGINE = "._MYSQL_ENGINE_;

        $sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "paypal_capture` (
              `id_paypal_capture` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
              `id_capture` VARCHAR(55),
              `id_paypal_order` INT(11),
              `capture_amount` FLOAT(11),
              `result` VARCHAR(255),
              `date_add` DATETIME,
              `date_upd` DATETIME
        ) ENGINE = " . _MYSQL_ENGINE_ ;

        foreach ($sql as $q) {
            if (!DB::getInstance()->execute($q)) {
                return false;
            }
        }

        return true;
    }

    /**
     * [registrationHook description]
     * @return [type] [description]
     */
    private function registrationHook()
    {
        if (!$this->registerHook('paymentOptions')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('displayOrderConfirmation')
            || !$this->registerHook('displayAdminOrder')
            || !$this->registerHook('ActionOrderStatusPostUpdate')
            || !$this->registerHook('actionValidateOrder')
        ) {
            return false;
        }


        return true;
    }

    public function uninstall()
    {
        $config = array(
            'PAYPAL_SANDBOX',
            'PAYPAL_API_INTENT',
            'PAYPAL_API_ADVANTAGES',
            'PAYPAL_API_CARD',
            'PAYPAL_SANDBOX_CLIENTID',
            'PAYPAL_SANDBOX_SECRET',
            'PAYPAL_LIVE_CLIENTID',
            'PAYPAL_LIVE_SECRET',
            'PAYPAL_METHOD',
        );
        foreach ($config as $var) {
            Configuration::deleteByName($var);
        }

        //Uninstall DataBase
        if (!$this->uninstallSQL()) {
            return false;
        }

        // Uninstall default
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    /**
     * Uninstall DataBase table
     * @return boolean if install was successfull
     */
    private function uninstallSQL()
    {
        $sql = array();

        $sql[] = "DROP TABLE IF EXISTS `"._DB_PREFIX_."paypal_capture`";

        $sql[] = "DROP TABLE IF EXISTS `"._DB_PREFIX_."paypal_order`";

        foreach ($sql as $q) {
            if (!DB::getInstance()->execute($q)) {
                return false;
            }
        }

        return true;
    }

    public function getUrl()
    {
        if (Configuration::get('PAYPAL_SANDBOX')) {
            return 'https://www.sandbox.paypal.com/';
        } else {
            return 'https://www.paypal.com/';
        }
    }

    public function getContent()
    {

      /*  $test = "{\"success\":true,\"data\":{\"url\":\"https:\/\/www.sandbox.paypal.com\/FR\/merchantsignup\/partner\/onboardingentry?token=NTBlNjdiYmEtMjU4Ny00ODcyLTg3NzMtYTkzNTc1NDE5NGJhNG5ET3lva1RVamhkRCtoZWlwRHFRaU5LalhVaUYyVGJxVEdJeHdXWmVXbz0=&context_token=6148968567513348096\"},\"error\":null}";
//print_r(Tools::jsonDecode($test));
        $response = $this->getPartnerInfo('EXPRESS_CHECKOUT');
        print_r($response);die;*/

        $this->_postProcess();
        $return_url = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        /*
        $PartnerboardingURL = "";
        if ((Configuration::get('PAYPAL_SANDBOX') && !Configuration::get('PAYPAL_LIVE_ACCESS'))
        || (!Configuration::get('PAYPAL_SANDBOX') && !Configuration::get('PAYPAL_SANDBOX_ACCESS'))) {
            $partner_info = $this->getUrlOnboarding(Configuration::get('PAYPAL_METHOD'));
            if (!$partner_info->error) {
                $PartnerboardingURL = $partner_info->data->link;
            }
        }
        */


        if (Configuration::get('PAYPAL_SANDBOX')) {
            if (Configuration::get('PAYPAL_API_USERNAME') != '' && Configuration::get('PAYPAL_API_PSWD') != ''  && Configuration::get('PAYPAL_API_SIGNATURE') != '') {
                $ec_card_active = Configuration::get('PAYPAL_API_CARD');
                $ec_paypal_active = !Configuration::get('PAYPAL_API_CARD');
            } else {
                $ec_card_active = false;
                $ec_paypal_active = false;
            }
        } else {
            if (Configuration::get('PAYPAL_API_USERNAME') != '' && Configuration::get('PAYPAL_API_PSWD') != ''  && Configuration::get('PAYPAL_API_SIGNATURE') != '') {
                $ec_card_active = Configuration::get('PAYPAL_API_CARD');
                $ec_paypal_active = !Configuration::get('PAYPAL_API_CARD');
            } else {
                $ec_card_active = false;
                $ec_paypal_active = false;
            }
        }

        $this->context->smarty->assign(array(
            'path' => $this->_path,
            //'path_ajax_sandbox' => $this->context->link->getAdminLink('AdminModules',true,array(),array('configure'=>'paypal')),
            'country' => Country::getNameById($this->context->language->id, $this->context->country->id),
            'localization' => $this->context->link->getAdminLink('AdminLocalization', true),
            'preference' => $this->context->link->getAdminLink('AdminPreferences', true),
            'active_products' => $this->express_checkout,
            'return_url' => $return_url,
             'access_token_sandbox' => Configuration::get('PAYPAL_SANDBOX_ACCESS'),
             'access_token_live' => Configuration::get('PAYPAL_LIVE_ACCESS'),
            'PAYPAL_SANDBOX_CLIENTID' => Configuration::get('PAYPAL_SANDBOX_CLIENTID'),
            'PAYPAL_SANDBOX_SECRET' => Configuration::get('PAYPAL_SANDBOX_SECRET'),
            'PAYPAL_LIVE_CLIENTID' => Configuration::get('PAYPAL_LIVE_CLIENTID'),
            'PAYPAL_LIVE_SECRET' => Configuration::get('PAYPAL_LIVE_SECRET'),
            'paypal_card' => Configuration::get('PAYPAL_API_CARD'),
            'ec_card_active' => $ec_card_active,
            'ec_paypal_active' => $ec_paypal_active,
            'need_rounding' => Configuration::get('PS_ROUND_TYPE') == Order::ROUND_ITEM ? 0 : 1,
        ));
        $this->context->controller->addCSS($this->_path.'views/css/paypal-bo.css', 'all');

        $fields_form = array();
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('MODULE SETTINGS'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Activate sandbox'),
                    'name' => 'paypal_sandbox',
                    'is_bool' => true,
                    'hint' => $this->l('Set up a test environment in your PayPal account (only if you are a developer)'),
                    'values' => array(
                        array(
                            'id' => 'paypal_sandbox_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'paypal_sandbox_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Payment action'),
                    'name' => 'paypal_intent',
                    'desc' => $this->l(''),
                    'hint' => $this->l('Sale: the money moves instantly from the buyer�s account to the seller�s account at the time of payment. Authorization/capture: The authorized mode is a deferred mode of payment that requires the funds to be collected manually when you want to transfer the money. This mode is used if you want to ensure that you have the merchandise before depositing the money, for example. Be careful, you have 29 days to collect the funds.'),
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 'sale',
                                'name' => $this->l('Sale')
                            ),
                            array(
                                'id' => 'authorization',
                                'name' => $this->l('Authorize')
                            )
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Accept credit and debit card payment'),
                    'name' => 'paypal_card',
                    'is_bool' => true,
                    'hint' => $this->l('Your customers can pay with debit and credit cards as well as local payment systems whether or not they use PayPal'),
                    'values' => array(
                        array(
                            'id' => 'paypal_card_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'paypal_card_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Show PayPal benefits to your customers'),
                    'name' => 'paypal_show_advantage',
                    'desc' => $this->l(''),
                    'is_bool' => true,
                    'hint' => $this->l('You can increase your conversion rate by presenting PayPal benefits to your customers on payment methods selection page.'),
                    'values' => array(
                        array(
                            'id' => 'paypal_show_advantage_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'paypal_show_advantage_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right button',
            ),
        );

        $fields_value = array(
            'paypal_sandbox' => Configuration::get('PAYPAL_SANDBOX'),
            'paypal_intent' => Configuration::get('PAYPAL_API_INTENT'),
            'paypal_card' => Configuration::get('PAYPAL_API_CARD'),
            'paypal_show_advantage' => Configuration::get('PAYPAL_API_ADVANTAGES'),
        );
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->title = $this->displayName;
        $helper->show_toolbar = false;
        $helper->submit_action = 'paypal_config';
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->tpl_vars = array(
            'fields_value' => $fields_value,
            'id_language' => $this->context->language->id,
            'back_url' => $this->context->link->getAdminLink('AdminModules')
                .'&configure='.$this->name
                .'&tab_module='.$this->tab
                .'&module_name='.$this->name
                .'#paypal_params'
        );
        $form = $helper->generateForm($fields_form);
        if (count($this->_errors)) {
            $this->message .= $this->displayError($this->_errors);
        } elseif (Configuration::get('PAYPAL_SANDBOX') == 1) {
            $this->message .= $this->displayWarning($this->l('Your PayPal account is currently configured to accept payments on the Sandbox (test environment). Any transaction will be fictitious. Disable the option, to accept actual payments (production environment) and log in with your PayPal credentials'));
        } elseif (Configuration::get('PAYPAL_SANDBOX') == 0) {
            $this->message .= $this->displayConfirmation($this->l('Your PayPal account is properly connected, you can now receive payments'));
        }
        $block_info = '';
        if (Configuration::get('PS_ROUND_TYPE') != Order::ROUND_ITEM) {
            $block_info = $this->display(__FILE__, 'views/templates/admin/block_info.tpl');
        }

        return $this->message.$block_info.$this->display(__FILE__, 'views/templates/admin/configuration.tpl').$form;
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('paypal_config')) {
            Configuration::updateValue('PAYPAL_SANDBOX', Tools::getValue('paypal_sandbox'));
            Configuration::updateValue('PAYPAL_API_INTENT', Tools::getValue('paypal_intent'));
            Configuration::updateValue('PAYPAL_API_CARD', Tools::getValue('paypal_card'));
            Configuration::updateValue('PAYPAL_API_ADVANTAGES', Tools::getValue('paypal_show_advantage'));
        }
/*
        if (Tools::getValue('activate_method')) {
            Configuration::updateValue('PAYPAL_EXPRESS_CHECKOUT', 1);
            Configuration::updateValue('PAYPAL_METHOD', Tools::getValue('activate_method'));

            Configuration::updateValue('PAYPAL_SANDBOX_ACCESS', 1);
            Configuration::updateValue('PAYPAL_LIVE_ACCESS', 1);
        }
*/
       /* if (Tools::isSubmit('save_credentials')) {
            $sandbox = Tools::getValue('sandbox');
            $live = Tools::getValue('live');

            Configuration::updateValue('PAYPAL_SANDBOX_CLIENTID', $sandbox['client_id']);
            Configuration::updateValue('PAYPAL_SANDBOX_SECRET', $sandbox['secret']);

            Configuration::updateValue('PAYPAL_LIVE_CLIENTID', $live['client_id']);
            Configuration::updateValue('PAYPAL_LIVE_SECRET', $live['secret']);

            Configuration::updateValue('PAYPAL_API_CARD', Tools::getValue('with_card'));
            Configuration::updateValue('PAYPAL_EXPRESS_CHECKOUT', 1);
            Configuration::updateValue('PAYPAL_METHOD', Tools::getValue('method'));
        }

        if (Tools::isSubmit('save_rounding_settings')) {
            Configuration::updateValue('PAYPAL_SANDBOX', 0);
            Configuration::updateValue('PS_ROUND_TYPE', Order::ROUND_ITEM);
            Tools::redirect($this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
        }

        switch (Configuration::get('PAYPAL_METHOD')) {
            case 'EXPRESS_CHECKOUT':
                $method = AbstractMethodPaypal::load('EC');
            break;
        }*/

        if (Tools::getValue('method')) {
            Configuration::updateValue('PAYPAL_API_CARD', Tools::getValue('with_card'));
            Configuration::updateValue('PAYPAL_EXPRESS_CHECKOUT', 1);
            Configuration::updateValue('PAYPAL_METHOD', Tools::getValue('method'));

            $response = $this->getPartnerInfo(Tools::getValue('method'));
            $result = Tools::jsonDecode($response);
            if (!$result->error && isset($result->data->url)) {
                $PartnerboardingURL = $result->data->url;
               // print_r($PartnerboardingURL);die;
                Tools::redirectLink($PartnerboardingURL);
            }
        }
    }

    public function hookPaymentOptions($params)
    {
        $payment_options = new PaymentOption();
        $action_text = $this->l('Pay with Paypal');
        $payment_options->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/paypal_sm.png'));
        if (Configuration::get('PAYPAL_API_ADVANTAGES')) {
            $action_text .= ' | '.$this->l('It\'s easy, simple and secure');
        }
        $this->context->smarty->assign(array(
            'path' => $this->_path,
        ));
        $payment_options->setCallToActionText($action_text);
        $payment_options->setAction($this->context->link->getModuleLink($this->name, 'ec_init', array('credit_card'=>'0'), true));
        $payment_options->setAdditionalInformation($this->context->smarty->fetch('module:paypal/views/templates/front/payment_infos.tpl'));

        $payments_options = [
            $payment_options,
        ];

        if (Configuration::get('PAYPAL_API_CARD')) {
            $payment_options = new PaymentOption();
            $action_text = $this->l('Pay with debit or credit card');
            $payment_options->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/logo_card.png'));
            $payment_options->setCallToActionText($action_text);
            $payment_options->setAction($this->context->link->getModuleLink($this->name, 'ec_init', array('credit_card'=>'1'), true));
            $payment_options->setAdditionalInformation($this->context->smarty->fetch('module:paypal/views/templates/front/payment_infos_card.tpl'));
            $payments_options[] = $payment_options;
        }

        return $payments_options;
    }

    public function hookPaymentReturn($params)
    {
    }

    public function hookDisplayOrderConfirmation($params)
    {
    }

    public function validateOrder($id_cart, $id_order_state, $amount_paid, $payment_method = 'Unknown', $message = null, $transaction = array(), $currency_special = null, $dont_touch_amount = false, $secure_key = false, Shop $shop = null)
    {
        $this->amount_paid_paypal = (float)$amount_paid;
        $cart = new Cart((int) $id_cart);
        $total_ps = (float)$cart->getOrderTotal(true, Cart::BOTH);
        parent::validateOrder(
            (int) $id_cart,
            (int) $id_order_state,
            (float) $total_ps,
            $payment_method,
            $message,
            array('transaction_id' => $transaction['PAYMENTINFO_0_TRANSACTIONID']),
            $currency_special,
            $dont_touch_amount,
            $secure_key,
            $shop
        );

        $paypal_order = new PaypalOrder();

        $paypal_order->id_order = $this->currentOrder;
        $paypal_order->id_cart = Context::getContext()->cart->id;
        $paypal_order->id_transaction = $transaction['PAYMENTINFO_0_TRANSACTIONID'];
        $paypal_order->id_payment = $transaction['TOKEN'];
        $paypal_order->client_token = "";
        $paypal_order->payment_method = $transaction['PAYMENTINFO_0_PAYMENTTYPE'];
        $paypal_order->currency = $transaction['PAYMENTINFO_0_CURRENCYCODE'];
        $paypal_order->total_paid = (float) $amount_paid;
        $paypal_order->payment_status = $transaction['PAYMENTINFO_0_PAYMENTSTATUS'];
        $paypal_order->total_prestashop = (float) $total_ps;
        $paypal_order->save();

        if ($transaction['PAYMENTINFO_0_PAYMENTSTATUS'] = "Pending" && $transaction['PAYMENTINFO_0_PENDINGREASON'] == "authorization") {
            $paypal_capture = new PaypalCapture();
            $paypal_capture->id_paypal_order = $paypal_order->id;
            $paypal_capture->save();
        }
    }

    public function hookActionValidateOrder($params)
    {
        $order = $params['order'];
        $amount_paid = (float) $this->amount_paid_paypal;
        if (isset($amount_paid) && $amount_paid != 0 && $order->total_paid != $amount_paid) {
            $order->total_paid = $amount_paid;
            $order->total_paid_real = $amount_paid;
            $order->total_paid_tax_incl = $amount_paid;
            $order->update();

            $sql = 'UPDATE `'._DB_PREFIX_.'order_payment`
		    SET `amount` = '.(float)$amount_paid.'
		    WHERE  `order_reference` = "'.pSQL($order->reference).'"';
            Db::getInstance()->execute($sql);
        }
    }


    public function hookDisplayAdminOrder($params)
    {
        $id_order = $params['id_order'];
        $order = new Order((int)$id_order);
        $paypal_msg = '';
        $paypal_order = PaypalOrder::getOrderById($id_order);

        if ($paypal_order['total_paid'] != $paypal_order['total_prestashop']) {
            $preferences = $this->context->link->getAdminLink('AdminPreferences', true);
            $paypal_msg .= $this->displayWarning('<p class="paypal-warning">'.$this->l('Product pricing has been modified as your rounding settings aren\'t compliant with PayPal.').' '.
                $this->l('To avoid automatic rounding to customer for PayPal payments, please update your rounding settings.').' '.
                '<a target="_blank" href="'.$preferences.'">'.$this->l('Reed more.').'</a></p>'
            );
        }

        if (Tools::getValue('capturePaypal')) {
            $method_ec = AbstractMethodPaypal::load('EC');
            $capture_response = $method_ec->confirmCapture();
            //print_r($capture_response);die;
            if ($order->current_state != Configuration::get('PS_OS_PAYMENT') && $capture_response['ACK'] == 'Success') {
                $order->setCurrentState(_PS_OS_PAYMENT_);
                Tools::redirect($_SERVER['HTTP_REFERER']);
            } elseif (isset($refund_response['L_LONGMESSAGE0'])) {
                $paypal_msg .= $this->displayWarning($this->l("We have problem during capture operation : ").$capture_response['L_LONGMESSAGE0']);
            }
        }
        if (Tools::getValue('refundPaypal')) {
            $method_ec = AbstractMethodPaypal::load('EC');
            $refund_response = $method_ec->refund();
            //print_r($refund_response);die;
            if (isset($refund_response['REFUNDTRANSACTIONID'])) {
                $order->setCurrentState(Configuration::get('PS_OS_REFUND'));
                Tools::redirect($_SERVER['HTTP_REFERER']);
            } elseif (isset($refund_response['L_LONGMESSAGE0'])) {
                $paypal_msg .= $this->displayWarning($this->l("We have problem during refund operation : ").$refund_response['L_LONGMESSAGE0']);
            }
        }

        $current_state = $order->getCurrentState();
        $order_link = Context::getContext()->link->getAdminLink('AdminOrders')."&id_order=".$id_order."&vieworder";
        $this->context->smarty->assign(array(
            'path_logo' => Tools::getHttpHost(true).'/modules/paypal/views/img/paypal_icon.png',
            'order_link' => $order_link,
        ));
        if ($current_state == Configuration::get('PS_OS_REFUND')) {
            $this->context->smarty->assign(array(
                'paypal_refunded' => true,
            ));
        }

        $paypal_order = PaypalOrder::getOrderById($id_order);
        $refund = array('refundPaypal' => $paypal_order['id_paypal_order']);
        
        $paypal_capture = PaypalCapture::getByOrderId($id_order);
        
        if ($paypal_capture) {
            //print_r($paypal_capture);die;
            $refund['capture_id'] = $paypal_capture['id_capture'];
            if ($paypal_capture['result'] == "Completed") {
                $this->context->smarty->assign(array(
                    'refund_link' => '&'.http_build_query($refund),
                ));
            } elseif ($paypal_capture['result'] != 'voided') {
                $this->context->smarty->assign(array(
                    'capture_link' => "&capturePaypal=".$paypal_capture['id_paypal_order'],
                ));
            }
        } else {
            $this->context->smarty->assign(array(
                'refund_link' => '&'.http_build_query($refund),
            ));
        }
        return $paypal_msg.$this->display(__FILE__, 'views/templates/hook/paypal_order.tpl');
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        if ($params['newOrderStatus']->id == Configuration::get('PS_OS_CANCELED')) {
            $orderPayPal = PaypalOrder::loadByOrderId($params['id_order']);
            $method = AbstractMethodPaypal::load('EC');
            $response = $method->void(array('authorization_id'=>$orderPayPal->id_transaction));

            if (isset($response['AUTHORIZATIONID']) && $response['ACK'] == 'Success') {
                $paypalCapture = PaypalCapture::loadByOrderPayPalId($orderPayPal->id);
                $paypalCapture->result = 'voided';
                $paypalCapture->save();
                $orderPayPal->payment_status = 'voided';
                $orderPayPal->save();
            }
        }
    }

    public function getPartnerInfo($method)
    {
        $return_url = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        if (Configuration::get('PS_SSL_ENABLED')) {
            $shop_url = Tools::getShopDomainSsl(true);
        } else {
            $shop_url = Tools::getShopDomain(true);
        }

        $partner_info = array(
            'email' => Configuration::get('PS_SHOP_EMAIL'),
            'shop_url' => $return_url,
            'address1' => Configuration::get('PS_SHOP_ADDR1'),
            'city' => Configuration::get('PS_SHOP_CITY'),
            'country_code' => Tools::strtoupper($this->context->country->iso_code),
            'postal_code' => Configuration::get('PS_SHOP_CODE'),
        );

        $sdk = new PaypalSDK(Configuration::get('PAYPAL_SANDBOX'));
        $response = $sdk->getUrlOnboarding($partner_info);
        // print_r($response);die;
        return $response;
    }
}
