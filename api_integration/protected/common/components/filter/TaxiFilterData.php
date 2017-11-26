<?php




class TaxiFilterData extends stdClass
{
    
    public $methodName;

    
    public $params;

    
    public $result;

    

    
    public function __construct($methodName, $params, $result = null)
    {
        $this->methodName = $methodName;
        $this->params = $params;
        $this->result = $result;

        return $this;
    }

    
}
