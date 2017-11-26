<?php




abstract class TaxiValidatorBase extends TaxiAdapterComponent implements ITaxiValidator
{
    
    public $errorsInfo;

    

    
    public function __construct($adapter)
    {
        parent::__construct($adapter);

        $this->errorsInfo = new TaxiValidateErrorsInfo();
    }

    

    
    public function getErrorsInfo()
    {
        $this->errorsInfo->afterFill();
        return $this->errorsInfo;
    }

    

    
    private function beforeValidation($methodName)
    {
        $this->errorsInfo->clear();
        $this->errorsInfo->command = $methodName;
    }

    
    protected function validateMethod($methodName, $params)
    {
        $this->beforeValidation($methodName);        
        $validateMethod = 'validate_' . $methodName;
        if (method_exists($this, $validateMethod)) {
            call_user_func_array(array($this, $validateMethod), array($params));
        }
        return $this->getErrorsInfo()->count === 0;
    }

    
    public function validateParams($methodName, $params)
    {
        $this->beforeValidation($methodName);
        $methodValidation = $this->validateMethod($methodName, $params);
        foreach ($params as $paramName => $value) {
            $validateParamMethod = 'validate_' . $methodName . '_' . $paramName;
            if (method_exists($this, $validateParamMethod)) {
                call_user_func_array(array($this, $validateParamMethod), array($value));
            }
        }
        return $methodValidation && ($this->getErrorsInfo()->count === 0);
    }

    
    public function validateResult($methodName, $result)
    {
        $this->beforeValidation($methodName);
        $success = true;
        $validateResultMethod = 'validateResult_' . $methodName;
        if (method_exists($this, $methodName)) {
            $success = call_user_func_array(array($this, $validateResultMethod), array($result));
        }

        return $success;
    }

    

    
    public function addError($fieldName, $message)
    {
        $this->errorsInfo->addError($fieldName, $message);
    }

}
