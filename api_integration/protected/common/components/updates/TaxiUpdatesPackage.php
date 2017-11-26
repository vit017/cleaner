<?php




class TaxiUpdatesPackage extends TaxiObject
{
    
    private $_lastArchivePath = null;

    

    

    
    public function getArchivesDir()
    {
        return dirname(TaxiEnv::$DIR_ROOT) . '/apii_updates';
    }

    
    protected function getFilesHelper()
    {
        $helper = new TaxiFilesHelper();
        return $helper;
    }

    

    
    public function updateArchivesDir()
    {
        if (!is_dir($dir = $this->getArchivesDir())) {
            $this->getFilesHelper()->makeDirectory($dir);
            if (!is_dir($dir)) {
                throw new TaxiException("Не удалось создать директорию для загрузки архивов с обновлениями");
            }
        }
        return true;
    }

    
    private function createPath($remoteZipUrl, $newZipName)
    {
        $dir = $this->getArchivesDir();
        $name = md5($remoteZipUrl) . '.zip';
        if (preg_match('/([^\\/]+\.zip)$/Ui', $remoteZipUrl, $m)) {
            $name = $m[1];
        }
        if ($newZipName) {
            return $dir . '/' . $newZipName;
        } else {
            return $dir . '/' . $name;
        }
    }

    
    public static function downloadFileByCurl($remoteFileUrl, $path)
    {
        $curl = curl_init($remoteFileUrl);
        $fp = fopen($path, 'w');
        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_exec($curl);
        curl_close($curl);
        fclose($fp);
        return is_file($path);
    }

    
    public function removeOldArchives()
    {
        $dir = $this->getArchivesDir();
        $helper = new TaxiFilesHelper();
        if (is_dir($dir)) {
            $helper->removeDir($dir);
        }
    }

    
    public function downloadZip($remoteZipUrl, $newZipName = null)
    {
        $this->removeOldArchives();
        $this->updateArchivesDir();
        $path = $this->createPath($remoteZipUrl, $newZipName);

        if ($this->downloadFileByCurl($remoteZipUrl, $path)) {
            $this->_lastArchivePath = $path;
            return true;
        } else {
            return false;
        }
    }

    
    public function clearAll()
    {
        $paths = $this->findAllArchivesPaths();
        $this->getFilesHelper()->removeFiles($paths);

        $paths = $this->findAllArchivesPaths();

        return empty($paths);
    }

    
    private function findAllArchivesPaths()
    {
        $helper = $this->getFilesHelper();
        $helper->filter->include->baseNamePregs[] = '/\.zip$/i';
        $helper->filter->level = 0;
        $paths = $helper->findFiles($this->getArchivesDir());

        return $paths;
    }

    
    public function getLastestPath()
    {
        if (isset($this->_lastArchivePath)) {
            return $this->_lastArchivePath;
        } else {
            $paths = $this->findAllArchivesPaths();
            if (count($paths) > 0) {
                return reset($paths);
            }
        }
    }

}
