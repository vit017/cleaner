<?php


class TaxiAutoloaderScanner extends stdClass
{
    
    private $_map = array();
    
    public function updateMap($fromDirectories, $toPath)
    {
        $this->_map = array();
        foreach ($fromDirectories as $basePath) {
            $this->scanAutoloaderMap($basePath);
        }
        return $this->writeMap($toPath);
    }
    
    private function writeMap($toPath)
    {
        $body = '';
        foreach ($this->_map as $class => $filePath){
            $body .= "   '{$class}' => '{$filePath}',\n";
        }
        $content = <<<EOF
<?php
/*
 * Auto generated automatic loader PHP map
 * ! Manual editing can not have any effect
 * 
 * Автоматически генерируемый файл для автоподгрузчика
 * ! Ручные модификации могут быть бесполезны
 */
return array(
{$body}
);
EOF;
        return file_put_contents($toPath, $content);
    }
    
    private function scanFiles($basePath)
    {
        $files = CFileHelper::findFiles($basePath, array(
                    'fileTypes' => array('php'),
                    'level' => -1,
        ));
        foreach ($files as $key => $filePath) {
            $files[$key] = str_replace('\\', '/', $filePath);
        }
        return $files;
    }
    
    private function extractClass($filePath)
    {
        $class = basename($filePath, '.php');
        if (preg_match('/^[A-Z]{1}/', $class)) {
            return $class;
        }
    }
    
    private function pushToMap($class, $filePath)
    {
        if (key_exists($class, $this->_map) && $this->_map[$class] != $filePath) {
            throw new Exception(
            'Дублированное имя файла/класса! ' . $filePath .
            'Класс уже есть по пути: ' . $this->_map[$class]
            );
        }
        $this->_map[$class] = $filePath;
    }
    
    private function scanAutoloaderMap($basePath)
    {
        if (!is_dir($basePath)) {
            return array();
        }
        foreach ($this->scanFiles($basePath) as $filePath) {
            $class = $this->extractClass($filePath);
            if ($class) {
                $this->pushToMap($class, $filePath);
            }
        }
        return $this->_map;
    }
    
    public function scanProtectedClasses()
    {
        return $this->scanAutoloaderMap(TaxiEnv::$DIR_PROTECTED);
    }
    
    public function scanAllClasses()
    {
        return $this->scanAutoloaderMap(TaxiEnv::$DIR_ROOT);
    }
}
