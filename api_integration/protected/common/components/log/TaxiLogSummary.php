<?php




class TaxiLogSummary extends TaxiLog implements ITaxiLogEventHandler
{
    
    public $fileName = '___TaxiLogSummary_log.log';

    
    public $maxFileSize = 10048576;    

    
    public $lineLengthLimit = 512;

    

    


    

    
    public function onLogWrite($info)
    {
        $this->writeInfo($info);
    }

}
