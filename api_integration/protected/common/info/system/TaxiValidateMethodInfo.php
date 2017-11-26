<?php

class TaxiValidateMethodInfo extends TaxiInfo
{
    
    public $command;
    
    public $paramsToValidate;
    
    public $fixedParams;
    
    public $hasErrors = false;
    
    public $errorsInfo;
    public function __construct()
    {
        $this->errorsInfo = new TaxiValidateErrorsInfo();
    }
}
