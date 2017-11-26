<?php

require_once dirname(__FILE__) . '/GootaxModel_Apitm.php';

class GootaxApi extends GootaxModel_Apitm
{
    public $adapter = "";

    public function sendGetTo(&$data, $method, $params = array())
    {
		$this->writeLog('method', $method);
        $result = $this->get($method, $params);
		$this->writeLog('result', $result);

        //$this->adapter->setRawAnswer($result);

        if ($result) {
            $data = $result;
            return true;
        } else {
            return false;
        }
    }

    public function sendPostTo(&$data, $method, $params = array())
    {
		$this->writeLog('method', $method);
        $result = $this->post($method, $params);
		$this->writeLog('result', $result);

        if ($result) {
            $data = $result;
            return true;
        } else {
            return false;
        }
    }

    public function sendAutocomplete(&$data, $method, $params = array())
    {
        $this->writeLog('method', $method);
        $result = $this->getAutocomplete($method, $params);
        $this->writeLog('result', $result);

        if ($result) {
            $data = $result;
            return true;
        } else {
            return false;
        }
    }
}