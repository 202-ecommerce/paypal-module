<?php
/**
 * 2007-2016 PrestaShop
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

class PaypalSDK
{

    protected $api_key;

    public function getURL()
    {
        if (Configuration::get('PAYPAL_SANDBOX')) {
            return 'https://api.sandbox.paypal.com/';
        } else {
            return 'https://api.paypal.com/';
        }
    }

    public function createAccessToken()
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/oauth2/token';
        $body = array("grant_type" => "client_credentials");
        $this->makeCall($this->getBody($body), null, "EOJ2S-Z6OoN_le_KS1d75wsZ6y0SFdVsY9183IvxFyZp:EClusMEUk8e9ihI7ZdVLF5cZ6y0SFdVsY9183IvxFyZp");

        return $this->response;
    }

    public function getCredentials($params)
    {
        $this->action = 'GET';
        $this->endpoint = '/v1/identity/applications/@classic/owner/LQWH2R7C7XS7C/credentials';
        $this->makeCall();

        return $this->response;
    }

    public function createPayment($body)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/payment';
        $response = $this->makeCall($this->getBody($body), $this->endpoint, $this->action);
        return Tools::jsonDecode($response);
    }

    public function executePayment($payment_id, $payer_id)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/payment/'.$payment_id.'/execute';
        $body = array('payer_id' => $payer_id);
        $response = $this->makeCall($this->getBody($body), $this->endpoint, $this->action);

        return Tools::jsonDecode($response);
    }

    public function updatePayment($payment_id, $body)
    {
        $this->action = 'PATCH';
        $this->endpoint = 'v1/payments/payment/'.$payment_id;
        $response = $this->makeCall($this->getBody($body), $this->endpoint, $this->action);

        return Tools::jsonDecode($response);
    }

    public function refundSale($body, $sale_id)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/sale/'.$sale_id.'/refund';
        $response = $this->makeCall($this->getBody($body), $this->endpoint, $this->action);
        return Tools::jsonDecode($response);
    }

    public function showRefund($sale_id)
    {
        $this->action = 'GET';
        $sale_id = "2MU78835H4515710F";
        $this->endpoint = 'v1/payments/refund/'.$sale_id;
        $response = $this->makeCall(null, $this->endpoint, $this->action);
        return Tools::jsonDecode($response);
    }

    public function showAuthorization($authorization_id)
    {
        $this->action = 'GET';
        $authorization_id = "2DC87612EK520411B";
        $this->endpoint = 'v1/payments/authorization/'.$authorization_id;
        $response = $this->makeCall(null, $this->endpoint, $this->action);
        return Tools::jsonDecode($response);
    }

    public function captureAuthorization($body, $authorization_id)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/authorization/'.$authorization_id.'/capture';
        $response = $this->makeCall($this->getBody($body), $this->endpoint, $this->action);
        return Tools::jsonDecode($response);
    }

    public function voidAuthorization($body)
    {
        $this->action = 'POST';
        $authorization_id = "2DC87612EK520411B";
        $this->endpoint = 'v1/payments/authorization/'.$authorization_id.'/void';
        $response = $this->makeCall(null, $this->endpoint, $this->action);
        return Tools::jsonDecode($response);
    }

    public function showCapture($capture_id)
    {
        $this->action = 'GET';
        $capture_id = "8F148933LY9388354";
        $this->endpoint = 'v1/payments/capture/'.$capture_id;
        $response = $this->makeCall(null, $this->endpoint, $this->action);
        return Tools::jsonDecode($response);
    }

    public function refundCapture($body, $capture_id)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/capture/'.$capture_id.'/refund';
        $response = $this->makeCall($this->getBody($body), $this->endpoint, $this->action);
        return Tools::jsonDecode($response);
    }

    public function grantToken($body, $api_key)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/identity/openidconnect/tokenservice';
        $this->makeCall($this->getBody($body), $api_key);

        return $this->response;
    }


    protected function getBody(array $fields)
    {
        $return = true;

        // if fields not empty
        if (empty($fields)) {
            $return = false;
        }

        // if not empty
        if ($return) {
            return json_encode($fields);
        }

        return $return;
    }

    protected function makeCall($body = null, $url = null, $action = "POST", $cnt_type = "application/json", $user = null)
    {

        $curl = curl_init();
        if ($action == "GET") {
            $body = (is_array($body)) ? http_build_query($body) : $body;
            $url = $url.$body;
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $this->getURL().$url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        if ($user) {
            curl_setopt($curl, CURLOPT_USERPWD, "username:password");
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        if ($action == "PUT" || $action == "DELETE" || $action == "PATCH") {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $action);
        }
        if ($action == "POST") {
            curl_setopt($curl, CURLOPT_POST, true);
        }
        if ($action == "PUT") {
            curl_setopt($curl, CURLOPT_PUT, true);
        }
        if ($action == "POST" || $action == "PUT" || $action == "DELETE") {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-type: ".$cnt_type,
            'Content-Length: ' . strlen($body),
            "Authorization: Bearer A101.J7r7-Vy8A9wPTMrWhorTPsp87CSQJEgpmsyESd0Rh6UYroz2Q6lTmGH6DdgnQ_uX.u3cZapHkbmvAVMhs4168L7e1ZTC"
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            die('error occured during curl exec. Additioanl info: ' . curl_errno($curl).':'. curl_error($curl));
        }
        return ($response);

    }
}
