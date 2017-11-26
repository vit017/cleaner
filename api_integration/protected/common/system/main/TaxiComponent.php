<?php


class TaxiComponent extends TaxiModel
{
    
    protected $_log;
    
    public function __construct()
    {
        $this->log = new TaxiLog($this);
    }
}
