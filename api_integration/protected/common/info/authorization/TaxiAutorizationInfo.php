<?php


class TaxiAutorizationInfo extends TaxiInfo
{
    
    const REQUEST_BROWSER_KEY_KEY = 'browserKey';
    
    const REQUEST_TOKEN_KEY = 'token';
    
    const COOKIE_BROWSER_KEY_KEY = 'api_browser_key';
    
    const COOKIE_TOKEN_KEY = 'api_token';
    
    public $phone;
    
    public $success;
    
    public $isAuthorizedNow;
    
    public $smsMessage;
    
    public $resultCode;
    
    public $text;
    
    public $browserKey;
    
    public $token;
    
    public $rawInfo;
    
   public $inBlackList;
    
    public function __construct()
    {
        if (!isset($_REQUEST[self::REQUEST_BROWSER_KEY_KEY]) &&
                !isset($_REQUEST[self::REQUEST_TOKEN_KEY])) {
            $this->browserKey = isset($_COOKIE[self::COOKIE_BROWSER_KEY_KEY]) ? $_COOKIE[self::COOKIE_BROWSER_KEY_KEY] : null;
            $this->token = isset($_COOKIE[self::COOKIE_TOKEN_KEY]) ? $_COOKIE[self::COOKIE_TOKEN_KEY] : null;
        } else {
            $this->browserKey = $_REQUEST[self::REQUEST_BROWSER_KEY_KEY];
            $this->token = $_REQUEST[self::REQUEST_TOKEN_KEY];

        }
    }
}
