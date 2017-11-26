<?php




class TaxiSmsValidator extends TaxiObject
{
    private $_cache;

    

    
    private function getCache()
    {
        if (!$this->_cache) {
            
            $this->_cache = new TaxiFileCache('/smsValidatorCache.dat');
            $this->_cache->expire = 600;
        }
        return $this->_cache;
    }

    
    protected function getSmsList()
    {
        $list = $this->getCache()->getValue('smsList');
        if ($list === null) {
            $list = array();
        }
        return $list;
    }

    
    protected function setSmsList($list)
    {
        $this->getCache()->setValue('smsList', $list);
    }

    
    
    public $tryCountLimit = 10;

    
    public function generateSms($phone)
    {
        $code = rand(100, 999);
        $row = array(
            'smsCode' => $code,
            'tryCount' => $this->tryCountLimit,
        );
        $list = $this->smsList;
        $list[$phone] = $row;
        $this->smsList = $list;
        return $code;
    }

    
    public function validateSms($phone, $smsCode, &$remainedAttempts)
    {
        $list = $this->getSmsList();
        if (!isset($list[$phone])) {
            return null;
        }
        $row = $list[$phone];
        if ($row['smsCode'] == $smsCode && $row['tryCount'] > 0) {
            $remainedAttempts = $row['tryCount'];
            return true;
        } else {
            $row['tryCount']--;
            $remainedAttempts = $row['tryCount'];
            $list[$phone] = $row;
            $this->setSmsList($list);
            return false;
        }
    }

    
    public function clear()
    {
        return $this->getCache()->flush();
    }

}
