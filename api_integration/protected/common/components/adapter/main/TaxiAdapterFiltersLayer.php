<?php


abstract class TaxiAdapterFiltersLayer extends TaxiComponent implements ITaxiAdapter, ITaxiFunctions
{
    
    public static $customClassesSubDir = '/custom/';

    
    public $label;

    
    public $configuratorClass;

    
    public $customFilterClass;

    
    public $customValidatorClass;

    
    public $accessRulles;

    
    public $key;

    
    private $_commonFilter;
    private $_commonValidator;
    private $_filter;
    private $_validator;
    private $_clientAuth;
    private $_configurator;

    

    
    public function getValidationErrorsInfo()
    {
        $commonValidator = $this->getCommonValidator();
        $customAdapterValidator = $this->getValidator();
        if ($customAdapterValidator) {
            return $commonValidator->getErrorsInfo()->mergeWith($customAdapterValidator->getErrorsInfo());
        } else {
            return $commonValidator->getErrorsInfo();
        }
    }

    
    public function getCommonFilter()
    {
        if (!$this->_commonFilter) {
            $this->_commonFilter = new TaxiCommonFilter($this);
        }
        return $this->_commonFilter;
    }

    
    public function getCommonValidator()
    {
        if (!$this->_commonValidator) {
            $this->_commonValidator = new TaxiCommonValidator($this);
        }
        return $this->_commonValidator;
    }

    
    public function setCommonFilter($commonFilter)
    {
        $this->_commonFilter = $commonFilter;
    }

    
    public function setCommonValidator($commonValidator)
    {
        $this->_commonValidator = $commonValidator;
    }

    
    public function getFilter()
    {
        if (!$this->_filter) {
            $this->_filter = $this->createFilter();
        }
        return $this->_filter;
    }

    
    public function getValidator()
    {
        if (!$this->_validator) {
            $this->_validator = $this->createValidator();
        }
        return $this->_validator;
    }

    
    public function setFilter($filter)
    {
        $this->_filter = $filter;
    }

    
    public function setValidator($validator)
    {
        $this->_validator = $validator;
    }

    
    public function getAuthorizationSalt()
    {
        return get_class($this) . 'eyriSk2s28';
    }

    
    public function getClientAuthorization()
    {
        if (!$this->_clientAuth) {
            $this->_clientAuth = new TaxiClientAuthorization($this->getAuthorizationSalt());
        }
        return $this->_clientAuth;
    }

    
    protected function createConfigurator()
    {
        if ($class = $this->configuratorClass) {
            return new $class();
        }
    }

    
    public function getConfigurator()
    {
        if (!$this->_configurator) {
            $this->_configurator = $this->createConfigurator();
        }
        return $this->_configurator;
    }
    
    

    
    public function hasConfigurator()
    {
        return !empty($this->configuratorClass);
    }

    
    
    
    public function applyFilters($commandName, $params)
    {
        $params = $this->applyManyFilters($commandName, $params, array(
            $this->getCommonFilter(),
            $this->getFilter(),
        ));
        return $params;
    }

    
    public function applyResultFilters($filterData)
    {
        $this->getFilter()->filterResult($filterData);
        $this->getCommonFilter()->filterResult($filterData);
        
        return $filterData->result;
    }

    
    public function checkAccess($commandName, $params)
    {
        if ($this->accessRulles) {
            return $this->accessRulles->checkAccess($commandName, $params);
        } else {
            return true;
        }
    }

    
    public function validateParams($commandName, $params)
    {
        $res = $this->applyManyValidators($commandName, $params, array(
            $this->getCommonValidator(),
            $this->getValidator(),
        ));
        return $res;
    }
    
    
    public function filterBeforeCall($methodName, $params)
    {
        $params = $this->getCommonFilter()->filterAllBeforeCall($methodName, $params);
        $params = $this->getFilter()->filterAllBeforeCall($methodName, $params);
        
        foreach ($params as $paramName => $value){
            $params[$paramName] = $this->getCommonFilter()->filterParamBeforeCall($methodName, $paramName, $value);
            $params[$paramName] = $this->getFilter()->filterParamBeforeCall($methodName, $paramName, $value);
        }
        return $params;
    }

    

    
    public function createFilter()
    {
        if ($this->customFilterClass) {
            $class = $this->customFilterClass;
        } else {
            $class = get_class($this) . 'Filter';
        }
        return new $class($this);
    }

    
    public function createValidator()
    {
        if ($this->customValidatorClass) {
            $class = $this->customValidatorClass;
        } else {
            $class = get_class($this) . 'Validator';
        }
        return new $class($this);
    }

    
    protected function applyManyFilters($commandName, $params, $filters)
    {
        foreach ($filters as $filter) {
            if ($filter) {
                $params = $filter->filterParams($commandName, $params);
            }
        }
        return $params;
    }

    
    protected function applyManyValidators($commandName, $params, $validators)
    {
        $res = true;
        foreach ($validators as $validator) {
            if ($validator) {
                $success = $validator->validateParams($commandName, $params);
                $res = $res && $success;
            }
        }
        return $res;
    }

}
