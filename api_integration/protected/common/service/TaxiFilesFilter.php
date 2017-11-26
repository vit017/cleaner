<?php


class TaxiFilesFilter extends TaxiObject
{
    
    private $_removeRelativePath = '';
    
    public $level = -1;
    
    public $include;
    
    public $exclude;
    
    public $enableSlashReplacing = true;
    public function __construct()
    {
        $this->include = new TaxiFilesFilterCascade();
        $this->include->defaultMatchResult = true;
        $this->exclude = new TaxiFilesFilterCascade();
        $this->exclude->defaultMatchResult = false;
    }
    
    public static function replaceSlashes($path)
    {
        return str_replace('\\', '/', $path);
    }
    
    
    public function getRemoveRelativePath()
    {
        return $this->_removeRelativePath;
    }
    
    public function setRemoveRelativePath($path)
    {
        if ($this->enableSlashReplacing) {
            $path = self::replaceSlashes($path);
        }
        $this->_removeRelativePath = $path;
    }
    
    
    private function tryRemoveRelativePath($path)
    {
        if (!$this->_removeRelativePath) {
            return $path;
        } elseif (mb_strpos($path, $this->_removeRelativePath) === 0) {
            $path = str_replace($this->_removeRelativePath, '', $path);
        }
        return $path;
    }
    
    public function match($path)
    {
        if ($this->enableSlashReplacing) {
            $path = self::replaceSlashes($path);
        }
        $path = $this->tryRemoveRelativePath($path);
        $include = $this->include->match($path);
        $exclude = $this->exclude->match($path);
        return $include && !$exclude;
    }
    
    public function filterList($list)
    {
        $res = array();
        foreach ($list as $path) {
            if ($this->match($path)) {
                $res[] = $path;
            }
        }
        return $res;
    }
    
    public function getCFileHelperCallback()
    {
        return array(
            'pathMatchCallback' => array($this, 'match'),
        );
    }
}
