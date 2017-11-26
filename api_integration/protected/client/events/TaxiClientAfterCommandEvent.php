<?php




class TaxiClientAfterCommandEvent extends TaxiClientEvent
{
    
    public $commandName;

    
    public $commandParams;

    
    public $result;
    
    
    

    
    public function syncronizeWith($otherEvent)
    {
        $this->commandName = $otherEvent->commandName;
        $this->commandParams = $otherEvent->commandParams;
        $this->result = $otherEvent->result;
    }

}
