<?php




class TaxiLogSummaryConsole extends TaxiLogSummary
{
    
    public $fileName = '___hot_console_tmp_buffer.log';

    
    public $maxFileSize = 1048576;

    
    public $lineLengthLimit = 300;

    
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

    
    public function readBufferClean()
    {
        $path = $this->getFilePath();
        if (is_file($path)) {
            $res = file_get_contents($path);
            $this->clear();
            return $res;
        }
    }

}
