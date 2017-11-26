<?php


class TaxiFileCache extends TaxiObject implements ITaxiCache
{
    
    public $fileName = '/cache.dat';
    
    public $enabled = true;
    
    public $expire = 186400;
    
    private $_data = array();
    
    
    public function __construct($fileName = null)
    {
        if ($fileName) {
            $this->fileName = $fileName;
        }
        $this->read();
    }
    
    public function __destruct()
    {
           }
    
    
    public function getValue($key)
    {
        if (empty($this->_data)) {
            $this->read();
        }
        if ($this->enabled && isset($this->_data[$key]['value']) && isset($this->_data[$key]['expire'])) {
            if ($this->_data[$key]['expire'] > time()) {
                return $this->_data[$key]['value'];
            } else {
                unset($this->_data[$key]);
            }
        } else {
            return null;
        }
    }
    
    public function setValue($key, $value, $expire = null)
    {
        if (!$expire) {
            $expire = time() + $this->expire;
        } else {
            $expire = time() + $expire;
        }
        $this->_data[$key]['value'] = $value;
        $this->_data[$key]['expire'] = $expire;
        return $this->write();
    }
    
    public function flush()
    {
        $this->data = array();
        return $this->write();
    }
    
    private function invalidate()
    {
        $time = time();
        foreach ($this->_data as $key => $row) {
            if ($row['expire'] < $time) {
                unset($this->_data[$key]);
            }
        }
        $this->write();
    }
    
    
    public function getFilePath()
    {
        return TaxiEnv::$DIR_RUNTIME . '/cache/' . $this->fileName;
    }
    
    
    private function restoreData($rawString)
    {
        $data = null;
        @$data = unserialize($rawString);
        if (is_array($data)) {
            $this->_data = $data;
            return true;
        } else {
            return false;
        }
    }
    
    private function createDataString()
    {
        return serialize($this->_data);
    }
    
    private function read()
    {
        $filePath = $this->getFilePath();
        if (is_file($filePath)) {
            return $this->restoreData(file_get_contents($filePath));
        }
    }
    
    private function write()
    {
        return file_put_contents($this->getFilePath(), $this->createDataString());
    }
}
