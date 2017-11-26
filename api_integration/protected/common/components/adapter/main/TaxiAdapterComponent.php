<?php




class TaxiAdapterComponent extends TaxiObject
{
    
    public $adapter;

    
    public function __construct($adapter)
    {
        $this->adapter = $adapter;
    }

}
