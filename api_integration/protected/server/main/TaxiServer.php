<?php


class TaxiServer extends TaxiServerCommandsLayer implements ITaxiServer
{
    
    
    private function applyOutputFilter($adapter, $filterData)
    {
        $result = $this->_currentAdapter->applyResultFilters($filterData);
        $adapterClass = get_class($adapter);
        return $filterData->result;
    }
    
    private function callUserFunction($adapter, $methodName, $params)
    {
        $params = $adapter->filterBeforeCall($methodName, $params);
        $result = call_user_func_array(array($adapter, $methodName), $params);
        $filterData = new TaxiFilterData($methodName, $params, $result);
        $filtered = $this->applyOutputFilter($adapter, $filterData);
        return $filtered;
    }
    
    private function callAdapterMethod($adapter, $commandName, $params)
    {
        if (method_exists($adapter, $commandName)) {
            $this->_lastAdapter = $adapter;
            return $this->callUserFunction($adapter, $commandName, $params);
        } else {
            $this->afterMethodNotFound();
            return null;
        }
    }
    
    private function prepareParams($commandName, $paramsPart)
    {
        $params = $this->fixParams($commandName, $paramsPart);
        $params = $this->_currentAdapter->applyFilters($commandName, $params);
        return $params;
    }
    
    public function executeCommand($commandName, $paramsPart = array(),
            $adapterKey = null)
    {
        if ($adapter = $this->_currentAdapter = $this->createCurrentAdapter($adapterKey)) {
            $params = $this->prepareParams($commandName, $paramsPart);
            if (!$this->beforeExecuteCommand($commandName, $params, $adapter)) {
                return null;
            }
            return $this->callAdapterMethod($adapter, $commandName, $params);
        } else {
            $this->afterNoAdapterFound($commandName);
            return null;
        }
    }
    
    public function processPostRequest()
    {
        if (isset($_POST['command']) && isset($_POST['sign']) && isset($_POST['params'])) {
            $command = $_POST['command'];
            $sign = $_POST['sign'];
            $paramsString = $_POST['params'];
            $adapterKey = isset($_POST['adapter']) ? $_POST['adapter'] : null;
            return $this->processCommandFromPost($command, $sign, $paramsString, $adapterKey);
        } else {
            $this->afterBadPostServerParams();
            return false;
        }
    }
    
    private function internalProcessRequest()
    {
        $result = $this->processPostRequest();
        if ($result instanceof TaxiInfo) {
            $result->beforeJsonEncode();
        }
        return $this->echoResult($result);
    }
    
    public function processRequest()
    {
        if (!$this->allowRemote) {
            return false;
        }
        try {
            $this->internalProcessRequest();
        } catch (Exception $exception) {
            TaxiExceptionHandler::handle($exception);
        }
    }
}
