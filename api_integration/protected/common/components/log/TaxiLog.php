<?php


class TaxiLog extends stdClass implements ITaxiLog
{
    
    const LEVEL_INFO = 'INFO';
    
    const LEVEL_WARNING = 'WARNING';
    
    const LEVEL_ERROR = 'ERROR';
    
    public $fileName = '';
    
    protected $rotationFileName = '';
    
    public $enabled = true;
    
    public $lineLengthLimit = 8192;
    
    private $_sender;
    
    public $_allowConsoleLogging = true;
    
    private $_fromClass = 'Common';
    
    public $maxFileSize = 10048576;
    
    private $_catcher;
    
    private $_console;
    
    private $_logSummary;
    
    private $_hotConsole;
    
    
    public function __construct($sender = null, $fromClass = null)
    {
        $this->_fromClass = $fromClass;
        $this->_sender = $sender;
        if (get_class($this) == "TaxiLog") {
            $this->_logSummary = new TaxiLogSummary($this);
            $this->_hotConsole = new TaxiLogSummaryConsole($this);
            $this->_catcher = new TaxiLogCatcher($this);
            $this->_console = new TaxiLogConsole($this);
        }
    }
    
    public function checkAndRotate()
    {
        $filePath = $this->getFilePath();
        if (!is_file($filePath)) {
            $file = fopen($filePath, 'w');
            if (!$file || !fclose($file)) {
                                $this->enabled = false;
                return false;
            }
        } else {
            if (filesize($filePath) > $this->maxFileSize && is_file($filePath)) {
                $this->rotateFiles();
            }
        }
        return true;
    }
    
    private function isConsoleLogEnabled()
    {
        return TaxiEnv::$PRINT_LOG_CONSOLE_LOG;
    }
    
    
    public function getLogsDir()
    {
        return TaxiEnv::$DIR_ROOT . '/runtime';
    }
    
    public function getFilePath()
    {
        if (empty($this->fileName)) {
            if (empty($this->_fromClass)) {
                $this->_fromClass = get_class($this->_sender);
            }
            $this->fileName = $this->_fromClass . '_log.log';
            $this->rotationFileName = $this->_fromClass . '_log.1.log';
        }
        return $this->getLogsDir() . '/' . $this->fileName;
    }
    
    public function getRotationFilePath()
    {
        if ($this->rotationFileName) {
            return $this->getLogsDir() . '/' . $this->rotationFileName;
        } else {
            return str_replace('.', '.1.', $this->getFilePath());
        }
    }
    
    public function getSenderObject()
    {
        return $this->_sender;
    }
    
    public function setSenderObject($value)
    {
        $this->_sender = $value;
    }
    
    public function getFromClass()
    {
        return $this->_fromClass;
    }
    
    public function setFromClass($fromClass)
    {
        $this->_fromClass = $fromClass;
    }
    
    
    public function clear()
    {
        $mainFilePath = $this->getFilePath();
        $rotationFilPath = $this->getRotationFilePath();
        $all = array($rotationFilPath, $mainFilePath);
        foreach ($all as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
    
    public function rotateFiles()
    {
        $toFile = $this->getRotationFilePath();
        $toFile = $this->getLogsDir() . '/'. basename($toFile);
        $fromFile = $this->getFilePath();
        if (is_file($toFile)) {
            unlink($toFile);
        }
        if (is_file($fromFile)) {
            return rename($fromFile, $toFile);
        }
    }
    
    protected function writeLine($line)
    {
        if (!$this->enabled) {
            return true;
        }
        $path = $this->getFilePath();
        touch($path);
        $file = fopen($path, 'a+');
        if ($file) {
            fwrite($file, $line);
            fclose($file);
            return $this->checkAndRotate();
        } else {
            throw new TaxiException("Не удалось открыть файл лога: " . $this->getFilePath());
        }
    }
    
    public function beforeWrite($logInfo)
    {
    }
    
    public function writeInfo($info)
    {
        $this->beforeWrite($info);
        return $this->writeLine($info->createLine($this->lineLengthLimit));
    }
    
    public function write($message, $senderObject = null,
            $level = self::LEVEL_INFO, $weight = 0)
    {
        $info = new TaxiLogInfo();
        $info->level = $level;
        $info->message = $message;
        $info->weight = $weight;
        $info->sender = isset($senderObject) ? $senderObject : $this->getSenderObject();
        return $this->writeInfo($info);
    }
    
    public function error($message, $senderObject = null)
    {
        return $this->write($message, $senderObject, self::LEVEL_ERROR);
    }
    
    public function warning($message, $senderObject = null)
    {
        return $this->write($message, $senderObject, self::LEVEL_WARNING);
    }
    
    public function info($message, $senderObject = null)
    {
        return $this->write($message, $senderObject, self::LEVEL_INFO);
    }
}
