<?php

class GootaxAdapterFilter extends TaxiFilter
{
    public function filter_createOrder($params)
    {
		return $params;
    }

    public function filter_callCost($params)
    {
		return $params;
    }

    public function filter_createOrder_priorTime($value)
    {
        return $value;
    }

}
