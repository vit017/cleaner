<?php




abstract class TaxiAccessRulle extends TaxiObject
{
    
    protected $_errors = array();

    
    protected $_adapter;

    
    public function __construct($adapter = null)
    {
        $this->_adapter = $adapter;
    }

    
    abstract public function checkAccess($commandName, $params);

    
    public function addError($message)
    {
        $this->_errors[] = $message;
    }

    
    public function getLastErrors()
    {
        return implode(', ', $this->_errors);
    }

}
