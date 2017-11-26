<?php




class TaxiValidator extends TaxiValidatorBase
{

    
    protected function _notEmpty($value)
    {
        return !empty($value);
    }

    
    protected function _isNaturalNumber($value)
    {
        return preg_match('/^\d+$/', $value);
    }

    
    protected function _Number($value)
    {
        return preg_match('/^[\.\-\+\d]+$/', $value);
    }

    
    public function packageRulle($params, $options, $rulleName)
    {
        $subValidator = $rulleName;
        foreach ($options as $property => $message) {
            if (key_exists($property, $params)) {
                $value = $params[$property];
                if (!$this->{$subValidator}($value)) {
                    $this->addError($property, $message);
                }
            }
        }
    }

    
    public function ruleRequire($params, $options)
    {
        $this->packageRulle($params, $options, '_notEmpty');
    }

    
    public function parseTime($value)
    {
        return date_parse_from_format('d.m.Y H:i:s', $value);
    }

}
