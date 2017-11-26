<?php




class TaxiFilesHelper
{
    
    
    public $filter;

    
    private $_internalFilter;

    public function __construct()
    {
        $this->filter = new TaxiFilesFilter();
        $this->_internalFilter = new TaxiFilesFilter();
    }

    
    public function makeDirectory($dir, $newMode = null)
    {
        if (!is_dir($dir)) {
            CFileHelper::mkdir($dir, array(), true);
        }
        if (!is_dir($dir)) {
            throw new TaxiException("Не удалось создать вложенные директории - {$dir}");
        } elseif ($newMode) {
            $this->chmodeRecurcive($dir, $newMode);
        }
    }

    
    public function findDirsAndFiles($dirOrFile, $filter = null)
    {
        $this->setCurrent($filter);
        $list = $this->_findDirsAndFiles($dirOrFile);
        $list = $this->_internalFilter->filterList($list);

        return $list;
    }

    
    private function getCFileHelperOptions()
    {
        $res = array();
        if ($this->_internalFilter) {
            if ($this->_internalFilter->level) {
                $res['level'] = $this->_internalFilter->level;
            }
        }
        return $res;
    }

    
    private function _findDirsAndFiles($dirOrFile)
    {
        if (is_file($dirOrFile)) {
            return array($dirOrFile);
        } elseif (is_dir($dirOrFile)) {
            $dirs = CFileHelper::findDirs($dirOrFile, $this->getCFileHelperOptions());
            $files = CFileHelper::findFiles($dirOrFile, $this->getCFileHelperOptions());
            return array_merge($dirs, $files);
        } else {
            return array();
        }
    }

    
    public function chmodeRecurcive($dirOrFile, $newMode = 0777)
    {
        $all = $this->findDirsAndFiles($dirOrFile);
        foreach ($all as $path) {
            if (!chmod($path, $newMode)) {
                throw new TaxiException("Не удалось сменить права на {$newMode} у пути {$dirOrFile}");
            }
        }
    }

    
    public function removeFiles($paths)
    {
        foreach ($paths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
        foreach ($paths as $path) {
            if (is_file($path)) {
                throw new Exception("Не удалось удалить файл {$path}");
            }
        }
    }

    
    protected function verifyDirEmpty($dir)
    {
        $any = $this->findDirsAndFiles($dir, array());

        if (!empty($any)) {
            var_dump($any);
            throw new Exception("Не удалось удалить все папки директории {$dir}");
        }
        return true;
    }

    
    public function removeDirRecurcive($dir, $removeSelf = true,
            $firstRun = true)
    {
        if (file_exists($dir) && is_dir($dir)) {
            $dirHandle = opendir($dir);
            if (!$dirHandle) {
                return false;
            }
            while (false !== ($file = readdir($dirHandle))) {
                if ($file != '.' && $file != '..') {                    $tmpPath = $dir . '/' . $file;
                    chmod($tmpPath, 0777);

                    if ($this->_internalFilter->match($tmpPath)) {
                        if (is_dir($tmpPath)) {                              $this->removeDirRecurcive($tmpPath, true, false);
                        } else {
                            if (file_exists($tmpPath)) {
                                                                @unlink($tmpPath);
                            }
                        }
                    }
                }
            }
            if ($this->_internalFilter->match($dir) && is_dir($dir) && $removeSelf) {
                @rmdir($dir);
            }
            closedir($dirHandle);
        } else {
            throw new Exception("Удаляемой папки не существует или это файл!");
        }        
        if ($firstRun) {
            $this->verifyDirEmpty($dir);
        }
    }

    
    public function removeDir($dir = null, $level = -1, $removeSelf = false)
    {
        $files = $this->findFiles($dir);
        $this->removeFiles($files);

        $this->removeDirRecurcive($dir, $removeSelf);
    }

    
    public function copyDirectory($from, $to, $filter = null)
    {
        if (!is_dir($from)) {
            throw new TaxiException("Исходная директория не существуюет или пуста!");
        }
        if ($filter) {
            $copyOptions = $filter->getCFileHelperCallback();
        } else {
            $copyOptions = array();
        }
        CFileHelper::copyDirectory($from, $to, $copyOptions);
        if (!is_dir($to)) {
            throw new TaxiException("Целевая директория пуста!");
        }
    }

    
    private function _findFiles($dir)
    {
        return CFileHelper::findFiles($dir, $this->getCFileHelperOptions());
    }

    
    private function setCurrent($filter)
    {
        if ($filter) {
            $this->_internalFilter = $filter;
        } else {
            $this->_internalFilter = $this->filter;
        }
    }

    
    public function findFiles($dir, $filter = null)
    {
        $res = array();

        if (is_dir($dir)) {
            $this->setCurrent($filter);
            $files = $this->_findFiles($dir);
            $res = $this->_internalFilter->filterList($files);
        }

        return $res;
    }

    
    public function removeBaseDir($baseDir, $from)
    {
        $baseDir = TaxiFilesFilter::replaceSlashes($baseDir);
        $from = TaxiFilesFilter::replaceSlashes($from);

        if (mb_strpos($from, $baseDir) === 0) {
            $from = str_replace($baseDir, '', $from);
        }
        return $from;
    }

}
