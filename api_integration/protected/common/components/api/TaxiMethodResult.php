<?php


class TaxiMethodResult extends stdClass implements ITaxiQueryCommandResult
{
    
    const STATUS_SUCCESS = 1;
    
    const STATUS_FAIL = 0;
    
    public $commandName;
    
    public $result;
    
    public $status;
    
    public $errorCode;
    
    public $errorMessage;
    
    public $serverTime;
    
    public $clientTime;
    
    public $rawAnswer;
    
    
    public function isSuccessful()
    {
        return $this->status == self::STATUS_SUCCESS;
    }
    
    
    public function afterSuccessExecute($client)
    {
        return true;
    }
    
    public function afterFailExecute($client)
    {
        if (!$this->errorCode) {
            $this->errorCode = TaxiServerErrors::INTERNAL_ERROR;
        }
        if (!$this->errorMessage) {
            $this->errorMessage = $this->errorMessage;
        }
        return true;
    }
    
    
    public function hasErrors()
    {
        return $this->status && !$this->errorCode && !$this->errorMessage;
    }
    
    public function validate()
    {
        
        return true;
    }
    
    
    public function updateServerTime()
    {
        $this->serverTime = $this->createTimeStamp();
    }
    
    public function updateClientTime()
    {
        $this->clientTime = $this->createTimeStamp();
    }
    
    public function createTimeStamp()
    {
        $res = date('j-m-Y H:i:s.u');
        $parts = explode(' ', microtime());
        $res = str_replace('000000', trim($parts[0], ' 0.'), $res);
        return $res;
    }
}
