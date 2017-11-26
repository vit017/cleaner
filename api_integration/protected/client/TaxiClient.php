<?php




class TaxiClient extends TaxiComponent implements ITaxiClient
{

    
    public static $afterCommandEventsMap = array(
        'createOrder'  => 'raise_afterCreateOrder',
        'addReview'    => 'raise_afterAddReview',
        'getOrderInfo' => 'raise_afterGetOrderInfo',
    );

    
    public $useLocalServer = true;

    
    public $needWriteToBitrix = true;

    
    public $salt = 'syeiRusk2lx8spksj';

    
    protected $_connection;

    
    protected $_authorization;

    
    private $_adapterKey;

    
    private $_adaptersMap;

    
    private $_configArray;

    
    private $_localServer;

    
    private static $_rawResultProperties = array(
        'status',
        'result',
        'errorMessage',
        'serverTime',
        'errorCode',
        'rawAnswer',
    );

    
    public function __construct($connection = null)
    {
        if (!$connection) {
            $connection = new TaxiClientConnection();
        }
        $this->_connection = $connection;
        $this->_log = new TaxiLog($this);
        $this->_authorization = new TaxiClientAuthorization($this->salt . $_SERVER['SERVER_NAME']);

        $this->loadConfigs();
    }

    
    public function loadConfigs()
    {
        $this->_configArray = TaxiEnv::$config->getClientConfig();
        $this->_adaptersMap = $this->_configArray['adaptersMap'];
        $this->setAdapterKey($this->_configArray['defaultAdapterKey']);

        TaxiConfig::applyOptionsTo($this->_configArray['options'], $this);
    }

    


    


    

    
    public function setHost($value)
    {
        $this->_connection->host = $value;
    }

    
    public function setSecretKey($value)
    {
        $this->_connection->secretKey = $value;
    }

    
    public function getHost()
    {
        return $this->_connection->host;
    }

    
    public function getSecretKey()
    {
        return $this->_connection->secretKey;
    }

    
    public function setAdapterKey($adapterKey)
    {
        $this->_adapterKey = $adapterKey;
    }

    
    public function getAdapterKey()
    {
        $raw = null;
        if (isset($_GET['adapterKey'])) {
            $raw = $_GET['adapterKey'];
            if (!preg_match('/^\S+$/', $raw)) {
                $raw = null;
            }
        }
        $cutomAdapterKey = $raw;
        if ($cutomAdapterKey) {
            return $cutomAdapterKey;
        } else {
            return $this->_adapterKey;
        }
    }

    
    private function parseRawResultTo($toResult, $rawResult)
    {
        if (is_array($rawResult)) {
            foreach (self::$_rawResultProperties as $property) {
                if (key_exists($property, $rawResult)) {
                    $toResult->{$property} = $rawResult[$property];
                }
            }
            $toResult = $this->clearNullValues($toResult);
        } else {
            $toResult->status = 0;
        }
    }

    
    public function clearNullValues($toResult)
    {
        if (is_object($toResult->result)) {
            foreach ($toResult->result as $property => $value) {
                if (($value === null) ) {
                    unset($toResult->result->{$property});
                }
            }
        }

        return $toResult;
    }

    
    private function createResult($commandName, $rawResult)
    {
        $result = new TaxiMethodResult();
        $result->commandName = $commandName;
        $result->updateClientTime();

        $this->parseRawResultTo($result, $rawResult);

        return $result;
    }

    
    private function findServerKey($adapterKey)
    {
        if (key_exists($adapterKey, $this->_adaptersMap)) {
            return $this->_adaptersMap[$adapterKey];
        } elseif (key_exists('local', $this->_configArray['servers'])) {
            return 'local';
        } else {
            throw new TaxiClientException("Не задан или не найден сервер для этого адаптера: {$adapterKey}");
        }
    }

    
    private function initServerConnection($serverKey)
    {
        $this->_connection->host = $this->_configArray['servers'][$serverKey]['host'];
        $this->_connection->secretKey = $this->_configArray['servers'][$serverKey]['secretKey'];
    }

    
    public function getLocalServer()
    {
        if (!$this->_localServer) {
            $this->_localServer = new TaxiServer();
        }
        return $this->_localServer;
    }

    
    public function sendToLocalServer($commandName, $params, $adapterKey)
    {
        $server = $this->getLocalServer();
        $class = get_class($server);
        return $server->executeCommandEmulation($commandName, $params, $adapterKey);
    }

    
    private function isLocal($adapterKey, $commandName)
    {
        $server = $this->getLocalServer();
        $adapter = $server->adapters->createAdapter($adapterKey);
        if ($adapter && $adapter->isLocalMethod($commandName)) {
            return true;
        } else {
            return false;
        }
    }

    
    private function existsServerPackage()
    {
        if (is_dir(TaxiEnv::$DIR_PROTECTED . '/server') && TaxiEnv::$autoloader->findClassPath('TaxiServer')) {
            return true;
        } else {
            return false;
        }
    }

    
    private function runServerCommand($commandName, $params)
    {
        $adapterKey = $this->getAdapterKey();
        $serverKey = $this->findServerKey($adapterKey);

        $rawResult = null;
        
        if ($this->existsServerPackage()) {
            if ($serverKey === 'local' && $this->useLocalServer || $this->isLocal($adapterKey, $commandName)) {
                $rawResult = $this->sendToLocalServer($commandName, $params, $adapterKey);
                return $rawResult;
            }
        }
        $this->initServerConnection($serverKey);
        $rawResult = $this->_connection->send($commandName, $params, $adapterKey);

        return $rawResult;
    }

    public function raise_afterCreateOrder($baseEvent)
    {
        $event = new TaxiClientAfterCreateOrderEvent($this);
        $event->syncronizeWith($baseEvent);
        $eventListener = new TaxiBitrixCreateOrderEventListener();
        $eventListener->onEvent($event);
    }

    public function raise_afterAddReview($baseEvent)
    {
        $event = new TaxiClientAfterCreateOrderEvent($this);
        $event->syncronizeWith($baseEvent);
        $eventListener = new TaxiBitrixAddReviewEventListener();
        $eventListener->onEvent($event);
    }

    public function raise_afterGetOrderInfo($baseEvent)
    {
        $event = new TaxiClientAfterGetOrderInfoEvent($this);
        $event->syncronizeWith($baseEvent);
        $eventListener = new TaxiBitrixGetOrderInfoEventListener();
        $eventListener->onEvent($event);
    }

    
    public function raiseAfterCommandEvents($commandName, $params, $result)
    {
        $eventsMap = self::$afterCommandEventsMap;
        $baseEvent = new TaxiClientAfterCommandEvent($this);

        $baseEvent->commandName = $commandName;
        $baseEvent->commandParams = $params;
        $baseEvent->result = $result;

        foreach ($eventsMap as $mapCommandName => $raiseMethodName) {
            if ($commandName === $mapCommandName) {
                call_user_func(array($this, $raiseMethodName), $baseEvent);
            }
        }
    }

    
    public function executeServerCommand($commandName, $params = array())
    {
        $rawResult = $this->runServerCommand($commandName, $params);
        $result = $this->createResult($commandName, $rawResult);

        if ($result->isSuccessful()) {
            $result->afterSuccessExecute($this);
            $this->raiseAfterCommandEvents($commandName, $params, $result);
        } else {
            $result->afterFailExecute($this);
        }
        return $result;
    }

    
    public function executeOnServer($command)
    {
        return $this->executeServerCommand($command->getCommandName(), $command->getParams());
    }

    
    public function executeOnClient($query)
    {
        $this->clientSide->execute($query);
    }

    
    private function echoResult($result)
    {
        if (!$result->hasErrors()) {
            
        }
        header('Access-Control-Allow-Origin: *');
        echo json_encode($result);
    }

    
    private function createQuery()
    {
        $query = new TaxiQuery();

        $query->setParams($_POST);

        if (isset($_GET['clientCommand'])) {
            $command = $_GET['clientCommand'];
        } elseif (isset($_GET['command'])) {
            $command = $_GET['command'];
        }
        $query->setCommandName($command);

        return $query;
    }

    
    private function internalProcessRequest()
    {
        $result = 'Error in GET options!';
        if ($query = $this->createQuery()) {
            if (!isset($_GET['clientCommand'])) {
                $result = $this->executeOnServer($query);
            } else {
                $result = $this->executeOnClient($query);
            }
        }

        $this->echoResult($result);
    }

    
    public function processRequest()
    {
        try {
            $this->internalProcessRequest();
        } catch (Exception $exception) {
            TaxiExceptionHandler::handle($exception);
        }
    }

}
