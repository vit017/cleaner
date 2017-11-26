<?php


class TaxiConfig extends TaxiObject
{
    
    private $_configArray;
    
    
    public function __construct()
    {
        $path = $this->getConfigPath() . '/config.php';
        if (file_exists($path)){
            $this->_configArray = require $path;
        }
        
        if (TaxiEnv::$FLAG_DISPLAY_ERRORS) {
            ini_set('error_reporting', E_ALL);
            ini_set("display_errors", "1");
            ini_set("display_startup_errors", "1");
            ini_set('allow_url_fopen', '1');
            error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
        } else {
            ini_set('error_reporting', E_ERROR);
            ini_set("display_errors", "0");
            ini_set("display_startup_errors", "0");
            ini_set('allow_url_fopen', '1');
            error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
        }
        
        if (!isset($this->_configArray['types'])) {
            $this->_configArray['types'] = array();
        }
        if (!isset($this->_configArray['logs'])) {
            $this->_configArray['logs'] = array();
        }
        if (!isset($this->_configArray['adapters'])) {
            $this->_configArray['adapters'] = array();
        }
    }
    
    
    public function getConfigArray()
    {
        return $this->_configArray;
    }
    
    public function getClientConfig()
    {
        return $this->_configArray['client'];
    }
    
    public function getAdapterTypesConfig()
    {
        return $this->_configArray['types'];
    }
    
    public function getAdaptersConfig()
    {
        return $this->_configArray['adapters'];
    }
    
    public function getServerConfig()
    {
        return $this->_configArray['server'];
    }
    
    public function getLogsConfig()
    {
        return $this->_configArray['logs'];
    }
    
    public function getConfigPath()
    {
        return TaxiEnv::$DIR_ROOT . '/config';
    }
    
    public function loadClassConfigTo($object, $fromConfig = null)
    {
        $class = get_class($object);
        if (!$fromConfig) {
            $fromConfig = $class . '.php';
        }
        $filePath = $this->getConfigPath() . '/' . $fromConfig;
        if (file_exists($filePath)) {
            $options = require $filePath;
            self::applyOptionsTo($options[$class], $object);
            return true;
        }
    }
    
    public static function applyOptionsTo($options, $object)
    {
        if (is_array($options)) {
            foreach ($options as $propertyName => $value) {
                $object->{$propertyName} = $value;
            }
        }
    }
}
