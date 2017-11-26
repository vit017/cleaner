<?php
/**
 * Created by JetBrains PhpStorm.
 * User: unklefedor
 * Date: 30.09.13
 * Time: 12:40
 * To change this template use File | Settings | File Templates.
 */

class bhMailChimp{

    private $APIkey = NULL;
    private $APIurl = NULL;
    private $baseAPIurl = '.api.mailchimp.com/2.0';
    private $client = NULL;

    private $method = array(
        'ping' => '/helper/ping',
        'subscribe' => '/lists/subscribe'
    );

    public function __construct( $APIkey ){

        $this->APIkey = $APIkey;
        $this->generateApiUrl();
        $this->initCURL();
    }

    private function initCURL(){
        $this->client = curl_init();
        curl_setopt($this->client, CURLOPT_USERAGENT, 'MailChimp-PHP/2.0.3');
        curl_setopt($this->client, CURLOPT_POST, true);
        curl_setopt($this->client, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->client, CURLOPT_HEADER, false);
        curl_setopt($this->client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->client, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->client, CURLOPT_TIMEOUT, 600);
        curl_setopt($this->client, CURLOPT_SSL_VERIFYPEER, false);
    }

    private function generateApiUrl(){
        if ( $this->APIkey ){
            $p = explode('-',$this->APIkey);
            $sId = $p[1];
            $this->APIurl = 'https://'.$sId.$this->baseAPIurl;
        }
    }

    public function subscribe( $data ){
        $method = $this->APIurl.$this->method['subscribe'];
        $request = array(
            'id'                => $data['list'],
            'email'             => array('email' => $data['email']),
            'merge_vars'        => array('FNAME' => $data['name'],'LNAME' => $data['segment'] ),
            'double_optin'      => false,
            'update_existing'   => true,
            'replace_interests' => false,
            'send_welcome'      => false,
        );

        return $this->sendRequest( $method, $request );
    }

    private function sendRequest( $method, $request = array() ){
        $request['apikey'] = $this->APIkey;
        $request = json_encode($request);

        $ch = $this->client;
        curl_setopt($ch, CURLOPT_URL, $method.'.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $response_body = curl_exec($ch);
        $result = json_decode($response_body, true);

        if ( $result['error'] ){
            return false;
        }else{
            return true;
        }
    }

}
?>