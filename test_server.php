<?php
/**
 * Created by PhpStorm.
 * User: Fedotchenko
 * Date: 12/01/2017
 * Time: 17:33
 */
include_once _PS_MODULE_DIR_.'paypal/sdk/PaypalSDK.php';

class PaypalTest {

    protected $action;
    protected $endpoint;
    protected $response;
    protected $urlAPI;
    public $error;

    public function __construct()
    {
        $this->urlAPI = 'https://api.sandbox.paypal.com/';
    }

    public function createAccessToken($body)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/oauth2/token';
        $response = $this->makeCall($body, $this->endpoint, $this->action, "application/json", "AReLzfjunEgE3vvOxUgjPQZZXe2L9tcxI0NVIUzOF8BAmB8G4I0qsEUwptPtVF1Ioyu1TpAMQtG_nAeG:EAhYgH_trpF1FGeXQzia4UWAPasM6zVVCY6gCFT9m7RsDbe7nCV2LXs2Ewn9YW32nEIqh1bR0zNfrZOS");

        return $response;
    }

    public function createPartnerReferrals($body, $token)
    {
        $this->action = 'POST';
        $this->endpoint = 'v1/customer/partner-referrals';
        $response = $this->makeCall($body, $this->endpoint, $this->action, "application/json", null, $token);

        return json_decode($response);
    }

    protected function makeCall($body = null, $url = null, $action = "POST", $cnt_type = "application/json", $user = null, $token = null)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $this->urlAPI.$url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
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
                "Authorization: Bearer ".$token,
            ));
        }

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            return json_encode(array('error' => 'error occured during curl exec. Additioanl info: ' . curl_errno($curl).':'. curl_error($curl)));
        }
        return ($response);

    }


}

$paypaltest = new PaypalTest(1);
$response = $paypaltest->createAccessToken("grant_type=client_credentials");
$response_decode = json_decode($response);
$access_token = $response_decode->access_token;
/*if ($_POST['getToken']) {
    echo json_encode($access_token);
    return true;
}*/
//print_r($response);die;
$referrals = $paypaltest->createPartnerReferrals(json_encode($_POST), $access_token);

if ($referrals->name) {
    $referrals->error = "Error API: ".$referrals->name." : ".$referrals->message;
}
$link = $referrals->links[1]->href;
$response = array(
    'success'=> isset($referrals->error) ? false : true,
    'data'=> array(
        'link'=> $link,
    ),
    'error'=> isset($referrals->error) ? $referrals->error : null,
);

echo json_encode($response);