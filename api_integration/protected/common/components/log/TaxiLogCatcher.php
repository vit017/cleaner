<?php






class TaxiLogCatcher extends TaxiLog implements ITaxiLogEventHandler
{
    
    public $fileName = '__catched_by_TaxiLogCatcher.log';

    
    public $maxFileSize = 10048576;

    
    public $lineLengthLimit = 4096;

    

    

    
    public function getCriterias()
    {
        $t = new TaxiLogCatchCriteria();
        $t->fileRoute = '__catched___ping_createOrder.log';
        $t->messagePreg = '/ping|createOrder/';

        $t = new TaxiLogCatchCriteria();
        $t->fileRoute = '__taxi_createOrder.log';
        $t->messagePreg = '/INETADDORDER|AJAX processing command createOrder|Executing server command \'createOrder\'/' ;
        return array($t);
    }

    

    
    protected function writeToCriteriaPath($criteria)
    {
        $oldFileName = $this->fileName;
        $criteriaFileName = $criteria->getFileRoute();
        if ($criteriaFileName) {
            $this->fileName = $criteriaFileName;
            $this->writeInfo($criteria->info);
        }
        $this->fileName = $oldFileName;
    }

    
    protected function needCatch($info)
    {
        $res = false;
        $criterias = $this->getCriterias();
        foreach ($criterias as $criteria) {
            $criteria->info = $info;
            if ($criteria->needCatch()) {
                $this->writeToCriteriaPath($criteria);
                $res = true;
            }
        }
        return $res;
    }

    
    public function onLogWrite($info)
    {
        if ($this->needCatch($info)) {
            $this->writeInfo($info);
        }
    }

}
