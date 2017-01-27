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

    private $action;
    private $endpoint;
    private $token;
    private $urlAPI;
    private $urlIntermediateServer;

    public function __construct($token,$sandbox=0)
    {
        $this->token = $token;
        $this->action = 'POST';
        if ($sandbox) {
            $this->urlAPI = 'https://api.sandbox.paypal.com/';
            $this->urlIntermediateServer = 'http://SI.com/';
        } else {
            $this->urlAPI = 'https://api.paypal.com/';
            $this->urlIntermediateServer = 'http://SI.com/';
        }

    }

    public function getUrlOnboarding($partner_info)
    {
        return $this->makeCallIntermediateServer('getUrl',$partner_info);

    }

    private function makeCallIntermediateServer($method,$data)
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $this->urlIntermediateServer.$method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json"));
        $response = curl_exec($curl);
        return $response;

    }

    public function createPayment($body)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/payment';
        $response = $this->makeCall($this->getBody($body));
        return json_decode($response);
    }

    public function createWebExperience($body)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payment-experience/web-profiles';
        $response = $this->makeCall($this->getBody($body));
        return json_decode($response);
    }

    public function executePayment($payment_id, $payer_id)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/payment/'.$payment_id.'/execute';
        $body = array('payer_id' => $payer_id);
        $response = $this->makeCall($this->getBody($body));

        return json_decode($response);
    }

    public function updatePayment($payment_id, $body)
    {
        $this->action = 'PATCH';
        $this->endpoint = 'v1/payments/payment/'.$payment_id;
        $response = $this->makeCall($this->getBody($body));

        return json_decode($response);
    }

    public function refundSale($body, $sale_id)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/sale/'.$sale_id.'/refund';
        $response = $this->makeCall($this->getBody($body));
        return json_decode($response);
    }

    public function showRefund($sale_id)
    {
        $this->action = 'GET';
        $this->endpoint = 'v1/payments/refund/'.$sale_id;
        $response = $this->makeCall(null);
        return json_decode($response);
    }

    public function showAuthorization($authorization_id)
    {
        $this->action = 'GET';
        $this->endpoint = 'v1/payments/authorization/'.$authorization_id;
        $response = $this->makeCall(null);
        return json_decode($response);
    }

    public function captureAuthorization($body, $authorization_id)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/authorization/'.$authorization_id.'/capture';
        $response = $this->makeCall($this->getBody($body));
        return json_decode($response);
    }

    public function voidAuthorization($authorization_id)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/authorization/'.$authorization_id.'/void';
        $response = $this->makeCall(null);
        return json_decode($response);
    }

    public function showCapture($capture_id)
    {
        $this->action = 'GET';
        $this->endpoint = 'v1/payments/capture/'.$capture_id;
        $response = $this->makeCall(null);
        return json_decode($response);
    }

    public function refundCapture($body, $capture_id)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/capture/'.$capture_id.'/refund';
        $response = $this->makeCall($this->getBody($body));
        return json_decode($response);
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

    protected function makeCall($body = null, $cnt_type = "application/json", $user = null)
    {
        $curl = curl_init();
        if ($this->action == "GET") {
            $body = (is_array($body)) ? http_build_query($body) : $body;
            $this->endpoint = $this->endpoint.$body;
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $this->urlAPI.$this->endpoint);
        if ($this->action == "PUT" || $this->action == "DELETE" || $this->action == "PATCH") {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->action);
        }
        if ($this->action == "POST") {
            curl_setopt($curl, CURLOPT_POST, true);
        }
        if ($this->action == "PUT") {
            curl_setopt($curl, CURLOPT_PUT, true);
        }
        if ($this->action == "POST" || $this->action == "PUT" || $this->action == "DELETE") {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }
        if ($user) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Accept: application/json",
                "Accept-Language: en_US",
                "Authorization: Basic ".base64_encode($user)
            ));
        } else {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-type: ".$cnt_type,
                'Content-Length: ' . strlen($body),
                "Authorization: Bearer A101.8v3dlDK4N9OYtnSpQgGRRZyx0CI2JEV5eT-KCCljD80ydZ0J2BhO92X2NlCvPAkA.LCRr9AtKidubsi8V2bVDuSRTc0q",
            ));
        }

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            die('error occured during curl exec. Additioanl info: ' . curl_errno($curl).':'. curl_error($curl));
        }
        return ($response);

    }
}
