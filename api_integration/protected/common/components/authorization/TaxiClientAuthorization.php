<?php




class TaxiClientAuthorization extends TaxiComponent
{

    
    public $useIpValidationInToken = false;


    private $_salt;
    public $api_token;

    


    
    public function __construct($salt)
    {
        if (!$salt) {
            throw new TaxiException("Необходимо передать соль при создании объекта авториазации");
        }
        $this->_salt = $salt;
    }

    public function setApiToken($api_token)
    {
        $this->api_token = $api_token;
    }

    public function getApiToken()
    {
        return $this->api_token;
    }

    

    
    protected function afterCheckBadToken($token, $phone, $browserUniqueKey)
    {
        if (!$this->checkBrowserKey($browserUniqueKey)) {
            $this->log->warning("Browser key is bad! {$browserUniqueKey} on phone {$phone}");
        }
        if (!$this->checkToken($token, $phone, $browserUniqueKey)) {
            $goodToken = $this->createCookieToken($phone, $browserUniqueKey);
            $this->log->warning("Token is bad! {$token} on phone {$phone} \n Good token is = {$goodToken}");
        }
        return true;
    }

    
    protected function afterCheckGoodToken($token, $phone, $browserUniqueKey)
    {
        $this->log->info("Success checking token {$token} on phone {$phone}");
        return true;
    }

    

    
    protected function hash($value)
    {
        return sha1($value . $this->_salt);
    }

    
    protected function generateRandom()
    {
        return rand(0, 1000000) . rand(0, 1000000);
    }

    
    private function createRemoteUserIndentity()
    {
        if ($this->useIpValidationInToken) {
            return $this->hash($_SERVER['REMOTE_ADDR'] . $this->_salt);
        } else {
            return $this->hash($this->_salt);
        }
    }

    
    public function createCookieBrowserUniqueKey()
    {
        return $this->hash($this->_salt . $this->generateRandom());
    }

    
    public function createCookieToken($phone, $browserUniqueKey)
    {
        $api_token = $this->hash($this->createRemoteUserIndentity() . $phone . $browserUniqueKey);
        $this->setApiToken($api_token);
        return $this->api_token;
    }

    
    public function checkBrowserKey($browserUniqueKey)
    {
        if (!preg_match('/^[\dabcdef]{40}$/', $browserUniqueKey)) {
            return false;
        } else {
            return true;
        }
    }

    
    public function checkToken($token, $phone, $browserUniqueKey)
    {
        return $token === $this->createCookieToken($phone, $browserUniqueKey);
    }

    
    public function checkAuthorization($token, $phone, $browserUniqueKey)
    {
        if ($token &&
                $browserUniqueKey &&
                $this->checkBrowserKey($browserUniqueKey) &&
                $this->checkToken()
        ) {
            $this->afterCheckGoodToken($token, $phone, $browserUniqueKey);
            return true;
        } else {
            $this->afterCheckBadToken($token, $phone, $browserUniqueKey);
            return false;
        }
    }

    
    public function checkTokenByPhone($phone)
    {
        $info = new TaxiAutorizationInfo();

        $info->browserKey = isset($_COOKIE["browserKey"]) ? $_COOKIE["browserKey"] : null;
        $info->token = isset($_COOKIE["token"]) ? $_COOKIE["token"] : null;

        if ($info->browserKey && $info->token) {
            return $this->checkToken($info->token, $phone, $info->browserKey);
        } else {
            return false;
        }
    }

}
