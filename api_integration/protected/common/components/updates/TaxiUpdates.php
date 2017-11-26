<?php




class TaxiUpdates extends TaxiObject implements ITaxiEntryPoint
{
    
    const PASS_TYPE_NONE = 'none';

    
    const PASS_TYPE_NATIVE_KEY = 'native_key';

    
    public $backupDirPrefix = '~before_update_';

    
    public $updateRelativePath = '/api_integration';

    
    public $newTmpUpdatePath = null;

    
    protected $_actionOptions = array(
        'actionPing' => array(
            'messageToDuplicate' => array(
                'allowEmpty' => true,
                'value' => null,
                'filter' => '/.*/',
            ),
        ),
        'actionDownloadZip' => array(
            'zipRemoteUrl' => array(
                'allowEmpty' => false,
                'value' => null,
                'filter' => '/.*/',
            ),
            'newZipName' => array(
                'allowEmpty' => false,
                'value' => null,
                'filter' => '/.*/',
            ),
            'passType' => array(
                'allowEmpty' => true,
                'value' => self::PASS_TYPE_NONE,
                'filter' => '/[A-Za-z0-9]+/',
            ),
        ),
        'actionUpdateFromZip' => array(
        ),
        'actionGetCurrentVersion' => array(
        ),
    );

    

    

    
    public function processRequest()
    {
        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
            $actionName = 'action' . $this->toUpper($command);
            if ($this->isAllowedParamsFor($actionName)) {
                return $this->callAction($actionName);
            } else {
                $this->echoActionResult('Bad params');
            }
        } else {
            $this->echoActionResult($this->getEmptyActionResult());
        }
    }

    
    private function toUpper($name)
    {
        if (preg_match('/^([A-Za-z])(\S+)/', $name, $m)) {
            return strtoupper($m[1]) . $m[2];
        }
    }

    

    
    public function getEmptyActionResult()
    {
        return 'No action found!';
    }

    

    
    private function createRequestParams($actionName)
    {
        $res = $this->_actionOptions[$actionName];
        foreach ($res as $paramName => $set) {
            if (isset($_REQUEST[$paramName])) {
                $res[$paramName]['value'] = $_REQUEST[$paramName];
            }
        }
        return $res;
    }

    
    private function createActionParams($actionName)
    {
        $raw = $this->createRequestParams($actionName);
        $res = array();
        foreach ($raw as $paramName => $set) {
            $res[$paramName] = $set['value'];
        }
        return $res;
    }

    
    private function isAllowedParamsFor($actionName)
    {
        $success = true;
        foreach ($this->createRequestParams($actionName) as $paramName => $set) {
            if ($set['value'] === null && $set['allowEmpty'] === false) {
                return false;
            }
            if (!preg_match($set['filter'], $set['value'])) {
                return false;
            }
        }
        return $success;
    }

    
    private function callAction($actionName)
    {
        $params = $this->createActionParams($actionName);
        $actionResult = call_user_func_array(array($this, $actionName), $params);
        $this->echoActionResult($actionResult);
        return $actionResult;
    }

    
    protected function echoActionResult($result)
    {
        echo json_encode($result);
    }

    

    
    public function actionPing($messageToDuplicate = null)
    {
        if (!$messageToDuplicate) {
            return true;
        } else {
            return $messageToDuplicate . $messageToDuplicate;
        }
    }

    
    public function createBackupsDir($updatePath)
    {
        return dirname($updatePath) . '/' . $this->backupDirPrefix . basename($updatePath);
    }

    
    public function actionDownloadZip($zipRemoteUrl, $newZipName,
            $passType = null)
    {
        $package = new TaxiUpdatesPackage();
        if ($package->downloadZip($zipRemoteUrl, $newZipName)) {
            $zipPath = $package->getLastestPath();

            return $zipPath;
        }
    }

    
    public function createBackup($updatePath, $current = null)
    {
        if (is_dir($updatePath)) {
            $helper = new TaxiFilesHelper();
            if ($current) {
                $to = $this->createBackupsDir($updatePath);
            } else {
                $to = $this->createBackupsDir($current);
            }
            if (is_dir($to)) {
                $helper->removeDir($to, $level = -1, $removeSelf = true);
            }
            $helper->copyDirectory($updatePath, $to);
            if (is_dir($to)) {
                return $to;
            } else {
                throw new TaxiException("Can't create backup target updates dir!");
            }
        }
    }

    
    public function updateFromZip($zipPath, $updatePath, $current = null)
    {
        $this->createBackup($updatePath, $current);

        $filter = new TaxiFilesFilter();
        $filter->removeRelativePath = $updatePath;

        
        $filter->exclude->beginPart[] = '/config/';
        $filter->exclude->pregs[] = '/^\/config$/';

        if (is_dir($updatePath)) {
            $files = new TaxiFilesHelper();
            $files->filter = $filter;
            $files->removeDir($updatePath);
        }

        $zip = new TaxiZip();
        $zip->extractArchive($zipPath, $updatePath, $filter);

        return true;
    }

    
    public function getUpdatesPath()
    {
        if (!$this->newTmpUpdatePath) {
            return TaxiEnv::$DIR_SERVER_ROOT . $this->updateRelativePath;
        } else {
            return $this->newTmpUpdatePath;
        }
    }

    
    public function actionUpdateFromZip()
    {
        $package = new TaxiUpdatesPackage();
        $zipPath = $package->getLastestPath();

        $to = $this->getUpdatesPath();
        $current = TaxiEnv::$DIR_ROOT;
        $to = TaxiFilesFilter::replaceSlashes($to);
        $current = TaxiFilesFilter::replaceSlashes($current);

        if ($to == $current) {
            throw new TaxiException("Нельзя провести перезапись текущего расположения кода, т.к. возможны конфликты! (Запускайте обновление через спец. точку например \www\api_integration_updates_center");
            $helper = new TaxiFilesHelper();
            $to = dirname($to) . '/~new_tmp__' . basename($to);
            $old = dirname($current) . '/~old_tmp__' . basename($current);
            $helper->copyDirectory($current, $to);
            $this->newTmpUpdatePath = $to;
        }

        $this->updateFromZip($zipPath, $to, $current);

        if ($this->newTmpUpdatePath) {
                                                            rename($current, $old);
            rename($this->newTmpUpdatePath, $current);
        }

        return $zipPath;
    }

    
    public function actionGetCurrentVersion()
    {
        $info = new stdClass();

        $info->version = 'undefined';
        $info->build = '0';
        $info->buildHost = '';
        $info->buildTime = '0';

        return $info;
    }

}
