<?php


class TaxiLogCatchCriteria extends TaxiObject
{
    protected $_info;
    protected $_classPreg;
    protected $_messagePreg;
    protected $_levelPreg;
    protected $_weightPreg;
    protected $_fileRoute;
    
    
    public function getFileRoute()
    {
        return $this->_fileRoute;
    }
    
    
    private function checkPregConditions($map)
    {
        foreach ($map as $test => $preg) {
            if ($test && $preg && preg_match($preg, $test)) {
                return true;
            }
        }
    }
    
    protected function checkPregs()
    {
        $info = $this->info;
        $map = array(
            $info->message => $this->messagePreg,
            $info->level => $this->levelPreg,
            $info->weight => $this->weightPreg,
            $info->getSenderClass() => $this->classPreg,
        );
        return $this->checkPregConditions($map);
    }
    
    protected function modifyInfo()
    {
        $this->info = clone ($this->info);
        return false;
    }
    
    public function needCatch()
    {
        if (!$this->_info) {
            return false;
        }
        if ($this->checkPregs()) {
            $this->modifyInfo();
            return true;
        }
    }
}
