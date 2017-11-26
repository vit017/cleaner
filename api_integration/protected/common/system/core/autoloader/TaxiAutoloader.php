<?php


function _TaxiAutoloader_InitFunction()
{
    $path = dirname(__FILE__) . '/TaxiSimpleAutoloader.php';
    $class = 'TaxiSimpleAutoloader';
    if (!class_exists($class) && file_exists($path)) {
        include_once $path;
    }
}
_TaxiAutoloader_InitFunction();

class TaxiAutoloader extends TaxiSimpleAutoloader
{
    
    public $scanMap = array();
    
    public $enableAutomaticUpdate = true;
    
    public $enableTracking = false;
    
    private $_track = array(
        'CFileHelper' => 'CFileHelper',
        'TaxiAutoloaderScanner' => 'TaxiAutoloaderScanner',
        'TaxiSimpleAutoloader' => 'TaxiSimpleAutoloader',
        'TaxiAutoloader' => 'TaxiAutoloader',
    );
    
    
    public function isForceUpdateModeEnabled()
    {
        return isset($_GET['TaxiAutoloader_ForceUpdateModeEnabled']) && $_GET['TaxiAutoloader_ForceUpdateModeEnabled'];
    }
    
    
    public function getScanner()
    {
        self::safeIncludeOnce('TaxiAutoloaderScanner', dirname(__FILE__) . '/TaxiAutoloaderScanner.php');
        self::safeIncludeOnce('CFileHelper', TaxiEnv::$DIR_PROTECTED . '/vendors/yii/CFileHelper.php');
        $scanner = new TaxiAutoloaderScanner();
        return $scanner;
    }
    
    public function getSourceScanDirectories()
    {
        return array_merge(
                array($this->baseDir), $this->scanMap
        );
    }
    
    public function getAutomaticMapPath()
    {
        return TaxiEnv::$DIR_RUNTIME . '/cache/TaxiAutoloader_automatic_map.php';
    }
    
    public function getTrackingFilePath()
    {
        return TaxiEnv::$DIR_RUNTIME . '/TaxiAutoloader_track.dat';
    }
    
    
    public function afterIncludeClass($class, $path)
    {
        if ($this->enableTracking) {
            $this->track($class, $path);
        }
    }
    
    
    public function __destruct()
    {
        if ($this->enableTracking) {
            $this->saveTrack();
        }
    }
    
    protected function track($class, $path)
    {
        $this->_track[$class] = $path;
    }
    
    protected function saveTrack()
    {
        $path = $this->getTrackingFilePath();
        $oldTrack = array();
        if (file_exists($path)) {
            @$oldTrack = unserialize(file_get_contents($path));
            if (!is_array($oldTrack)) {
                $oldTrack = array();
            }
        }
        $newTrack = array_merge($oldTrack, $this->_track);
        file_put_contents($path, serialize($newTrack));
    }
    
    public static function safeIncludeOnce($class, $path)
    {
        if (!class_exists($class) && file_exists($path)) {
            include_once $path;
        }
    }
    
    public function updateAutomaticMap()
    {
        $scanner = $this->getScanner();
        $scanner->updateMap($this->getSourceScanDirectories(), $this->getAutomaticMapPath());
        $this->init();
    }
    
    protected function internalLoadMap($path)
    {
        if (!is_file($path)) {
            return false;
        }
        $map = require $path;
        if (is_array($map)) {
            $this->_realMap = array_merge($this->_realMap, $map);
            return true;
        }
    }
    
    public function tryLoadAutomaticMap()
    {
        $path = $this->getAutomaticMapPath();
        if ($this->isForceUpdateModeEnabled() || !is_file($path)) {
            $this->updateAutomaticMap();
        }
        $success = $this->internalLoadMap($path);
        if (!$success && is_file($path)) {
        }
        return $success;
    }
    
    public function init()
    {
        $this->_realMap = array();
        $this->tryLoadAutomaticMap();
        foreach ($this->map as $className => $filePath) {
            if (is_integer($className) && preg_match('/[\\/]([^\\/]+)\.php$/', $filePath, $m)) {
                $className = $m[1];
            }
            $real = $this->processAliases($filePath);
            $this->_realMap[$className] = $real;
        }
        return $this->_realMap;
    }
    
    public function includeClass($class, $deepLevel = 0)
    {
        $included = false;
        if (isset($this->_realMap[$class])) {
            $path = $this->_realMap[$class];
            if (is_file($path)) {
                include $path;
                $this->afterIncludeClass($class, $path);
                $included = true;
            }
        }
        if ($included) {
            return true;
        } elseif ($this->enableAutomaticUpdate && $deepLevel < 1) {
            $this->updateAutomaticMap();
            trigger_error('Попытка автоподгрузки класса ' . $class . ' закончилась неудачей, карта подгрузки обновлена', E_USER_WARNING);
            return self::includeClass($class, $deepLevel + 1);
        }
        return false;
    }
}
