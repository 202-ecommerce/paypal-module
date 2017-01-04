<?php
/*
* 2007-2015 PrestaShop
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
/**
 * @since 1.5.0
 */

class MethodEC extends AbstractMethodPaypal
{
     public $name = 'paypal';

     public function init()
     {
         $sdk = new PaypalSDK(Configuration::get('PAYPAL_SANDBOX'));
         $cart = Context::getContext()->cart;
         $currency = Context::getContext()->currency;
         $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        // $cart_rules = $cart->getOrderedCartRulesIds();
         $shipping_cost = $cart->getTotalShippingCost();
        // $shipping_addr = new Address($cart->id_address_delivery);
         $summary = $cart->getSummaryDetails();

         $products = $cart->getProducts();

         $params = array(
             'intent' => Configuration::get('PAYPAL_API_INTENT'), //sale
             'payer' => array(
                 'payment_method' => 'paypal', //credit_card
                // 'funding_instruments' => array(array('credit_card' => array())),
             ),
             'note_to_payer' => 'Contact us for any questions on your order.',
             'redirect_urls' => array(
                 'return_url' => Context::getContext()->link->getModuleLink($this->name, 'ec_validation', array(), true),
                 'cancel_url' => Tools::getShopDomain(true, true).'/index.php?controller=order&step=1',
             ),
         );

         $params['experience_profile_id'] = Configuration::get('PAYPAL_EXPERIENCE_PROFILE');
         foreach ($products as $product) {
             $items = array(
                 'quantity' => $product['cart_quantity'],
                 'name' => $product['name'],
                 'price' =>  $product['price'],
                 'currency' => $currency->iso_code,
                 'description' => $product['description_short'],
                 'tax' => $product['total_wt'] - $product['total'],
             );
         }
         $params['transactions'][] = array(
             'amount' => array(
                 'total' => $total,
                 'currency' => $currency->iso_code,
                 'details' => array(
                     'subtotal' => $summary['total_products'],
                     'tax' => $summary['total_tax'],
                     'shipping' => $shipping_cost,
                    /* 'handling_fee' => "0",
                     'shipping_discount' => "0",
                     'insurance' => "0",*/
                 ),
             ),
             'item_list' => array(
                 'items' => array(
                     $items
                 ),
             ),
            /* 'shipping_address' => array(
                 'recipient_name' => $shipping_addr->firstname.' '.$shipping_addr->lastname,
                 'line1' => $shipping_addr->address1,
                 'line2' => $shipping_addr->address2,
                 'city' => $shipping_addr->city,
                 'country_code' => Context::getContext()->country->iso_code,
                 'postal_code' => $shipping_addr->postcode,
                 'phone' => $shipping_addr->phone,
             )*/
         );



         $payment = $sdk->createPayment($params);

         return $payment;


     }

     public function validation()
     {

         $sdk = new PaypalSDK(Configuration::get('PAYPAL_SANDBOX'));
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
         $sdk = new PaypalSDK(Configuration::get('PAYPAL_SANDBOX'));
         $paypal_order = new PaypalOrder(Tools::getValue('id_paypal_order'));
         $body = array(
             'amount' => array(
                 'total' => $paypal_order->total_paid,
                 'currency' => $paypal_order->currency
             ),
             'is_final_capture' => true,

         );

         $response = $sdk->captureAuthorization($body, $paypal_order->id_transaction);
         if (isset($response->id)) {
             Db::getInstance()->update(
                 'paypal_capture',
                 array(
                     'capture_amount' => $response->amount->total,
                     'result' => $response->sate,
                 ),
                 'id_paypal_order = '.(int)Tools::getValue('id_paypal_order')
             );
             $order = new Order(Tools::getValue('id_order'));
             $order->setCurrentState(_PS_OS_PAYMENT_);
         }

     }

     public function check()
     {

     }

     public function refund()
     {
         $sdk = new PaypalSDK(Configuration::get('PAYPAL_SANDBOX'));
         $id_paypal_order = Tools::getValue('id_paypal_order');
         $paypal_order = new PaypalOrder($id_paypal_order);
         $body = array(
             'amount' => array(
                 'total' => $paypal_order->total_paid,
                 'currency' => $paypal_order->currency
             )
         );

         if (Tools::getValue('capture_id')) {
             $response = $sdk->refundCapture($body, Tools::getValue('capture_id'));
             if (isset($response->id)) {
                 Db::getInstance()->update(
                     'paypal_capture',
                     array(
                         'result' => $response->sate,
                     ),
                     'id_paypal_order = '.(int)$id_paypal_order
                 );
             }
         } else {
             $response = $sdk->refundSale($body, $paypal_order->id_transaction);
             if (isset($response->id)) {
                 $paypal_order->result = $response->sate;
                 $paypal_order->update();
             }
         }
         if (isset($response->id)) {
             $order = new Order(Tools::getValue('id_order'));
             $order->setCurrentState(_PS_OS_REFUND_);
         }


     }
}