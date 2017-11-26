<?php


abstract class TaxiEventListener extends TaxiComponent
{   
    
    public $currentEvent;
    
    
    abstract public function onEvent($event);
}
