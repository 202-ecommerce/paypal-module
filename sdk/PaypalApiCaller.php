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

abstract class PaypalApiCall
{
    /**
     *  Host of call
     * @var string
     */
    protected $host;

    /**
     * Endpoint
     * @var string
     */
    protected $endpoint;

    /**
     * Instance of module who use this class
     * @var Module
     */
    protected $module;

    /**
     * Response of call
     * @var string
     */
    protected $response;

    /**
     * Only POST, GET for the moment
     * @var string
     */
    protected $action;

    /**
     * Logs
     * @var array
     */
    protected $logs = array();

    /**
     * If debug mod
     * @var boolean
     */
    protected $debug = true;

    /**
     * 401 User
     * @var string
     */
    protected $user;

    /**
     * 401 Password
     * @var string
     */
    protected $passwd;

    /**
     * Initialization
     */
    public function __construct($host, $module, $user = null, $passwd = null)
    {
        $this->host     = $host;
        $this->module   = $module;
        $this->user     = $user;
        $this->passwd   = $passwd;
    }

    /**
     * Get body
     * @return mixed Create body
     */
    abstract protected function getBody(array $fields);

    /**
     * Make call
     * @param  mixed  $body        body content
     * @param  mixed  $http_header header content
     * @param  string $user        User login
     * @param  string $passwd      User password
     * @return mixed               Call
     */
    protected function makeCall($body = null, $http_header = null, $user = null)
    {
        // init uri to call
        $uri_to_call = $this->host.$this->endpoint;


        $stream_context = array();

        if ($body != null) {

            if ($http_header) {
                $stream_context = stream_context_create(
                    array (
                        'http' => array (
                            'method' => 'POST',
                            'content' => $body,
                            'header'=> $http_header
                            )
                    )
                );
            } else {
                $stream_context = stream_context_create(
                    array (
                        'http' => array (
                            'method' => 'POST',
                            'content' => $body
                            )
                    )
                );
            }
      
        } else {
            $stream_context = array();
        }

        $test = array($http_header, $body, $this->user, $this->passwd);

        $this->response = $this->fileGetContents($uri_to_call, false, $stream_context);
        

        if ($this->response != false) {
            $this->response = json_decode($this->response);
        }



    }

    private function fileGetContents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 15)
    {
        if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
            $stream_context = @stream_context_create(array('http' => array('timeout' => $curl_timeout)));
        }
echo $url;
var_dump($stream_context);
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($curl, CURLOPT_TIMEOUT, $curl_timeout);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            if ($stream_context != null) {
                $opts = stream_context_get_options($stream_context);
               
                if (isset($opts['http']['method']) && Tools::strtolower($opts['http']['method']) == 'post') {
                    curl_setopt($curl, CURLOPT_POST, true);
                    if (isset($opts['http']['content'])) {
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $opts['http']['content']);
                    }
                    if (isset($opts['http']['header'])) {
                        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                           "Content-type: application/json",
                            "Content-Length: " . strlen($opts['http']['content']),
                            "Authorization: ".$opts['http']['header']
                        ));
                    } else {
                        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                           "Content-type: application/json",
                            "Content-Length: " . strlen($opts['http']['content']),
                            "Authorization: Bearer 4muUxf8pQEdsXYZL1Y8frK9owE1VEmzdT0HUjpT5QEntuFlbKJyIWIP0D9WaLAIOaqHHTg"
                        ));
                    }
                } else {
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                       "Content-type: application/json",
                        "Authorization: Bearer 4muUxf8pQEdsXYZL1Y8frK9owE1VEmzdT0HUjpT5QEntuFlbKJyIWIP0D9WaLAIOaqHHTg"
                    ));
                }
            }


            $content = curl_exec($curl);
            var_dump(curl_errno($curl));
            var_dump(curl_error($curl));
var_dump($content);

            curl_close($curl);

            return $content;
        } else {
            return false;
        }
    }

}
