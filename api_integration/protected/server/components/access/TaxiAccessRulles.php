<?php




class TaxiAccessRulles extends TaxiAccessRulle
{
    private $_rulles = array();

    
    public function __construct($adapter = null)
    {
        if (!$adapter) {
            throw new TaxiException("Необходимо задать адаптер для коллекции правил доступа " . __CLASS__);
        }
        parent::__construct($adapter);
    }

    
    private function internalAdd($commandName, $rulle)
    {
        $this->_rulles[$commandName][] = $rulle;
        if (!$rulle->adapter) {
            $rulle->adapter = $this->adapter;
        }
    }

    
    public function add($commandName, $rulles)
    {
        foreach ($rulles as $rulle) {
            $this->internalAdd($commandName, $rulle);
        }
    }

    
    public function clear()
    {
        $this->_rulles = array();
    }

    
    public function afterFailCheckingAccess($commandName, $params)
    {
        if ($this->adapter) {
            $this->adapter->log->error("Access deny to our API method {$commandName} because: \n" . $this->getLastErrors()
                    . "\n - with params: \n" . CVarDumper::dumpAsString($params)
            );
        }
    }

    
    public function checkAccess($commandName, $params)
    {
        $success = true;
        if (!empty($this->_rulles[$commandName])) {
            foreach ($this->_rulles[$commandName] as $rulle) {
                if (!$rulle->checkAccess($commandName, $params)) {
                    $this->_errors[] = $rulle->getLastErrors();
                    $success = false;
                    $this->afterFailCheckingAccess($commandName, $params);
                }
            }
        }
        return $success;
    }

    
    public function getLastErrors()
    {
        return implode('; ', $this->_errors);
    }

}
