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

class PaypalSDK
{
    private $action;
    private $endpoint;
    private $token;
    private $clientId;
    private $secret;
    private $urlAPI;

    public function __construct($sandbox=0)
    {
        $this->clientId = '';
        $this->secret = '';
        $this->action = 'POST';
        if ($sandbox) {
            $this->urlAPI = 'https://api-3t.sandbox.paypal.com/nvp';
        } else {
            $this->urlAPI = 'https://api-3t.paypal.com/nvp';
        }
    }

    public function getUrlOnboarding($body)
    {
        $response = $this->makeCallSI(http_build_query($body, '', '&'));
        return $response;
    }

    public function getCredentials()
    {
        $this->action = 'GET';
        $this->endpoint = '/v1/identity/applications/@classic/owner/HM5T4HLJMQG6Q/credentials';
        $this->makeCall(null, $this->endpoint, $this->action);

        return $this->response;
    }



    public function makeCallPaypal($body)
    {
        $response = $this->makeCall(http_build_query($body, '', '&'));
        return $response;
    }

    protected function makeCallSI($body = null, $need_user = false)
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, "http://paypal-sandbox.pp-ps-auth.com/getUrl?".$body);


        $response = curl_exec($curl);



        return $response;
    }

    protected function makeCall($body = null, $need_user = false)
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

        $response = curl_exec($curl);
        $result = explode('&', $response);
        foreach ($result as $value) {
            $tmp = explode('=', $value);
            $return[$tmp[0]] = urldecode(!isset($tmp[1]) ? $tmp[0] : $tmp[1]);
        }

        if (curl_errno($curl)) {
            die('error occured during curl exec. Additioanl info: ' . curl_errno($curl).':'. curl_error($curl));
        }
        return $return;
    }
}
