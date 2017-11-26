<?php




abstract class TaxiServerCommandsLayer extends TaxiServerEventsLayer
{
    
    public $developerIps = array('127.0.0.1');

    

    
    public function isDeveloperIp()
    {
        return key_exists($_SERVER['REMOTE_ADDR'], array_flip($this->developerIps));
    }

    
    private function useReturnRawAnswer()
    {
        if (!$this->allowRawAnswer) {
            return false;
        } elseif ($this->isDeveloperIp()) {
            return true;
        } else {
            return false;
        }
    }

    

    
    private function createAnyMethodResult($result)
    {
        $res = new TaxiMethodResult();

        $res->updateServerTime();

        $res->result = $result;
        $res->errorMessage = $this->getErrorMessage();
        $res->errorCode = $this->_errorCode;
        $res->rawAnswer = $this->useReturnRawAnswer() ? $this->getLastRawAnswer() : null;

        return $res;
    }

    
    private function createErrorMethodResult($result)
    {
        $res = $this->createAnyMethodResult($result);
        $res->status = 0;

        return $res;
    }

    
    private function createSuccessMethodResult($result)
    {
        $res = $this->createAnyMethodResult($result);
        $res->status = 1;

        return $res;
    }

    
    protected function echoResult($result)
    {
        if ($result !== null) {
            $answer = $this->createSuccessMethodResult($result);
        } else {
            $answer = $this->createErrorMethodResult($result);
        }
        echo json_encode($answer);
        return $result !== null;
    }

    
    protected function createCurrentAdapter($adapterKey)
    {
        $adapterKey = $adapterKey ? $adapterKey : $this->_defaultAdapterKey;
        $adapter = $this->adapters->createAdapter($adapterKey);
        $adapterClass = get_class($adapter);
        return $adapter;
    }

    
    public function echoEmulation($result)
    {
        if ($result !== null) {
            $answer = $this->createSuccessMethodResult($result);
        } else {
            $answer = $this->createErrorMethodResult($result);
        }
        return (array) $answer;
    }

    
    public function executeCommandEmulation($commandName, $paramsPart = array(),
            $adapterKey = null)
    {
        $result = $this->executeCommand($commandName, $paramsPart, $adapterKey);
        if ($result instanceof TaxiInfo) {
            $result->beforeJsonEncode();
        }
        return $this->echoEmulation($result);
    }

    
    private function createInputParams($paramsString, $command, $sign)
    {
        $params = json_decode($paramsString, true);
        if (!is_array($params)) {
            $this->afterFailJsonDecode($paramsString);
            return false;
        }
        if ($this->createSign($command, $paramsString) != $sign) {
            $this->afterCorruptSign($command, $sign);
            return false;
        }
        return $params;
    }

    
    public function processCommandFromPost($command, $sign, $paramsString,
            $adapterKey)
    {
        $params = $this->createInputParams($paramsString, $command, $sign);
        if ($params !== false) {
            $result = $this->executeCommand($command, $params, $adapterKey);
            return $result;
        }
    }

}
