<?php


class TaxiInfo extends TaxiObject
{
    
    public function afterFill()
    {
    }
    
    public function beforeJsonEncode()
    {
        $this->afterFill();
    }
}
