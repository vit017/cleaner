<?php




abstract class TaxiServerEventsLayer extends TaxiServerBaseLayer
{

    
    public function afterFailCheckAccess($commandName)
    {
        $this->log->error("Access checking error by method {$commandName}");
        $this->_errorCode = TaxiServerErrors::ACCESS_ERROR;
        $this->_errorMessage = 'Доступ запрещен';
    }

    
    public function afterNoAdapterFound($commandName)
    {
        $this->_errorCode = TaxiServerErrors::INTERNAL_ERROR;
        $this->_errorMessage = 'Адаптер не найден';
        $this->log->error($commandName . ' - cannt run this method - this method name is not exists and NO Adapters found!');
    }

    
    public function afterFailParamsValidation($adapter)
    {
        $this->log->warning("Fail validation for adapter " . get_class($adapter));
        $this->log->warning("Was founded some errors: \n" . CVarDumper::dumpAsString($adapter->getValidationErrorsInfo()));
        $this->_errorCode = TaxiServerErrors::BAD_PARAMS;
        $this->_errorMessage = 'Ошибка входных параметров';
    }

    
    public function beforeExecuteCommand($commandName, $params, $adapter)
    {
        if (!$adapter->checkAccess($commandName, $params)) {
            $this->afterFailCheckAccess($commandName);
            return false;
        }
        if (!$adapter->validateParams($commandName, $params)) {
            $this->afterFailParamsValidation($adapter);
            return false;
        }
        return true;
    }

    
    public function afterMethodNotFound()
    {
        $this->_errorCode = TaxiServerErrors::BAD_COMMAND;
        $this->log->error($commandName . ' - cannt run this method - adapter hasnt this method');
    }

    
    public function afterBadPostServerParams()
    {
        $this->_errorCode = TaxiServerErrors::BAD_COMMAND;
        $this->log->error('Not found POST all params: command, sign, params');
    }

    
    public function afterFailJsonDecode($paramsString)
    {
        $this->_errorCode = TaxiServerErrors::BAD_PARAMS;
        $this->log->error('$params cannt decode JSON format! ' . $paramsString);
    }

    
    public function afterCorruptSign($commandName, $sign)
    {
        $this->_errorCode = TaxiServerErrors::BAD_SIGN;
        $this->log->error('Cannt validate sign! (Corrupt SIGN?)');
    }

}
