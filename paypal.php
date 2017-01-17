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
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PayPal extends PaymentModule
{

    public static $dev = true;
    public $express_checkout;
    public $message;

    public function __construct()
    {
        $this->name = 'paypal';
        $this->tab = 'payments_gateways';
        $this->version = '4.0.0';
        $this->author = 'PrestaShop';
        $this->is_eu_compatible = 1;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->controllers = array('payment', 'validation');
        $this->bootstrap = true;

        $this->currencies = true;
        $this->currencies_mode = 'radio';

        parent::__construct();
        $this->includeFiles();

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
            || !Configuration::updateValue('PAYPAL_API_INTENT', 0)
            || !Configuration::updateValue('PAYPAL_API_ADVANTAGES', 0)
            || !Configuration::updateValue('PAYPAL_API_CARD', 0)
        ) {
            return false;
        }

        return true;

    }

    private function includeFiles()
    {
        $path = $this->getLocalPath().'classes'.DIRECTORY_SEPARATOR;
        foreach (scandir($path) as $class) {
            if ($class != "index.php" && is_file($path.$class)) {
                $class_name = Tools::substr($class, 0, -4);
                if ($class_name != 'index' && !preg_match('#\.old#isD', $class) && !class_exists($class_name)) {
                    require_once $path.$class_name.'.php';
                }
            }
        }

        $path .= '..'.DIRECTORY_SEPARATOR.'sdk'.DIRECTORY_SEPARATOR;

        foreach (scandir($path) as $class) {
            if ($class != "index.php" && is_file($path.$class)) {
                $class_name = Tools::substr($class, 0, -4);
                if ($class_name != 'index' && !preg_match('#\.old#isD', $class) && !class_exists($class_name)) {
                    require_once $path.$class_name.'.php';
                }
            }
        }
    }

    /**
     * Install DataBase table
     * @return boolean if install was successfull
     */
    private function installSQL()
    {

        # Install All Object Model SQL via install function
        $path = $this->getLocalPath().'classes'.DIRECTORY_SEPARATOR;
        $classes = scandir($path);
        foreach ($classes as $class) {
            if ($class != 'index.php' && !preg_match('#\.old#isD', $class) && is_file($path.$class)) {
                $class_name = Tools::substr($class, 0, -4);
                // Check if class_name is an existing Class or not
                if (class_exists($class_name)) {
                    if (method_exists($class_name, 'install')) {
                        if (!call_user_func(array($class_name, 'install'))) {
                            return false;
                        }
                    }
                }
            }
        }

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
              `date_add` DATETIME,
              `date_upd` DATETIME
        ) ENGINE = "._MYSQL_ENGINE_." ";

        $sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "paypal_capture` (
              `id_paypal_capture` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
              `id_capture` VARCHAR(55),
              `id_paypal_order` INT(11),
              `capture_amount` FLOAT(11)),
              `result` VARCHAR(255),
              `date_add` DATETIME,
              `date_upd` DATETIME
        ) ENGINE = " . _MYSQL_ENGINE_ . " ";

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
        // Example :
        if (!$this->registerHook('paymentOptions')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('displayOrderConfirmation')
            || !$this->registerHook('displayAdminOrder')
        ) {
            return false;
        }


        return true;
    }

    public function uninstall()
    {
        foreach (array(
                     'PAYPAL_MERCHANT_ID',
                     'PAYPAL_API_CREDENTIAL',
                     'PAYPAL_API_PSWD',
                     'PAYPAL_API_SIGNATURE',
                     'PAYPAL_API_USERNAME') as $var) {
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

    }

    /**
     * Uninstall DataBase table
     * @return boolean if install was successfull
     */
    private function uninstallSQL()
    {
        # Uninstall All Object Model SQL via install function
        $path = $this->getLocalPath().'classes'.DIRECTORY_SEPARATOR;
        $classes = scandir($path);
        foreach ($classes as $class) {
            if ($class != 'index.php' && !preg_match('#\.old#isD', $class) && is_file($path.$class)) {
                $class_name = Tools::substr($class, 0, -4);
                // Check if class_name is an existing Class or not
                if (class_exists($class_name)) {
                    if (method_exists($class_name, 'uninstall')) {
                        if (!call_user_func(array($class_name, 'uninstall'))) {
                            return false;
                        }
                    }
                }
            }
        }

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

    public function getURL()
    {
        if (Configuration::get('PAYPAL_SANDBOX')) {
            return 'https://www.sandbox.paypal.com/';
        } else {
            return 'https://www.paypal.com/';
        }
    }

    public function getContent()
    {
        $this->_postProcess();
        $return_url = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $PartnerboardingURL = "";
        if ((Configuration::get('PAYPAL_SANDBOX') && !Configuration::get('PAYPAL_LIVE_ACCESS'))
        || (!Configuration::get('PAYPAL_SANDBOX') && !Configuration::get('PAYPAL_SANDBOX_ACCESS'))) {
            $partner_info = $this->getPartnerInfo(Configuration::get('PAYPAL_METHOD'));
            if (!$partner_info->error) {
                $PartnerboardingURL = $partner_info->data->link;
            }
        }

         $this->context->smarty->assign(array(
             'path' => $this->_path,
             'PartnerboardingURL' => $PartnerboardingURL,
             'country' => Country::getNameById($this->context->language->id, $this->context->country->id),
             'localization' => $this->context->link->getAdminLink('AdminLocalization', true),
             'active_products' => $this->express_checkout,
             'return_url' => $return_url,
             'access_token_sandbox' => Configuration::get('PAYPAL_SANDBOX_ACCESS'),
             'access_token_live' => Configuration::get('PAYPAL_LIVE_ACCESS'),
         ));
         $this->context->controller->addCSS($this->_path.'views/css/paypal-bo.css', 'all');
         $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('PARAMETERS of the module'),
                'image' => $this->_path.'/views/img/paypal_icon.png',
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
            ),
        );
        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('PARAMETRES PAYPAL EXPRESS CHECKOUT'),
                'image' => $this->_path.'/views/img/paypal_icon.png',
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Mode intent'),
                    'name' => 'paypal_intent',
                    'desc' => $this->l(''),
                    'hint' => $this->l('Sale: Performs the immediate debit of the customer during an order. Authorization / Capture: Authorization mode is a deferred payment method that requires manually capturing funds when you want to pass money. This mode is used if you want to make sure you have the merchandise before you cash the money for example. Attention, you have 29 days to capture the funds'),
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 'sale',
                                'name' => $this->l('Sale')
                            ),
                            array(
                                'id' => 'authorize',
                                'name' => $this->l('Authorize')
                            )
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Activate payment by cart'),
                    'name' => 'paypal_card',
                    'is_bool' => true,
                    'hint' => $this->l('Your customers can pay for their purchases with their national or international bank cards, whether they already have a PayPal account or not'),
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
                    'label' => $this->l('Show Advantages Paypal for clients during payment'),
                    'name' => 'paypal_show_advantage',
                    'desc' => $this->l(''),
                    'is_bool' => true,
                    'hint' => $this->l('Increase your conversion rate by presenting the benefits of PayPal to your customers when selecting the payment method'),
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

        if (Configuration::get('PAYPAL_SANDBOX') == 1) {
            $this->message .= $this->displayWarning($this->l('Your PayPal account is currently configured to accept payments on the Sandbox (test environment). Any transaction will be fictitious. Disable the option, to accept actual payments (production environment) and log in with your PayPal credentials'));
        } elseif (Configuration::get('PAYPAL_SANDBOX') == 0) {
            $this->message .= $this->displayConfirmation($this->l('Your PayPal account is properly connected, you can now receive payments'));
        }

        return $this->message.$this->display(__FILE__, 'views/templates/admin/configuration.tpl').$form;

    }

    public function getPartnerInfo($method)
    {

        $return_url = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $partner_info = array(
            'customer_data' => array(
                'customer_type' => 'MERCHANT',
                'person_details' => array(
                    'email_address' => Configuration::get('PS_SHOP_EMAIL'),
                ),
                'business_details' => array(
                    'business_address' => array(
                        'line1' => Configuration::get('PS_SHOP_ADDR1'),
                        'city' => Configuration::get('PS_SHOP_CITY'),
                        'country_code' => Tools::strtoupper($this->context->country->iso_code),
                        'postal_code' => Configuration::get('PS_SHOP_CODE'),
                    ),
                    'website_urls' => array(Tools::getShopDomain(true)),
                ),
                'preferred_language_code' => 'en_US',
                'primary_currency_code' => $this->context->currency->iso_code,
            ),
            'products' => array('EXPRESS_CHECKOUT'),
        );
        $web_experience_preference = new stdClass();
        $web_experience_preference->return_url = ($return_url.'&activate_method='.$method);
        $web_experience_preference->partner_logo_url = Tools::getShopDomain(true).'/img/logo.png';

        $partner_info['web_experience_preference'] = $web_experience_preference;
        /* $partner_info['requested_capabilities'][] = array(
            'capability' => 'API_INTEGRATION',
            'api_integration_preference' => array(
                'rest_api_integration' => array(
                    'integration_method' => 'PAYPAL',
                    'integration_type' => 'THIRD_PARTY',
                ),
                'rest_third_party_details' => array(
                    'partner_client_id' => 'AReLzfjunEgE3vvOxUgjPQZZXe2L9tcxI0NVIUzOF8BAmB8G4I0qsEUwptPtVF1Ioyu1TpAMQtG_nAeG',
                    'feature_list' => array('PAYMENT', 'REFUND'),
                ),
            ),
        );*/
        $sdk = new PaypalSDK(Configuration::get('PAYPAL_SANDBOX'));
        $response = json_decode($sdk->getUrlOnboarding($partner_info));
        return $response;

    }

    private function _postProcess()
    {
        /*if (Tools::getValue('merchantId')) {
            Configuration::updateValue('PAYPAL_MERCHANT_ID', Tools::getValue('merchantId'));
            $sdk = new PaypalSDK(Configuration::get('PAYPAL_SANDBOX'));
            $access_token = $sdk->createAccessToken();
            $credentials = $sdk->getCredentials($access_token);
            Configuration::updateValue('PAYPAL_API_CREDENTIAL', $credentials->api_credential);
            Configuration::updateValue('PAYPAL_API_USERNAME', $credentials->api_username);
            Configuration::updateValue('PAYPAL_API_PSWD', $credentials->api_password);
            Configuration::updateValue('PAYPAL_API_SIGNATURE', $credentials->api_signature);
            $permissions = $credentials->granted_permissions; //active products of Paypal for this user
        }*/

        if (Tools::isSubmit('paypal_config')) {
            Configuration::updateValue('PAYPAL_SANDBOX', Tools::getValue('paypal_sandbox'));
            Configuration::updateValue('PAYPAL_API_INTENT', Tools::getValue('paypal_intent'));
            Configuration::updateValue('PAYPAL_API_CARD', Tools::getValue('paypal_card'));
            Configuration::updateValue('PAYPAL_API_ADVANTAGES', Tools::getValue('paypal_show_advantage'));

            if (Configuration::get('PAYPAL_API_CARD')) {
                $landing_page_type = "billing";
            } else {
                $landing_page_type = "login";
            }
            $profile = array(
                'name' => Configuration::get('PS_SHOP_NAME').microtime(true),
                'flow_config' => array(
                    'landing_page_type' => $landing_page_type,
                    'bank_txn_pending_url' => Context::getContext()->link->getModuleLink($this->name, 'ec_validation', array(), true),
                ),
            );
            $sdk = new PaypalSDK(Configuration::get('PAYPAL_SANDBOX'));
            $web_experience = $sdk->createWebExperience($profile);

            if (isset($web_experience->id)) {
                Configuration::updateValue('PAYPAL_EXPERIENCE_PROFILE', $web_experience->id);
            }
        }

        if (Tools::getValue('method')) {
            $partner_info = $this->getPartnerInfo($_GET['method']);
            if ($partner_info->error) {
                $this->message .= $this->displayWarning($partner_info->error);
            } else {
                Tools::redirect($partner_info->data->link);
            }
        }
        if (Tools::getValue('activate_method')) {
            Configuration::updateValue('PAYPAL_EXPRESS_CHECKOUT', 1);
            Configuration::updateValue('PAYPAL_METHOD', Tools::getValue('activate_method'));

            Configuration::updateValue('PAYPAL_SANDBOX_ACCESS', 1);
            Configuration::updateValue('PAYPAL_LIVE_ACCESS', 1);
        }

    }

    public function hookPaymentOptions($params)
    {
        $payment_options = new PaymentOption();
        $action_text = $this->l('Pay by Paypal');
        $payment_options->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/paypal_sm.png'));
        if (Configuration::get('PAYPAL_API_ADVANTAGES')) {
            $action_text .= ' | '.$this->l('It\'s simple, easy, secure');
        }
        $this->context->smarty->assign(array(
            'path' => $this->_path,
        ));
        $payment_options->setCallToActionText($action_text);
        $payment_options->setAction($this->context->link->getModuleLink($this->name, 'ec_init', array(), true));
        if (Configuration::get('PAYPAL_API_ADVANTAGES')) {
            $payment_options->setAdditionalInformation($this->context->smarty->fetch('module:paypal/views/templates/front/payment_infos.tpl'));
        }

        $payment_options = [
            $payment_options,
        ];

        return $payment_options;

    }

    public function hookPaymentReturn($params)
    {

    }

    public function hookDisplayOrderConfirmation($params)
    {

    }

    public function validateOrder($id_cart, $id_order_state, $amount_paid, $payment_method = 'Unknown', $message = null, $transaction = array(), $currency_special = null, $dont_touch_amount = false, $secure_key = false, Shop $shop = null)
    {
        $intent = $transaction->intent;
        if ($intent == "authorize") {
            $intent = "authorization";
        }
        parent::validateOrder(
            (int) $id_cart,
            (int) $id_order_state,
            (float) $amount_paid,
            $payment_method,
            $message,
            array('transaction_id' => $transaction->transactions[0]->related_resources[0]->$intent->id),
            $currency_special,
            $dont_touch_amount,
            $secure_key,
            $shop
        );

        $paypal_order = new PaypalOrder();

        $paypal_order->id_order = $this->currentOrder;
        $paypal_order->id_cart = Context::getContext()->cart->id;
        $paypal_order->id_transaction = $transaction->transactions[0]->related_resources[0]->$intent->id;
        $paypal_order->id_payment = $transaction->id;
        $paypal_order->client_token = "";
        $paypal_order->payment_method = $transaction->payer->payment_method;
        $paypal_order->currency = $transaction->transactions[0]->amount->currency;
        $paypal_order->total_paid = (float) $transaction->transactions[0]->amount->total;
        $paypal_order->payment_status = $transaction->state;
        $paypal_order->save();

        if ($intent == "authorization") {
            $paypal_capture = new PaypalCapture();
            $paypal_capture->id_paypal_order = $paypal_order->id;
            $paypal_capture->save();
        }

    }

    public function hookdisplayAdminOrder($params)
    {
        if (Tools::getValue('capturePaypal')) {
            $method_ec = AbstractMethodPaypal::load('EC');
            $method_ec->confirmCapture();
        }
        if (Tools::getValue('refundPaypal')) {
            $method_ec = AbstractMethodPaypal::load('EC');
            $method_ec->refund();
        }
        $id_order = $params['id_order'];
        $order_link = Context::getContext()->link->getAdminLink('AdminOrders')."&id_order=".$id_order."&vieworder";
        $this->context->smarty->assign(array(
            'link_suivi' => $order_link,
        ));

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('paypal_order', 'po');
        $sql->where('po.id_order = '.(int)$id_order);
        $result = Db::getInstance()->getRow($sql);

        $refund = array('refundPaypal' => $result['id_paypal_order']);


        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('paypal_order', 'po');
        $sql->innerJoin('paypal_capture', 'pc', 'po.`id_paypal_order` = pc.`id_paypal_order`');
        $sql->where('po.id_order = '.(int)$id_order);
        $result = Db::getInstance()->getRow($sql);
        if ($result) {
            $refund['capture_id'] = $result['id_capture'];
            if ($result['result'] == "completed") {
                $this->context->smarty->assign(array(
                    'refund_link' => http_build_query($refund),
                ));
            } else {
                $this->context->smarty->assign(array(
                    'capture_link' => "&capturePaypal=".$result['id_paypal_order'],
                ));
            }
        } else {
            $this->context->smarty->assign(array(
                'refund_link' => http_build_query($refund),
            ));
        }
        return $this->display(__FILE__, 'views/templates/hook/paypal_commande.tpl');
    }

}
