<?php

class GootaxModel_Apitm
{
    public $ip;
    public $port;
    public $tenantid;
    public $appid;
    public $api_key = '';
    public $timeout = 30;
    public $_log;

    public function setIp($ip = '0.0.0.0')
    {
        $this->ip = $ip;
    }

    public function setPort($port = 8089)
    {
        $this->port = $port;
    }

    public function setKey($key = '')
    {
        $this->api_key = $key;
    }

    public function setTenantid($tenant = '')
    {
        $this->tenantid = $tenant;
    }

    public function setAppid($appid = '')
    {
    $this->appid = $appid;
    }

    public function get($method, $params = '')
    {
        $lang = $_COOKIE["USER_LANG"];
		if (is_array($params)) {
			$params = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
		}

        if (strlen($params) > 0) {
            $url = $this->getUrl($method) . "?" . $params;
        } else {
            $url = $this->getUrl($method);
        }
		$signature = $this->getSignature($params);

		$this->writeLog('url', $url);
		$this->writeLog('HTTPHEADER', array(
			'typeclient: web',
            "lang: {$lang}",
			"tenantid: {$this->tenantid}",
            "appid: {$this->appid}",
			"signature: {$signature}",
			'Content-Type: application/x-www-form-urlencode'
		));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'typeclient: web',
			"lang: {$lang}",
			"tenantid: {$this->tenantid}",
            "appid: {$this->appid}",
			"signature: {$signature}",
			'Content-Type: application/x-www-form-urlencode'
		));
        $result = curl_exec($ch);
		$this->writeLog('result', $result);
        $errorCode = curl_errno($ch);
		$this->writeLog('errorCode', $errorCode);

        curl_close($ch);
		
		
        return ($errorCode == CURLE_OK) ? json_decode($result) : false;
    }

    public function getAutocomplete($method, $params = '')
    {
        $lang = $_COOKIE["USER_LANG"];

        if (is_array($params)) {
            $params = http_build_query($params);
        }

        if (strlen($params) > 0) {
            $url = 'https://geo.kabbi.eu/v1/' . $method . '?' . $params;
        } else {
            return false;
        }
        $signature = $this->getSignature($params);

        $this->writeLog('url', $url);
        $this->writeLog('HTTPHEADER', array(
            'typeclient: web',
            "lang: {$lang}",
            "tenantid: {$this->tenantid}",
            "signature: {$signature}",
            'Content-Type: application/x-www-form-urlencode'
        ));


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'typeclient: web',
            "lang: {$lang}",
            "tenantid: {$this->tenantid}",
            "signature: {$signature}",
            'Content-Type: application/x-www-form-urlencode'
        ));
        $result = curl_exec($ch);
        $this->writeLog('result', $result);
        $errorCode = curl_errno($ch);
        $this->writeLog('errorCode', $errorCode);

        curl_close($ch);

        /*
        var_dump($url);
        var_dump($result);
        die(__METHOD__);
        */

        return ($errorCode == CURLE_OK) ? json_decode($result) : false;
    }

    public function post($method, $params = '')
    {
        $lang = $_COOKIE["USER_LANG"];
        if (is_array($params)) {
            $paramsSign = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
		}
		$url = $this->getUrl($method);
		$signature = $this->getSignature($paramsSign);

		$this->writeLog('url', $url);
		$this->writeLog('HTTPHEADER', array(
			'typeclient: web',
            "lang: {$lang}",
			"tenantid: {$this->tenantid}",
            "appid: {$this->appid}",
			"signature: {$signature}",
			'Content-Type: application/x-www-form-urlencode'
		));

		$this->writeLog('params http_build_query', $params);

        if ($method == 'accept_password') {
            $headers = array(
                'typeclient: web',
                "lang: {$lang}",
                "tenantid: {$this->tenantid}",
                "appid: {$this->appid}",
                "signature: {$signature}",
                "deviceid: 098867352434",
            );
        } else {
            $headers = array(
                'typeclient: web',
                "lang: {$lang}",
                "tenantid: {$this->tenantid}",
                "appid: {$this->appid}",
                "signature: {$signature}",
                //'Content-Type: application/x-www-form-urlencode'
            );
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
		$this->writeLog('result', $result);
        $errorCode = curl_errno($ch);
		$this->writeLog('errorCode', $errorCode);

        curl_close($ch);
	

        return ($errorCode == CURLE_OK) ? json_decode($result) : false;
    }

    private function getUrl($method)
    {
		return "https://{$this->ip}:{$this->port}/{$method}";
    }

    private function getSignature($params)
    {
		$this->writeLog('sign', $params . $this->api_key);
        return MD5($params . $this->api_key);
    }

    public function writeLog($label, $message = '')
    {
        if (!$this->_log) {
            $this->_log = new TaxiLog($this);
        }
        $this->_log->info("{$label}: " . CVarDumper::dumpAsString($message));
    }

}