<?php
abstract class GootaxBaseLayer extends TaxiSmsAdapter
{
    protected $_api;

    protected $_cache;

    public function __construct()
    {
        parent::__construct();

        $this->_log = new TaxiLog($this);
        $this->_api = new GootaxApi();
        $this->_api->adapter = $this;
        $this->_cache = new TaxiFileCache('/' . get_class($this) . '.dat');
    }

    public function setIp($value)
    {
        $this->_api->setIp($value);
    }

    public function setPort($value)
    {
        $this->_api->setPort($value);
    }

    public function setKey($value)
    {
        $this->_api->setKey($value);
    }

    public function setTenantid($value)
    {
        $this->_api->setTenantid($value);
    }

    public function setAppid($value)
    {
    $this->_api->setAppid($value);
    }

}
