<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once (_PS_MODULE_DIR_.'paypal/sdk/PaypalSDK.php');

class MethodEC extends AbstractMethodPaypal
{
    public $name = 'paypal';

    public function setConfig($params)
    {
        $sdk = new PaypalSDK(
            Configuration::get('PAYPAL_SANDBOX')?Configuration::get('PAYPAL_SANDBOX_CLIENTID'):Configuration::get('PAYPAL_LIVE_CLIENTID'),
            Configuration::get('PAYPAL_SANDBOX')?Configuration::get('PAYPAL_SANDBOX_SECRET'):Configuration::get('PAYPAL_LIVE_SECRET'),
            Configuration::get('PAYPAL_SANDBOX'));
        $web_experience = $sdk->createWebExperience($params);
        if(isset($web_experience->id))
        {
            return $web_experience->id;
        }
        else
        {
            return false;
        }
    }

    public function init($data)
    {
        $sdk = new PaypalSDK(
            Configuration::get('PAYPAL_SANDBOX')?Configuration::get('PAYPAL_SANDBOX_CLIENTID'):Configuration::get('PAYPAL_LIVE_CLIENTID'),
            Configuration::get('PAYPAL_SANDBOX')?Configuration::get('PAYPAL_SANDBOX_SECRET'):Configuration::get('PAYPAL_LIVE_SECRET'),
            Configuration::get('PAYPAL_SANDBOX'));
        $context = Context::getContext();
        $cart = $context->cart;
        $currency = $context->currency;
        $customer = $context->customer;


        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $shipping_cost = $cart->getTotalShippingCost();
        $summary = $cart->getSummaryDetails();

        $products = $cart->getProducts();
        $address = new Address($cart->id_address_delivery);
        $country = new Country($address->id_country);
        $state = new State($address->id_state);

        $params = array(
            'intent' => Configuration::get('PAYPAL_API_INTENT'), //sale
            'payer' => array(
                'payment_method' => 'paypal', //credit_card
                'payer_info' => array(
                    'email' => $customer->email,
                )
            ),
            'redirect_urls' => array(
                'return_url' => Context::getContext()->link->getModuleLink($this->name, 'ec_validation', array(), true),
                'cancel_url' => Tools::getShopDomain(true, true).'/index.php?controller=order&step=1',
            ),
        );

        if($data['use_card'])
        {
            $params['experience_profile_id'] = Configuration::get('PAYPAL_EXPERIENCE_PROFILE_CARD');
        }
        else
        {
            $params['experience_profile_id'] = Configuration::get('PAYPAL_EXPERIENCE_PROFILE');
        }
        $items = array();
        foreach ($products as $product) {
            $items[] = array(
                'quantity' => $product['cart_quantity'],
                'name' => $product['name'],
                'price' =>  str_replace(',','.' , round($product['price'], 2)),
                'currency' => $currency->iso_code,
                'description' => strip_tags($product['description_short']),
                'tax' => str_replace(',','.' , round($product['total_wt'] - $product['total'],2)),
            );
        }



        $params['transactions'][] = array(
            'amount' => array(
                'total' => $total,
                'currency' => $currency->iso_code,
                'details' => array(
                    'subtotal' => str_replace(',','.' , round($summary['total_products'],2)),
                    'tax' => str_replace(',','.' , round($summary['total_tax'],2)),
                    'shipping' => str_replace(',','.' , round($shipping_cost,2)),
                ),
            ),
            'item_list' => array(
                'items' => $items,
                "shipping_address" => array(
                    "recipient_name" => $address->firstname.' '.$address->lastname,
                    "line1" => $address->address1,
                    "line2" => $address->address2,
                    "city" => $address->city,
                    "country_code" => $country->iso_code,
                    "postal_code" => $address->postcode,
                    "phone" => $address->phone,
                    "state" => $state->iso_code
                ),
            ),
        );

        $return = false;
        $payment = $sdk->createPayment($params);

        if (isset($payment->links)) {
            foreach ($payment->links as $redirect_urls) {
                if ($redirect_urls->method == "REDIRECT") {
                    $return = $redirect_urls->href;
                }
            }
        }
        return $return;


    }

    public function validation()
    {
        $sdk = new PaypalSDK(
        Configuration::get('PAYPAL_SANDBOX')?Configuration::get('PAYPAL_SANDBOX_CLIENTID'):Configuration::get('PAYPAL_LIVE_CLIENTID'),
        Configuration::get('PAYPAL_SANDBOX')?Configuration::get('PAYPAL_SANDBOX_SECRET'):Configuration::get('PAYPAL_LIVE_SECRET'),
        Configuration::get('PAYPAL_SANDBOX'));
        $exec_payment = $sdk->executePayment(Tools::getValue('paymentId'), Tools::getValue('PayerID'));

        $cart = Context::getContext()->cart;
        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer))
             Tools::redirect('index.php?controller=order&step=1');
        $currency = Context::getContext()->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $paypal = Module::getInstanceByName('paypal');
        if (Configuration::get('PAYPAL_API_INTENT') == "sale") {
            $order_state = Configuration::get('PS_OS_PAYMENT');
        } else {
            $order_state = Configuration::get('PS_OS_PAYPAL');
        }

        $paypal->validateOrder($cart->id, $order_state, $total, 'paypal', NULL, $exec_payment, (int)$currency->id, false, $customer->secure_key);

    }

    public function confirmCapture()
    {
        $sdk = new PaypalSDK(
            Configuration::get('PAYPAL_SANDBOX')?Configuration::get('PAYPAL_SANDBOX_CLIENTID'):Configuration::get('PAYPAL_LIVE_CLIENTID'),
            Configuration::get('PAYPAL_SANDBOX')?Configuration::get('PAYPAL_SANDBOX_SECRET'):Configuration::get('PAYPAL_LIVE_SECRET'),
            Configuration::get('PAYPAL_SANDBOX'));
        $paypal_order = new PaypalOrder(Tools::getValue('capturePaypal'));
        $body = array(
            'amount' => array(
                'total' => number_format($paypal_order->total_paid, 2, ".", ","),
                'currency' => $paypal_order->currency
            ),
            'is_final_capture' => true,

        );

        $response = $sdk->captureAuthorization($body, $paypal_order->id_transaction);
        if (isset($response->state) && $response->state == 'completed' || isset($response->name) && $response->name == 'AUTHORIZATION_ALREADY_COMPLETED') {
            Db::getInstance()->update(
                'paypal_capture',
                array(
                    'id_capture' => $response->id,
                    'capture_amount' => $response->amount->total,
                    'result' => 'completed',
                ),
                'id_paypal_order = ' . (int)Tools::getValue('capturePaypal')
            );
        }
        return $response;
        

    }

    public function check()
    {

    }

    public function refund()
    {
        $sdk = new PaypalSDK(
            Configuration::get('PAYPAL_SANDBOX')?Configuration::get('PAYPAL_SANDBOX_CLIENTID'):Configuration::get('PAYPAL_LIVE_CLIENTID'),
            Configuration::get('PAYPAL_SANDBOX')?Configuration::get('PAYPAL_SANDBOX_SECRET'):Configuration::get('PAYPAL_LIVE_SECRET'),
            Configuration::get('PAYPAL_SANDBOX'));
        $id_paypal_order = Tools::getValue('refundPaypal');
        $paypal_order = new PaypalOrder($id_paypal_order);
        $body = array(
            'amount' => array(
                'total' => number_format($paypal_order->total_paid, 2, ".", ","),
                'currency' => $paypal_order->currency
            )
        );

        if (Tools::getValue('capture_id')) {
            $response = $sdk->refundCapture($body, Tools::getValue('capture_id'));
            if (isset($response->id)) {
                Db::getInstance()->update(
                    'paypal_capture',
                    array(
                        'result' => $response->state,
                    ),
                    'id_paypal_order = '.(int)$id_paypal_order
                );
            }
        } else {
            $response = $sdk->refundSale($body, $paypal_order->id_transaction);
            if (isset($response->id)) {
                $paypal_order->result = $response->state;
                $paypal_order->update();
            }
        }

        return $response;
    }

    public function void($params)
    {
        $sdk = new PaypalSDK(
            Configuration::get('PAYPAL_SANDBOX')?Configuration::get('PAYPAL_SANDBOX_CLIENTID'):Configuration::get('PAYPAL_LIVE_CLIENTID'),
            Configuration::get('PAYPAL_SANDBOX')?Configuration::get('PAYPAL_SANDBOX_SECRET'):Configuration::get('PAYPAL_LIVE_SECRET'),
            Configuration::get('PAYPAL_SANDBOX'));
        return $sdk->voidAuthorization($params['authorization_id']);
    }
}
