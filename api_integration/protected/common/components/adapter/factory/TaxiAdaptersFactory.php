<?php


class TaxiAdaptersFactory extends TaxiComponent
{
    
    private $_adaptersConfig;
    
    private $_typesConfig;
    
    private static $_testType = array(
        'dir' => null,
        'loader' => null,
        'class' => 'TaxiTestAdapter',
    );
    
    
    public function __construct()
    {
        $this->loadConfigs();
    }
    
    public function loadConfigs()
    {
        $this->_adaptersConfig = TaxiEnv::$config->getAdaptersConfig();
        $this->_typesConfig = TaxiEnv::$config->getAdapterTypesConfig();
    }
    
    
    private function proccessTypeTo(&$info)
    {
        if (isset($info['type'])) {
            $type = $info['type'];
            if (isset($this->_typesConfig[$type])) {
                $info = array_merge($info, $this->_typesConfig[$type]);
            } elseif ($type === 'test') {
                $info = array_merge($info, self::$_testType);
            }
        }
    }
    
    private function loadAdapterPhpFiles($info)
    {
        require_once $this->getAdaptersDir() . $info['dir'] . $info['loader'];
        $class = $info['class'];
        $filterPath = $this->getAdaptersDir() . $info['dir'] . '/' . $class . 'Filter.php';
        $validatorPath = $this->getAdaptersDir() . $info['dir'] . '/' . $class . 'Validator.php';
        if (is_file($validatorPath)) {
            require_once $validatorPath;
        }
        if (is_file($filterPath)) {
            require_once $filterPath;
        }
    }
    
    protected function isTestAdapterKey($adapterKey)
    {
        return preg_match('/^test$/', $adapterKey);
    }
    
    private function includeAdapterPhpFiles($info)
    {
        if ($info['dir'] && $info['loader']) {
            $this->loadAdapterPhpFiles($info);
        }
    }
    
    private function extractInfo($adapterKey)
    {
        if (!key_exists($adapterKey, $this->_adaptersConfig)) {
            return false;
        } else {
            $info = $this->_adaptersConfig[$adapterKey];
            $this->proccessTypeTo($info);
            return $info;
        }
    }
    
    private function createAdapterClassPath($infoDir, $class)
    {
        if ($infoDir) {
            return $this->getAdaptersDir() . $infoDir;
        } elseif ($class) {
            return TaxiAutoloader::findClassDir($class);
        }
    }
    
    private function internalCreateAdapter($adapterKey)
    {
        if ($info = $this->extractInfo($adapterKey)) {
            $this->includeAdapterPhpFiles($info);
            $class = $info['class'];
            $adapter = new $class;
            
            $adapter->key = $adapterKey;
            $adapter->adapterClassPath = $this->createAdapterClassPath($info['dir'], $class);
            self::applyOptionsTo($info['options'], $adapter);
            return $adapter;
        }
    }
    
    private function internalCreateTestAdapter()
    {
        $adapterKey = 'test';
        if ($info = $this->extractInfo($adapterKey)) {
            $adapter = new TaxiTestAdapter();
            $class = get_class($adapter);
            
            $adapter->key = $adapterKey;
            $adapter->adapterClassPath = TaxiEnv::$autoloader->findClassDir($class);
            self::applyOptionsTo($info['options'], $adapter);
            return $adapter;
        }
    }
    
    public function createAdapter($adapterKey)
    {
        if ($this->isTestAdapterKey($adapterKey)) {
            return $this->internalCreateTestAdapter();
        } else {
            return $this->internalCreateAdapter($adapterKey);
        }
    }
    
    
    public function getAdapters()
    {
        $keys = $this->getAdaptersKeys();
        $keys[] = 'test';
        $res = array();
        foreach ($keys as $key) {
            $res[] = $this->createAdapter($key);
        }
        return $res;
    }
    
    public function getAdaptersDir()
    {
        return TaxiEnv::$DIR_ROOT . '/adapters';
    }
    
    public function getAdaptersKeys()
    {
        return array_keys($this->_adaptersConfig);
    }
}
