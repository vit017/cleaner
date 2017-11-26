<?php




class TaxiHttpConnection extends TaxiConnection implements ITaxiHttpConnection
{
    
    public $timeout = 5;

    
    public $enableHeaders = false;

    
    protected $_lastRes;

    
    private $_adapter;

    
    private $_lastUrl;

    
    private $_lastResultString;

    
    private $_lastPostOptions;

    

    

    
    public function getCookiesPath()
    {
        return TaxiEnv::$DIR_RUNTIME . '/cookie/' . get_class($this) . '_cookies.txt';
    }

    
    public function getCookieJarPath()
    {
        return TaxiEnv::$DIR_RUNTIME . '/cookie/' . get_class($this) . '_cookies.txt';
    }

    

    
    public function getAdapter()
    {
        return $this->_adapter;
    }

    
    public function setAdapter($adapter)
    {
        $this->_adapter = $adapter;
    }

    
    public function afterQuery($rawResult)
    {
        $this->_lastRes = $rawResult;
        if ($this->_adapter) {
            $this->_adapter->setRawAnswer($rawResult);
        }
        if ($rawResult === Null || $rawResult === false) {
            $this->log->error("No answer after query from URL {$this->getLastUrl()}");
        } elseif (empty($rawResult)) {
            $this->log->warning("Empty answer after query from URL {$this->getLastUrl()}");
        }
        return true;
    }

    
    public function beforeQuery($url, $postOptions)
    {
        if ($postOptions) {
            $this->log->info("Sending POST query to {$url} with options \n" . CVarDumper::dumpAsString($postOptions));
        } else {
            $this->log->info("Sending GET query to {$url}");
        }
        $this->_lastUrl = $url;
        $this->_lastPostOptions = $postOptions;
        return true;
    }

    
    public function executeGetQuery($url)
    {
        return $this->executePostQuery($url, false);
    }

    public function beforeJsonDecode($str)
    {
        return $str;
    }

    public function touch($path)
    {
        if (!is_file($path)) {
            $f = fopen($path, 'w');
            fclose($f);
        }
    }

    
    public function executePostQuery($url, $postOptions)
    {
        if (!$this->beforeQuery($url, $postOptions)) {
            return false;
        }

        $ch = curl_init($url);
        if ($postOptions) {
            $paramsString = http_build_query($postOptions);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramsString);
        }
        $cookieJarPath = $this->getCookieJarPath();
        $cookiePath = $this->getCookiesPath();

        $this->touch($cookieJarPath);
        $this->touch($cookiePath);

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:25.0) Gecko/20100101 Firefox/25.0 FirePHP/0.7.4');
        curl_setopt($ch, CURLOPT_VERBOSE, 2);         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);         curl_setopt($ch, CURLOPT_HEADER, $this->enableHeaders);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);

        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJarPath);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiePath);


        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $resultString = curl_exec($ch);
        $errorCode = curl_errno($ch);

        curl_close($ch);

        $this->log->info('RAW asnwer with headers: ' . $resultString);

        if ($this->enableHeaders) {
            if (preg_match('/Content-Length:\s+(\d+)\s*(.+)$/ms', $resultString, $m)) {
                $len = trim($m[1]);
                $charset = null;
                if (preg_match('/charset=(\S+)/', $resultString, $m)) {
                    $charset = $m[1];
                }
                $resultString = mb_substr($resultString, mb_strlen($resultString) - $len, $len, $charset);
            }
        }

        if ($errorCode == CURLE_OK || $resultString) {

            $this->_lastResultString = $resultString;
            $resultString = $this->beforeJsonDecode($resultString);
            $result = CJSON::decode($resultString, true);
            if (!$result) {
                $result = $resultString;
            }

            if (is_array($result)) {
                $this->log->info("Raw CURL answer from {$this->lastUrl}: " . $resultString);
                $this->log->info("Decoded answer from: {$this->lastUrl} \n" . CVarDumper::dumpAsString($result));

                $this->afterQuery($result);
                return $result;
            } else {
                $this->log->info("Raw CURL answer from ouwer server:\n {$this->lastUrl}");

                $this->afterQuery($result);
                return $result;
            }
        } else {
            $this->log->error("{$errorCode} BAD CURL answer from {$this->lastUrl} or timeout?: " . $resultString);
            return false;
        }
    }

    

    
    public function getLastUrl()
    {
        return $this->_lastUrl;
    }

    
    public function getLastPostOptions()
    {
        return $this->_lastPostOptions;
    }

    
    public function getLastResultString()
    {
        return $this->_lastResultString;
    }

    
}
