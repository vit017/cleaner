<?php




class TaxiCommonFilter extends TaxiFilter
{

    
    public function filter__($params)
    {
        return $this->applyFilterRule($params, 'filterAny_strSafe');
    }

    
    public function filter_createOrder_priorTime($priorTime)
    {
        return $this->filterAny_priorTime($priorTime);
    }

    
    public function filter_createOrder_phone($value)
    {
        return $this->filterAny_phone($value);
    }

    
    public function filter_sendSms_phone($value)
    {
        return $this->filterAny_phone($value);
    }

    
    public function filter_needSendSms_phone($value)
    {
        return $this->filterAny_phone($value);
    }

    
    public function filter_isLogined_phone($value)
    {
        return $this->filterAny_phone($value);
    }

    
    public function filter_login_phone($value)
    {
        return $this->filterAny_phone($value);
    }

    

    
    private function filterAny_CarInfo($carInfo)
    {
        
        if ($this->adapter->onlyCarModelInfo) {
            $carInfo->number = $carInfo->driverId = $carInfo->driverName = $carInfo->rawData = null;
        }
    }

    
    public function filterResult_findCars($filterData)    
    {
        $cars = $filterData->result;
        foreach ($cars as $car) {
            $this->filterAny_CarInfo($car);
        }
        return $filterData->result = $cars;
    }

}
