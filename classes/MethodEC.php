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

include_once(_PS_MODULE_DIR_.'paypal/sdk/PaypalSDK.php');

class MethodEC extends AbstractMethodPaypal
{
    public $name = 'paypal';

    public $token;
    
    public $version = '204';

    public function setConfig($params)
    {
    }

    public function init($data)
    {
        $sdk = new PaypalSDK(Configuration::get('PAYPAL_SANDBOX'));

        $params = $this->setExpressCheckout();

        $return = false;
        $payment = $sdk->makeCallPaypal($params);
       /* echo '<pre>';
        print_r($payment);
        echo '<pre>';
        die;*/
        if (isset($payment['TOKEN'])) {
            $this->token = $payment['TOKEN'];
            $return = $this->redirectToAPI($payment['TOKEN'], 'setExpressCheckout');
        }
        return $return;
    }

    public function setExpressCheckout()
    {
        $this->method = 'SetExpressCheckout';
        $fields = array();

        // Seller informations
        $this->_setUserCredentials($fields);

        $fields['METHOD'] = 'SetExpressCheckout';
        $fields['VERSION'] = $this->version;
        $fields['CANCELURL'] = Tools::getShopDomain(true, true).'/index.php?controller=order&step=1';
        $fields['SOLUTIONTYPE'] = 'Sole';
        $fields['LANDINGPAGE'] = Tools::getValue('credit_card') ? 'Billing' : 'Login';

        // Set payment detail (reference)
        $this->_setPaymentDetails($fields);

        return $fields;
    }

    public function getExpressCheckout()
    {
        $this->method = 'GetExpressCheckoutDetails';
        $fields = array();
        $this->_setUserCredentials($fields);
        $fields['METHOD'] = 'GetExpressCheckoutDetails';
        $fields['VERSION'] = $this->version;
        $fields['TOKEN'] = $this->token;

        return $fields;
    }

    public function doExpressCheckout()
    {
        $this->method = 'DoExpressCheckoutPayment';
        $fields = array();
        // Seller informations
        $this->_setUserCredentials($fields);

        $fields['METHOD'] = 'DoExpressCheckoutPayment';
        $fields['VERSION'] = $this->version;
        $fields['TOKEN'] = Tools::getValue('token');
        $fields['PAYERID'] = Tools::getValue('PayerID');

        // Set payment details
        $this->_setPaymentDetails($fields);

        return $fields;
    }

    private function _setPaymentDetails(&$fields)
    {
        // Required field
        $fields['RETURNURL'] = Context::getContext()->link->getModuleLink($this->name, 'ec_validation', array(), true);
        $fields['NOSHIPPING'] = '1';

        // Products
        $tax = $total_products = 0;
        $index = -1;

        // Set cart products list
        $this->setProductsList($fields, $index, $total_products, $tax);
        $this->setDiscountsList($fields, $index, $total_products, $tax);
        $this->setGiftWrapping($fields, $index, $total_products, $tax);

        // Payment values
        $this->setPaymentValues($fields, $index, $total_products, $tax);

        // Set address information
        $id_address = (int) Context::getContext()->cart->id_address_delivery;
        if (($id_address == 0) && (Context::getContext()->customer)) {
            $id_address = Address::getFirstCustomerAddressId(Context::getContext()->customer->id);
        }
        $this->setShippingAddress($fields, $id_address);

        foreach ($fields as &$field) {
            if (is_numeric($field)) {
                $field = str_replace(',', '.', $field);
            }
        }


    }


    private function setPaymentValues(&$fields, &$index, &$total_products, &$tax)
    {
        $shipping_cost_wt = Context::getContext()->cart->getTotalShippingCost();
        $fields['PAYMENTREQUEST_0_PAYMENTACTION'] = Configuration::get('PAYPAL_API_INTENT');
        $currency = Context::getContext()->currency->iso_code;
        $fields['PAYMENTREQUEST_0_CURRENCYCODE'] = $currency;

        $cart = Context::getContext()->cart;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $summary = $cart->getSummaryDetails();
        $subtotal = Tools::ps_round($summary['total_products'], 2);

        $total_tax = Tools::ps_round($tax, 2);

        if ($subtotal != $total_products) {
            $subtotal = $total_products;
        }
        $shipping = Tools::ps_round($shipping_cost_wt, 2);

        $total_cart = $total_products + $shipping + $tax;

        if ($total != $total_cart) {
            $total = $total_cart;
        }

        /**
         * If the total amount is lower than 1 we put the shipping cost as an item
         * so the payment could be valid.
         */
        if ($total <= 1) {
            $carrier = new Carrier(Context::getContext()->cart->id_carrier);
            $fields['L_PAYMENTREQUEST_0_NUMBER'.++$index] = $carrier->id_reference;
            $fields['L_PAYMENTREQUEST_0_NAME'.$index] = $carrier->name;
            $fields['L_PAYMENTREQUEST_0_AMT'.$index] = $shipping;
            $fields['L_PAYMENTREQUEST_0_QTY'.$index] = 1;
            $fields['PAYMENTREQUEST_0_ITEMAMT'] = $subtotal + $shipping;
            $fields['PAYMENTREQUEST_0_AMT'] = $total + $shipping;
        } else {
            $fields['PAYMENTREQUEST_0_SHIPPINGAMT'] = $shipping;
            $fields['PAYMENTREQUEST_0_ITEMAMT'] = $subtotal;
            $fields['PAYMENTREQUEST_0_TAXAMT'] = $total_tax;
            $fields['PAYMENTREQUEST_0_AMT'] = $total;
        }
    }

    private function setProductsList(&$fields, &$index, &$total_products, &$tax)
    {
        $cart = Context::getContext()->cart;
        $products = $cart->getProducts();

        foreach ($products as $product) {
            $fields['L_PAYMENTREQUEST_0_NUMBER'.++$index] = (int) $product['id_product'];

            $fields['L_PAYMENTREQUEST_0_NAME'.$index] = $product['name'];

            if (isset($product['attributes']) && (empty($product['attributes']) === false)) {
                $fields['L_PAYMENTREQUEST_0_NAME'.$index] .= ' - '.$product['attributes'];
            }

            $fields['L_PAYMENTREQUEST_0_DESC'.$index] = Tools::substr(strip_tags($product['description_short']), 0, 50).'...';

            $fields['L_PAYMENTREQUEST_0_AMT'.$index] = Tools::ps_round($product['price'], 2);
            $fields['L_PAYMENTREQUEST_0_TAXAMT'.$index] = Tools::ps_round($product['price_wt'] - $product['price'], 2);
            $fields['L_PAYMENTREQUEST_0_QTY'.$index] = $product['quantity'];

            $total_products = $total_products + ($fields['L_PAYMENTREQUEST_0_AMT'.$index] * $product['quantity']);
            $tax = $tax + ($fields['L_PAYMENTREQUEST_0_TAXAMT'.$index] * $product['quantity']);
        }
    }

    private function setDiscountsList(&$fields, &$index, &$total_products)
    {
        $discounts = Context::getContext()->cart->getCartRules();

        if (count($discounts) > 0) {
            foreach ($discounts as $discount) {
                $fields['L_PAYMENTREQUEST_0_NUMBER'.++$index] = $discount['id_discount'];

                $fields['L_PAYMENTREQUEST_0_NAME'.$index] = $discount['name'];
                if (isset($discount['description']) && !empty($discount['description'])) {
                    $fields['L_PAYMENTREQUEST_0_DESC'.$index] = Tools::substr(strip_tags($discount['description']), 0, 50).'...';
                }

                /* It is a discount so we store a negative value */
                $fields['L_PAYMENTREQUEST_0_AMT'.$index] = -1 * Tools::ps_round($discount['value_real'], 2);
                $fields['L_PAYMENTREQUEST_0_QTY'.$index] = 1;

                $total_products = Tools::ps_round($total_products + $fields['L_PAYMENTREQUEST_0_AMT'.$index], 2);
            }
        }
    }

    private function setGiftWrapping(&$fields, &$index, &$total_products)
    {
        if (Context::getContext()->cart->gift == 1) {
            $gift_wrapping_price = $this->getGiftWrappingPrice();

            $fields['L_PAYMENTREQUEST_0_NAME'.++$index] = $this->l('Gift wrapping');

            $fields['L_PAYMENTREQUEST_0_AMT'.$index] = Tools::ps_round($gift_wrapping_price, 2);
            $fields['L_PAYMENTREQUEST_0_QTY'.$index] = 1;

            $total_products = Tools::ps_round($total_products + $gift_wrapping_price, 2);
        }
    }

    private function setShippingAddress(&$fields, $id_address)
    {
        $address = new Address($id_address);

        $fields['ADDROVERRIDE'] = '0';
        $fields['NOSHIPPING'] = '0';

        $fields['EMAIL'] = Context::getContext()->customer->email;
        $fields['PAYMENTREQUEST_0_SHIPTONAME'] = $address->firstname.' '.$address->lastname;
        $fields['PAYMENTREQUEST_0_SHIPTOPHONENUM'] = (empty($address->phone)) ? $address->phone_mobile : $address->phone;
        $fields['PAYMENTREQUEST_0_SHIPTOSTREET'] = $address->address1;
        $fields['PAYMENTREQUEST_0_SHIPTOSTREET2'] = $address->address2;
        $fields['PAYMENTREQUEST_0_SHIPTOCITY'] = $address->city;
        if ($address->id_state) {
            $state = new State((int) $address->id_state);
            $fields['PAYMENTREQUEST_0_SHIPTOSTATE'] = $state->iso_code;
        }
        $country = new Country((int) $address->id_country);
        $fields['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = $country->iso_code;
        $fields['PAYMENTREQUEST_0_SHIPTOZIP'] = $address->postcode;
    }

    public function redirectToAPI($token, $method)
    {
        if ($this->useMobile()) {
            $url = '/cgi-bin/webscr?cmd=_express-checkout-mobile';
        } else {
            $url = '/websc&cmd=_express-checkout';
        }

        if (($method == 'SetExpressCheckout') && ($this->type == 'payment_cart')) {
            $url .= '&useraction=commit';
        }
        $paypal = Module::getInstanceByName('paypal');
        return $paypal->getUrl().$url.'&token='.urldecode($token);
    }

    public function useMobile()
    {
        if ((method_exists(Context::getContext(), 'getMobileDevice') && Context::getContext()->getMobileDevice())
            || Tools::getValue('ps_mobile_site')) {
            return true;
        }

        return false;
    }

    public function validation()
    {
        $sdk = new PaypalSDK(Configuration::get('PAYPAL_SANDBOX'));

        $payment_info = $this->doExpressCheckout();
        $exec_payment = $sdk->makeCallPaypal($payment_info);

        $cart = Context::getContext()->cart;
        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        $currency = Context::getContext()->currency;
        $total = (float)$exec_payment['PAYMENTINFO_0_AMT'];
        $paypal = Module::getInstanceByName('paypal');
        if (Configuration::get('PAYPAL_API_INTENT') == "sale") {
            $order_state = Configuration::get('PS_OS_PAYMENT');
        } else {
            $order_state = Configuration::get('PS_OS_PAYPAL');
        }

        $paypal->validateOrder($cart->id, $order_state, $total, 'paypal', null, $exec_payment, (int)$currency->id, false, $customer->secure_key);
    }

    private function _setUserCredentials(&$fields)
    {
        Configuration::updateValue('PAYPAL_API_USERNAME', 'claloum-facilitator_api1.202-ecommerce.com');
        Configuration::updateValue('PAYPAL_API_PSWD', '2NRPZ3FZQXN9LY2N');
        Configuration::updateValue('PAYPAL_API_SIGNATURE', 'AFcWxV21C7fd0v3bYYYRCpSSRl31Am6xsFqhy1VTTuSmPwEstqKmFDaX');
        $fields['USER'] = Configuration::get('PAYPAL_API_USERNAME');
        $fields['PWD'] = Configuration::get('PAYPAL_API_PSWD');
        $fields['SIGNATURE'] = Configuration::get('PAYPAL_API_SIGNATURE');
    }

    public function confirmCapture()
    {
        $sdk = new PaypalSDK(Configuration::get('PAYPAL_SANDBOX'));

        $paypal_order = new PaypalOrder(Tools::getValue('capturePaypal'));

        $fields = array();
        $this->_setUserCredentials($fields);
        $fields['METHOD'] = 'DoCapture';
        $fields['VERSION'] = $this->version;
        $fields['AMT'] = number_format($paypal_order->total_paid, 2, ".", ",");
        $fields['AUTHORIZATIONID'] = $paypal_order->id_transaction;
        $fields['CURRENCYCODE'] = $paypal_order->currency;
        $fields['COMPLETETYPE'] = 'complete';

        $response = $sdk->makeCallPaypal($fields);

        if ($response['ACK'] == "Success") {
            Db::getInstance()->update(
                'paypal_capture',
                array(
                    'id_capture' => $response['TRANSACTIONID'],
                    'capture_amount' => $response['AMT'],
                    'result' => $response['PAYMENTSTATUS'],
                ),
                'id_paypal_order = '.(int)Tools::getValue('capturePaypal')
            );
        }
        
        return $response;
    }

    public function check()
    {
    }

    public function refund()
    {
        $sdk = new PaypalSDK(Configuration::get('PAYPAL_SANDBOX'));
        $id_paypal_order = Tools::getValue('refundPaypal');

        $paypal_order = new PaypalOrder($id_paypal_order);
        $id_transaction = Tools::getValue('capture_id') ? Tools::getValue('capture_id') : $paypal_order->id_transaction;

        $fields = array();
        $this->_setUserCredentials($fields);
        $fields['METHOD'] = 'RefundTransaction';
        $fields['VERSION'] = $this->version;
        $fields['TRANSACTIONID'] = $id_transaction;
        $fields['REFUNDTYPE'] = 'Full';

        if (Tools::getValue('capture_id')) {
            $response = $sdk->makeCallPaypal($fields);
            if (isset($response['REFUNDTRANSACTIONID']) && $response['ACK'] == 'Success') {
                Db::getInstance()->update(
                    'paypal_capture',
                    array(
                        'result' => 'Refunded',
                    ),
                    'id_paypal_order = '.(int)$id_paypal_order
                );
            }
        } else {
            $response = $sdk->makeCallPaypal($fields);
            if (isset($response['REFUNDTRANSACTIONID']) && $response['ACK'] == 'Success') {
                $paypal_order->result = 'Refunded';
                $paypal_order->update();
            }
        }
 
        return $response;
    }
    
    public function void($params)
    {
        $fields = array();
        $this->_setUserCredentials($fields);
        $fields['METHOD'] = 'DoVoid';
        $fields['VERSION'] = $this->version;
        $fields['AUTHORIZATIONID'] = $params['authorization_id'];
        $sdk = new PaypalSDK(Configuration::get('PAYPAL_SANDBOX'));
        return $sdk->makeCallPaypal($fields);
    }
}
