<?php


class TaxiClientConnection extends TaxiConnection implements ITaxiClientConnection
{
    
    public $translateGetParams = array();
    
    protected $_timeout = 30;
    
    private $_host;
    
    private $_secretKey;
    public function __construct()
    {
        $this->_log = new TaxiLog($this, 'TaxiClient');
        
        $this->translateGetParams = array(
            TaxiAutorizationInfo::REQUEST_BROWSER_KEY_KEY,
            TaxiAutorizationInfo::REQUEST_TOKEN_KEY,
        );
    }
    
    
    public function setHost($value)
    {
        $this->_host = $value;
    }
    
    public function setSecretKey($value)
    {
        $this->_secretKey = $value;
    }
    
    public function getHost()
    {
        return $this->_host;
    }
    
    public function getSecretKey()
    {
        return $this->_secretKey;
    }
    
    public function createSign($commandName, $paramsEncodedString)
    {
        return sha1($commandName . $paramsEncodedString . $this->getSecretKey());
    }
    
    public function send($commandName, $params = array(), $adapterKey = null)
    {
        $paramsEncodedString = json_encode($params);
        $sign = $this->createSign($commandName, $paramsEncodedString);
        $url = $this->getCurlUrl();
        if (!$this->_host || !$this->_secretKey) {
            $this->log->error("Wrong or empty? host and secretKey for this Connection!");
            return false;
        }
        return $this->sendPostRequest(array(
                    'command' => $commandName,
                    'sign' => $sign,
                    'params' => $paramsEncodedString,
                    'adapter' => $adapterKey,
        ));
    }
    
    private function injectTraslatedGetParamsTo($url)
    {
        return Hc::url_replace_params($url, $this->createTranslatedGetParams());
    }
    
    
    private function createTranslatedGetParams()
    {
        $res = array();
        foreach ($this->translateGetParams as $name) {
            if (isset($_GET[$name])) {
                $res[$name] = $_GET[$name];
            }
        }
        return $res;
    }
    
    private function getCurlUrl()
    {
        $url = $this->injectTraslatedGetParamsTo($this->getHost());
        return $url;
    }
    
    
    private function getCookieString()
    {
        $res = '';
        foreach ($_COOKIE as $key => $value) {
            $res .= "$key=$value; ";
        }
        return $res;
    }
    
    private function sendPostRequest($params)
    {
        $paramsString = http_build_query($params);
        $url = $this->getCurlUrl();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $paramsString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
        curl_setopt($ch, CURLOPT_COOKIE, $this->getCookieString());
        $resultString = curl_exec($ch);
        $errorCode = curl_errno($ch);
        curl_close($ch);
        if ($errorCode == CURLE_OK) {
            $result = json_decode($resultString, true);
            if (is_array($result)) {
                return $result;
            } else {
                $this->log->error('Raw CURL answer from ouwer server: ' . $resultString);
                return false;
            }
        } else {
            $this->log->error('BAD CURL answer from ouwer server or timeout?: ' . $resultString);
            return false;
        }
    }
}
