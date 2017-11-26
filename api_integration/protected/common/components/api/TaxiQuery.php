<?php


class TaxiQuery extends stdClass implements ITaxiQueryCommand
{
    public $commandName;
    public $params;
    
    
    public function getCommandName()
    {
        return $this->commandName;
    }
    
    public function setCommandName($value)
    {
        $this->commandName = $value;
    }
    
    public function getParams()
    {
        return $this->params;
    }
    
    public function setParams($value)
    {
        $this->params = $value;
    }
    
    public function hasErrors()
    {
        
        return false;
    }
    
    public function validate()
    {
        
        return true;
    }
}
