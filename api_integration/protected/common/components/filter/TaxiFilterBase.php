<?php




abstract class TaxiFilterBase extends TaxiAdapterComponent implements ITaxiFilter
{

    
    public function applyFilterRule($params, $filterFunction, $fields = null)
    {
        if ($fields) {
            foreach ($fields as $field) {
                if (key_exists($field, $params)) {
                    $params[$field] = call_user_func(array($this, $filterFunction), $params[$field]);
                }
            }
        } else {
            foreach ($params as $field => $value) {
                if (key_exists($field, $params)) {
                    $params[$field] = call_user_func(array($this, $filterFunction), $params[$field]);
                }
            }
        }
        return $params;
    }

    
    public function filterParams($methodName, $params)
    {
        $filterMethod = 'filter__';
        if (method_exists($this, $filterMethod)) {
            $params = call_user_func_array(array($this, $filterMethod), array($params));
        }
        $filterMethod = 'filter_' . $methodName;
        if (method_exists($this, $filterMethod)) {
            $params = call_user_func_array(array($this, $filterMethod), array($params));
        }
        foreach ($params as $paramName => $value) {
            $filterMethod = 'filter_' . $methodName . '_' . $paramName;
            if (method_exists($this, $filterMethod)) {
                $params[$paramName] = call_user_func_array(array($this, $filterMethod), array($value));
            }
        }
        return $params;
    }

    
    public function filterResult($filterData)
    {
        $filterMethod = 'filterResult_' . $filterData->methodName;
        if (method_exists($this, $filterMethod)) {
            $result = call_user_func_array(array($this, $filterMethod), array($filterData));
        }

        return $filterData->result;
    }

    
    public function filterAllBeforeCall($methodName, $params)
    {
        $methodName = 'filterBeforeCall_' . $methodName;
        if (method_exists($this, $methodName)) {
            return call_user_func(array($this, $methodName), $params);
        } else {
            return $params;
        }
    }

    
    public function filterParamBeforeCall($methodName, $paramName, $value)
    {
        $methodName = 'filterBeforeCall_' . $methodName . '_' . $paramName;
        if (method_exists($this, $methodName)) {
            return call_user_func(array($this, $methodName), $value);
        } else {
            return $value;
        }
    }

}
