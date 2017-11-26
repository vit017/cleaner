<?php


class TaxiFilesFilterCascade
{
    
    const LOGIC_MODE_AND = 'and';
    
    const LOGIC_MODE_OR = 'or';
    
    public $defaultMatchResult = true;
    
    public $logicMode;
    
    public $beginPart = array();
    
    public $substr = array();
    
    public $pregs = array();
    
    public $baseNamePregs = array();
    private $_path;
    public function __construct()
    {
        $this->logicMode = self::LOGIC_MODE_OR;
    }
    
    protected function logicOperand($args)
    {
        $res = reset($args);
        if ($this->logicMode === self::LOGIC_MODE_OR) {
            foreach ($args as $arg) {
                $res = $arg || $res;
            }
        } elseif ($this->logicMode === self::LOGIC_MODE_AND) {
            foreach ($args as $arg) {
                $res = $arg && $res;
            }
        }
        return $res;
    }
    
    protected function hasConditions()
    {
        return !empty($this->baseNamePregs) ||
                !empty($this->beginPart) ||
                !empty($this->pregs) ||
                !empty($this->substr);
    }
    
    protected function callAnyPregs($pregs, $value)
    {
        foreach ($pregs as $preg) {
            if (preg_match($preg, $value)) {
                return true;
            }
        }
        return false;
    }
    
    protected function callBaseNamePregs()
    {
        $baseName = pathinfo($this->_path, PATHINFO_BASENAME);
        return $this->callAnyPregs($this->baseNamePregs, $baseName);
    }
    
    protected function callBeginPart()
    {
        foreach ($this->beginPart as $part) {
            if (mb_strpos($this->_path, $part) === 0) {
                return true;
            }
        }
        return false;
    }
    
    protected function callPregs()
    {
        return $this->callAnyPregs($this->pregs, $this->_path);
    }
    
    protected function callSubstr()
    {
        foreach ($this->substr as $part) {
            if (mb_strpos($this->_path, $part) !== false) {
                return true;
            }
        }
        return false;
    }
    
    protected function callConditions()
    {
        return array(
            $this->callBaseNamePregs(),
            $this->callBeginPart(),
            $this->callPregs(),
            $this->callSubstr(),
        );
    }
    
    public function match($path)
    {
        if ($this->hasConditions()) {
            $this->_path = $path;
            $conditions = $this->callConditions();
            return $this->logicOperand($conditions);
        } else {
            return $this->defaultMatchResult;
        }
    }
}
