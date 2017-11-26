<?php

class TaxiValidateErrorsInfo extends TaxiInfo
{
    
    public $command;
    
    public $count = 0;
    
    public $summaryText = '';
    
    public $summaryHtml = '';
    
    public $errors = array();
    
    
    public function clear()
    {
        $this->errors = array();
        $this->afterFill();
    }
    
    public function addError($paramName, $message)
    {
        $this->errors[$paramName] = $message;
    }
    
    public function afterFill()
    {
        $this->summaryHtml = '<font class="errortext">'
                . implode('<br>', $this->errors)
                . '</font>';
        $this->summaryText = implode("\n", $this->errors);
        $this->count = count($this->errors);
    }
    
    public function mergeWith($otherInfo)
    {
        $this->errors = array_merge($this->errors, $otherInfo->errors);
        $this->afterFill();
        return $this;
    }
}
