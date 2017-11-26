<?php


class TaxiSimpleAutoloader extends stdClass
{
    
    public $map = array();
    
    protected $_realMap = array();
    
    public $baseDir;
    
    public $aliases = array();
    
    
    public function getRealMap()
    {
        return $this->_realMap;
    }
    
    
    protected function processAliases($path)
    {
        $replaced = str_replace(array_keys($this->aliases), $this->aliases, $path);
        if (strpos($replaced, '//') === 0) {
            $replaced = str_replace('//', '/', $replaced);
            $real = $this->baseDir . $replaced;
        } else {
            $real = $replaced;
        }
        $real = str_replace('\\', '/', $real);
        return $real;
    }
    
    public function init()
    {
        $this->_realMap = array();
        foreach ($this->map as $className => $filePath) {
            if (is_integer($className) && preg_match('/[\\/]([^\\/]+)\.php$/', $filePath, $m)) {
                $className = $m[1];
            }
            $real = $this->processAliases($filePath);
            $this->_realMap[$className] = $real;
        }
        return $this->_realMap;
    }
    
    protected function tryInclude($class)
    {
        if (isset($this->_realMap[$class])) {
            $path = $this->_realMap[$class];
            if (is_file($path)) {
                include $path;
                return true;
            }
        }
        return false;
    }
    
    public function includeClass($class, $deepLevel = 0)
    {
        return $this->tryInclude($class);
    }
    
    public function registerPhpAutoloader()
    {
        $this->init();
        return spl_autoload_register(array($this, 'includeClass'));
    }
    
    public function unregisterPhpAutoloader()
    {
        return spl_autoload_unregister(array($this, 'includeClass'));
    }
    
    public function mergeMapWith($map)
    {
        $this->map = array_merge($this->map, $map);
        $this->init();
    }
    
    public function findClassPath($class)
    {
        $map = $this->getRealMap();
        if ($map && isset($map[$class])) {
            return $map[$class];
        }
    }
    
    public function findClassDir($class)
    {
        $path = $this->findClassPath($class);
        if ($path) {
            return dirname($path);
        }
    }
}
