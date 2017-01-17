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

    protected $action;
    protected $endpoint;
    protected $response;
    protected $urlAPI;
    protected $urlIntermediateServer;

    public function __construct($sandbox)
    {
        if ($sandbox) {
            $this->urlAPI = 'https://api.sandbox.paypal.com/';
            $this->urlIntermediateServer = 'http://iuliia-1704.work.202-ecommerce.com/modules/paypal/test_server.php';
        } else {
            $this->urlAPI = 'https://api.paypal.com/';
            $this->urlIntermediateServer = 'http://iuliia-1704.work.202-ecommerce.com/modules/paypal/test_server.php';
        }

    }


    public function getUrlOnboarding($partner_info)
    {
        return $this->makeCallIntermediateServer($partner_info);

    }

    private function makeCallIntermediateServer($partner_info)
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $this->urlIntermediateServer);
       // curl_setopt($curl, CURLOPT_URL, $this->urlIntermediateServer.'?method='.$method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($partner_info));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Accept: application/json",
            "Authorization: Basic ".base64_encode("202:mattdelg")
        ));
        $response = curl_exec($curl);

        return $response;

    }

    public function getPartnerReferrals($referral_id)
    {
        //TODO delete if not use !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $this->action = 'GET';
        $this->endpoint = 'v1/customer/partner-referrals/'.$referral_id;
        $response = $this->makeCall(null, $this->endpoint, $this->action);
 
        return json_decode($response);
    }

    public function grantToken($body)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/identity/openidconnect/tokenservice'; // also possible to refresh token with this endpoint to recieve new access token
        $body = array(
            "grant_type" => "authorization_code", //refresh_token
            "code" => "Authorization-Code", // refresh token code and no url required
            "redirect_uri" => "http://iuliia-17.work.202-ecommerce.com/admin524drqi1g/index.php?controller=AdminModules&configure=paypal&token=f39f05142ce7edf40e2b990f7aaad5a7"
            );
        $this->makeCall($this->getBody($body),  $this->endpoint, $this->action, "application/x-www-form-urlencoded", "EOJ2S-Z6OoN_le_KS1d75wsZ6y0SFdVsY9183IvxFyZp:EClusMEUk8e9ihI7ZdVLF5cZ6y0SFdVsY9183IvxFyZp");

        return $this->response;
    }

    public function getCredentials()
    {
        $this->action = 'GET';
        $this->endpoint = '/v1/identity/applications/@classic/owner/LQWH2R7C7XS7C/credentials';
        $this->makeCall(null, $this->endpoint, $this->action);

        return $this->response;
    }

    public function createPayment($body)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/payment';
        $response = $this->makeCall($this->getBody($body), $this->endpoint, $this->action);
        return json_decode($response);
    }

    public function createWebExperience($body)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payment-experience/web-profiles';
        $response = $this->makeCall($this->getBody($body), $this->endpoint, $this->action);
        return json_decode($response);
    }

    public function executePayment($payment_id, $payer_id)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/payment/'.$payment_id.'/execute';
        $body = array('payer_id' => $payer_id);
        $response = $this->makeCall($this->getBody($body), $this->endpoint, $this->action);

        return json_decode($response);
    }

    public function updatePayment($payment_id, $body)
    {
        $this->action = 'PATCH';
        $this->endpoint = 'v1/payments/payment/'.$payment_id;
        $response = $this->makeCall($this->getBody($body), $this->endpoint, $this->action);

        return json_decode($response);
    }

    public function refundSale($body, $sale_id)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/sale/'.$sale_id.'/refund';
        $response = $this->makeCall($this->getBody($body), $this->endpoint, $this->action);
        return json_decode($response);
    }

    public function showRefund($sale_id)
    {
        // TODO delete after test
        $sale_id = "2MU78835H4515710F";
        $this->action = 'GET';
        $this->endpoint = 'v1/payments/refund/'.$sale_id;
        $response = $this->makeCall(null, $this->endpoint, $this->action);
        return json_decode($response);
    }

    public function showAuthorization($authorization_id)
    {
        // TODO delete after test
        $authorization_id = "2DC87612EK520411B";
        $this->action = 'GET';
        $this->endpoint = 'v1/payments/authorization/'.$authorization_id;
        $response = $this->makeCall(null, $this->endpoint, $this->action);
        return json_decode($response);
    }

    public function captureAuthorization($body, $authorization_id)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/authorization/'.$authorization_id.'/capture';
        $response = $this->makeCall($this->getBody($body), $this->endpoint, $this->action);
        return json_decode($response);
    }

    public function voidAuthorization($authorization_id)
    {
        // TODO delete after test
        $authorization_id = "2DC87612EK520411B";
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/authorization/'.$authorization_id.'/void';
        $response = $this->makeCall(null, $this->endpoint, $this->action);
        return json_decode($response);
    }

    public function showCapture($capture_id)
    {
        // TODO delete after test
        $capture_id = "8F148933LY9388354";
        $this->action = 'GET';
        $this->endpoint = 'v1/payments/capture/'.$capture_id;
        $response = $this->makeCall(null, $this->endpoint, $this->action);
        return json_decode($response);
    }

    public function refundCapture($body, $capture_id)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/payments/capture/'.$capture_id.'/refund';
        $response = $this->makeCall($this->getBody($body), $this->endpoint, $this->action);
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

    protected function makeCall($body = null, $url = null, $action = "POST", $cnt_type = "application/json", $user = null)
    {

        $curl = curl_init();
        if ($action == "GET") {
            $body = (is_array($body)) ? http_build_query($body) : $body;
            $url = $url.$body;
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $this->urlAPI.$url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
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
                "Authorization: Bearer A101.AeuzH29HiJvVSwpkeAxZGL8FPkAI-bX26QIh_Q2evXKX-fzJ6WUSrtKUuRWtnyoN.6WMol7MnSAqYrC8ylFTJXND_RnS",
            ));
        }

        $response = curl_exec($curl);
        //var_dump(curl_errno($curl));die;
        if (curl_errno($curl)) {
            die('error occured during curl exec. Additioanl info: ' . curl_errno($curl).':'. curl_error($curl));
        }
        return ($response);

    }
}
