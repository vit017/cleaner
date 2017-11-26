<?php


class TaxiLogInfo extends TaxiObject
{
    protected $_sender;
    protected $_message = '';
    protected $_level;
    protected $_weight = 0;
    protected $_dumps = array();
    protected $_defaultLineLimit = 2048;
    
    
    
    public function getSenderClass()
    {
        if (is_object($this->_sender)) {
            return get_class($this->_sender);
        }
    }
    
    protected function getTimeStamp()
    {
        $res = date('[j-n-Y][H:i:s.u]');
        $parts = explode(' ', microtime());
        $res = str_replace('000000', trim($parts[0], ' 0.'), $res);
        return $res;
    }
    
    protected function getLevelMark()
    {
        $mark = '';
        if ($this->level == TaxiLog::LEVEL_ERROR) {
            $mark .= '!!!' . TaxiLog::LEVEL_ERROR;
        } elseif ($this->level == TaxiLog::LEVEL_WARNING) {
            $mark .= '~' . TaxiLog::LEVEL_WARNING;
            return $mark;
        }
    }
    
    protected function getSenderMark()
    {
        $mark = '';
        if (is_object($this->sender)) {
            $senderClass = get_class($this->sender);
            $mark .= "[{$senderClass}]({$this->weight})";
        }
        return $mark;
    }
    
    
    public function createLine($lineLimit = null)
    {
        if (!$lineLimit) {
            $lineLimit = $this->_defaultLineLimit;
        }
        $line = $this->getLevelMark() . $this->getTimeStamp() . $this->getSenderMark();
        $line .= ' ' . $this->message . "\n";
        $strLen = mb_strlen($line);
        if ($strLen > $lineLimit) {
            $line = mb_strcut($line, 0, $lineLimit) . " ... <{$strLen} bytes> \n";
        }
        return $line;
    }
}
