<?php


class TaxiEvent extends TaxiObject
{
    
    public $sender;

    
    public function __construct($sender)
    {
        $this->sender = $sender;
    }

}
