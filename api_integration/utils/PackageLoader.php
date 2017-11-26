<?php
require_once dirname(__FILE__) . '/PackageJsFilter.php';

class PackageLoader
{
    
    public $COMPILE_FLAG = 'COMPILE_ASSETS';
    
    public $minifyServerUrl = 'http://catalog-taxi.ru/include/minify-master/min/';
    
    public $useLocalMinifyServerStrongly = true;
    
    public $useGetMinMethod = true;
    
    public $useForceCompile = false;
    
    public $allowDevMode = true;
    
    public $jsMap = array();
    
    public $minifyMasterPath = '/include/minify-master';
    
    public $jsFilter;
    
    public function __construct()
    {
        $this->jsFilter = new PackageJsFilter();
        $this->loadConfigs();
    }
    
    public function loadConfigs()
    {
        $path = $this->getDocumentRootPath() . '/api_integration/config/PackageLoader.php';
        if (is_file($path)) {
            $config = require $path;
            if (is_array($config)) {
                foreach ($config as $property => $value) {
                    $this->{$property} = $value;
                }
            }
        }
    }
    
    
    public function isDevMode()
    {
        return $this->allowDevMode &&
                preg_match('/\.dev/', $_SERVER['HTTP_HOST']) &&
                !isset($_GET[$this->COMPILE_FLAG]) && !$this->useForceCompile;
    }
    
    public function isLocalEnvironment()
    {
        return preg_match('/\.dev/', $_SERVER['HTTP_HOST']) || $_SERVER['SERVER_ADDR'] === '127.0.0.1';
    }
    
    public function useLocalMinifyServer()
    {
        return $this->useLocalMinifyServerStrongly || $this->isLocalEnvironment();
    }
    
    
    private function getBaseDir()
    {
        return $_SERVER['DOCUMENT_ROOT'] . $this->dir;
    }
    
    private function getDocumentRootPath()
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }
    
    private function getRelationJsMap()
    {
        $res = array();
        foreach ($this->jsMap as $fileName) {
            if (strpos($fileName, '<SITE_TEMPLATE_PATH>') !== false && defined('SITE_TEMPLATE_PATH')) {
                $fileName = str_replace('<SITE_TEMPLATE_PATH>', SITE_TEMPLATE_PATH, $fileName);
            }
            $fileName = str_replace($_SERVER['DOCUMENT_ROOT'], '', $fileName);
            $res[] = $fileName;
        }
        return $res;
    }
    
    public function getMinifyServerUrl()
    {
        if (empty($this->minifyServerUrl) || $this->useLocalMinifyServer()) {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . $this->minifyMasterPath . '/min/';
        } else {
            $url = $this->minifyServerUrl;
        }
        return $url;
    }
    
    
    private function createLoadingLines($scriptFileNames)
    {
        $baseDir = $this->getBaseDir();
        $lines = array();
        foreach ($scriptFileNames as $fileName) {
            $line = '<script src="' . $fileName . '" type="text/javascript"></script>';
            $lines[] = $line;
        }
        return $lines;
    }
    
    private function createOutFileName()
    {
        return $this->getDocumentRootPath() . '/api_integration/assets_min/' . 'package_' . sha1(implode(' ', $this->jsMap)) . '.min.js';
    }
    
    private function convertToRelative($fullFilePath)
    {
        $rootPath = $this->getDocumentRootPath();
        $filteredRootPath = str_replace('\\', '/', $rootPath);
        $res = str_replace($filteredRootPath, '', $fullFilePath);
        return $res;
    }
    
    private function checkMinifyLib()
    {
        return file_exist(
                $this->minifyMasterPath . '/min/lib/Minify/Loader.php'
        );
    }
    
    private function loadRawMinLib()
    {
        if (!$this->checkMinifyLib()) {
            throw new Exception("Minify master lib not found! Can't load Java Script files!");
        }
        define('MINIFY_MIN_DIR', $_SERVER['DOCUMENT_ROOT'] . '/' . $this->minifyMasterPath . '/min');
        require_once MINIFY_MIN_DIR . '/config.php';
        require MINIFY_MIN_DIR . '/config.php';
        require "$min_libPath/Minify/Loader.php";
        Minify_Loader::register();
        $sources = $this->getRelationJsMap();
        $code = Minify::combine($sources);
        return $code;
    }
    
    public function tryCompilePackage($toFile = false)
    {
        if (!$toFile) {
            $toFile = $this->createOutFileName();
        }
        $toFile = str_replace('\\', '/', $toFile);
        $relaviteFile = $this->convertToRelative($toFile);
        if (is_file($toFile) && !isset($_GET[$this->COMPILE_FLAG]) && !$this->isDevMode() && !$this->useForceCompile) {
            return $relaviteFile;
        }
        if ($this->useGetMinMethod) {
            $query = $this->createMinifyGetQuery($this->getRelationJsMap());
            $code = file_get_contents($query);
            if (empty($code)) {
                throw new Exception('Error in minify package loader! - Empty code returned from minify server!');
            }
        } else {
            $code = $this->loadRawMinLib();
        }
        if ($code) {
            $code = $this->jsFilter->applyFilters($toFile, $code);
            if (file_put_contents($toFile, $code)) {
                return $relaviteFile;
            }
        }
    }
    
    private function createMinifyGetQuery($files)
    {
        $getParam = implode(',', $files);
        $path = $this->getMinifyServerUrl() . '?f=' . $getParam;
        return $path;
    }
    
    public function createLoaderTags()
    {
                if ($this->isDevMode()) {
            return implode("\n", $this->createLoadingLines($this->getRelationJsMap()));
        } else {
            if (isset($_GET[$this->COMPILE_FLAG])) {
                $this->useForceCompile = true;
            }
            $packageFileName = $this->tryCompilePackage();
            if ($packageFileName) {
                return implode("\n", $this->createLoadingLines(array(
                            $packageFileName
                )));
            }
        }
    }
}
