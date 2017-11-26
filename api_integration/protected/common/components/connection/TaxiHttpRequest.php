<?php


class TaxiHttpRequest extends TaxiObject
{
    
    public $baseUrl;
    
    public $getOptions = array();
    
    public $postOptions = array();
    
    public $useEncoding = true;
    
    
    public function getMergedOptions()
    {
        return array_merge((array) $this->getOptions, (array) $this->postOptions);
    }
    
    public function getPostOptions()
    {
        return $this->postOptions;
    }
    
    public function getGetOptions()
    {
        return $this->getOptions;
    }
    
    
    public function createGetOnlyUrl()
    {
        return self::createGetUrl($this->baseUrl, $this->getMergedOptions());
    }
    
    public function createPostUrlOnly()
    {
        return $this->baseUrl;
    }
    
    public static function createSimpleGetUrl($baseUrl, $getOptions)
    {
        foreach ($getOptions as $key => $value) {
            $getOptions[$key] = "{$key}={$value}";
        }
        $url = $baseUrl . '?' . implode('&', $getOptions);
        return $url;
    }
    
    public static function createGetUrl($baseUrl, $getOptions)
    {
        foreach ($getOptions as $key => $value) {
            $value = urlencode($value);
            $getOptions[$key] = "{$key}={$value}";
        }
        $url = $baseUrl . '?' . implode('&', $getOptions);
        return $url;
    }
}
