<?php




abstract class TaxiServerBaseLayer extends TaxiComponent implements ITaxiServer
{
    
    protected $_lastAdapter;

    
    protected $_currentAdapter;

    
    protected $_timeout = 2;

    
    protected $_errorCode;

    
    protected $_errorMessage;

    
    protected $_methods;

    
    public $allowRawAnswer = false;

    
    public $allowRemote = true;

    
    protected $_adapters;

    
    protected $_defaultAdapterKey;

    
    private $_secretKey;

    

    
    public function __construct()
    {
        parent::__construct();

        $this->loadConfigs();
        $this->_adapters = new TaxiAdaptersFactory();
        $this->_methods = new TaxiMethods();
    }

    
    public function loadConfigs()
    {
        $config = TaxiEnv::$config->getServerConfig();
        foreach ($config as $property => $value) {
            $this->{$property} = $value;
        }
    }

    
    public function setSecretKey($value)
    {
        $this->_secretKey = $value;
    }

    
    public function getSecretKey()
    {
        return $this->_secretKey;
    }

    
    protected function fixParams($commandName, $params)
    {
        return $this->_methods->fixParams($commandName, $params);
    }

    
    public function createSign($commandName, $paramsEncodedString)
    {
        return sha1($commandName . $paramsEncodedString . $this->getSecretKey());
    }

    

    
    public function getLastRawAnswer()
    {
        if ($this->_lastAdapter && $this->_lastAdapter instanceof TaxiAdapter) {
            return $this->_lastAdapter->getRawAnswer();
        }
    }

    
    public function getErrorMessage()
    {
        return TaxiServerErrors::createMessage($this->_errorCode, $this->_errorMessage);
    }

    
    public function getDefaultAdapterKey()
    {
        return $this->_defaultAdapterKey;
    }

}
