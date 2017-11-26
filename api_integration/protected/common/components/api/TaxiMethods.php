<?php


class TaxiMethods extends TaxiComponent
{
    
    const SPECIAL = 'special';
    
    private $_fixParamsMap = array(
        'findCars'                => array(
            'filterParam' => null,
        ),
        'findStreets'             => array(
            'streetPart' => null,
            'maxLimit'   => 10,
            'city'       => '',
        ),
        'findGeoObjects'          => array(
            'streetPart' => null,
            'maxLimit'   => 10,
            'city'       => '',
            'type'       => null,
        ),
        'getCoords'               => array(
            'address' => null,
        ),
        'sendMessageToDriver'     => array(
            'phone'   => null,
            'orderId' => null,
            'message' => null
        ),
        'findTariffs'             => array(),
        'createOrder'             => array(
            'fromCity'      => null,
            'fromStreet'    => '',
            'fromHouse'     => '',
            'fromHousing'   => '',
            'fromBuilding'   => '',
            'fromPorch'     => '',
            'fromLat'       => null,
            'fromLon'       => null,
            'toCity'        => null,
            'toStreet'      => '',
            'toHouse'       => '',
            'toHousing'     => '',
            'toBuilding'     => '',
            'toPorch'       => '',
            'toLat'         => null,
            'toLon'         => null,
            'clientName'    => '',
            'phone'         => '',
            'priorTime'     => null,
            'customCarId'   => null,
            'customCar'     => null,
            'carType'       => null,
            'carGroupId'    => null,
            'tariffGroupId' => null,
            'comment'       => null,
            'isMobile'      => null,
            'additional'    => array(),
        ),
        'callCost'                => array(
            'fromCity'      => null,
            'fromStreet'    => '',
            'fromHouse'     => '',
            'fromHousing'   => '',
            'fromBuilding'   => '',
            'fromPorch'     => '',
            'fromLat'       => null,
            'fromLon'       => null,
            'toCity'        => null,
            'toStreet'      => '',
            'toHouse'       => '',
            'toHousing'     => '',
            'toBuilding'     => '',
            'toPorch'       => null,
            'toLat'         => null,
            'toLon'         => null,
            'clientName'    => null,
            'phone'         => null,
            'priorTime'     => null,
            'customCarId'   => null,
            'customCar'     => null,
            'carType'       => null,
            'carGroupId'    => null,
            'tariffGroupId' => null,
            'comment'       => null,
            'isMobile'       => null,
            'additional'    => array(),
        ),
        'getOrderInfo'            => array(
            'orderId' => null,
        ),
        'getCarInfo'              => array(
            'carId' => null,
        ),
        'getCurrentCarInfo'       => array(
            'orderId' => null,
        ),
        'changeOrderStatus'       => array(
            'orderId'    => null,
            'statusCode' => null,
        ),
        'rejectOrder'             => array(
            'orderId' => null,
        ),
        'validateCommand'         => array(
            'command'          => null,
            'paramsToValidate' => array(),
        ),
        'ping'                    => array(),
        'sendSms'                 => array(
            'phone'  => null,
            'typeId' => null,
        ),
        'login'                   => array(
            'phone'   => null,
            'typeId'  => null,
            'smsCode' => null,
        ),
        'isLogined'               => array(
            'phone'  => null,
            'typeId' => null,
        ),
        'callMe'                  => array(
            'phone' => null,
            'name'  => null,
            'text'  => null
        ),
        'getDefaultCity'          => array(),
        'needSendSms'             => array(
            'phone' => null,
        ),
        'getJsInitializationCode' => array(),
        'addReview'               => self::SPECIAL,
        'getCity'                 => array(),
        'canGetOrderListFromAPI'  => array(),
        'findOrderList'           => array(
            'phone' => null,
            'startTime' => null,
            'endTime' => null,
        ),
        'getClientBalanceInfo'    => array(
            'phone' => null,
        ),
        'registrationClient'      => array(
            'name'  => null,
            'login' => null,
            'pass'  => null,
            'email' => null,
        ),
                        'telemaximaUrCabinetLogon'             => array(
            'login'    => null,
            'password' => null,
        ),
                'telemaximaUrCabinetLogout'            => array(
            'company' => null,         ),
                'telemaximaUrCabinetCreateLegalOrder'  => array(
            'company'       => null,             'fromCity'      => null,             'fromStreet'    => null,             'fromHouse'     => null,             'fromHousing'   => null,             'fromPorch'     => null,             'fromLat'       => null,             'fromLon'       => null,             'toCity'        => null,             'toStreet'      => null,             'toHouse'       => null,             'toHousing'     => null,             'toPorch'       => null,             'toLat'         => null,             'toLon'         => null,             'orderer'       => null,             'carryer'       => null,             'phone'         => null,             'priorTime'     => null,             'countCar'      => null,             'carType'       => null,             'carGroupId'    => null,             'tariffGroupId' => null,             'comment'       => null,         ),
        'telemaximaUrCabinetFindTariffsLegal'  => array(
            'company' => null,
        ),
                'telemaximaUrCabinetCallLegalCost'     => array(
            'company'       => null,             'fromCity'      => null,             'fromStreet'    => null,             'fromHouse'     => null,             'fromHousing'   => null,             'fromPorch'     => null,             'fromLat'       => null,             'fromLon'       => null,             'toCity'        => null,             'toStreet'      => null,             'toHouse'       => null,             'toHousing'     => null,             'toPorch'       => null,             'toLat'         => null,             'toLon'         => null,             'orderer'       => null,             'carryer'       => null,             'phone'         => null,             'priorTime'     => null,             'countCar'      => null,             'carType'       => null,             'carGroupId'    => null,             'tariffGroupId' => null,             'comment'       => null,         ),
                'telemaximaUrCabinetStatistics'        => array(
            'company'  => null,             'dateFrom' => null,             'dateTo'   => null,         ),
                'telemaximaUrCabinetCompanyInfo'       => array(
            'company' => null,         ),
                'telemaximaUrCabinetGetLegalOrderInfo' => array(
            'company' => null,         ),
                'telemaximaUrCabinetChangePassword'    => array(
            'company'  => null,             'login'    => null,             'password' => null,         ),
                'telemaximaUrCabinetSetCompanyData'    => array(
            'company'              => null,             'shortName'            => null,             'oficialName'          => null,             'legalStreet'          => null,             'legalHouse'           => null,             'legalBuilding'        => null,             'legalApartment'       => null,             'postStreet'           => null,             'postHouse'            => null,             'postBuilding'         => null,             'postApartment'        => null,             'actualStreet'         => null,             'actualHouse'          => null,             'actualBuilding'       => null,             'actualApartment'      => null,             'arriveStreet'         => null,             'arriveHouse'          => null,             'arriveBuilding'       => null,             'arriveApartment'      => null,             'inn'                  => null,             'kpp'                  => null,             'bik'                  => null,             'correspondentAccount' => null,             'operatingAccount'     => null,         ),
        'telemaximaUrCabinetGetTelephon'       => array(
            'company' => null,         ),
                'telemaximaUrCabinetSetTelephon'       => array(
            'company'   => null,             'action'    => null,             'id'        => null,             'number'    => null,             'isDefault' => null,         ),
        'telemaximaUrCabinetGetStuff'          => array(
            'company' => null,         ),
        'telemaximaUrCabinetSetStuff'          => array(
            'company'    => null,             'action'     => null,             'id'         => null,             'name'       => null,             'surname'    => null,             'patronymic' => null,             'order'      => null,             'carry'      => null,         ),
        'telemaximaUrCabinetPingId'            => array(
            'company' => null,         ),
        'telemaximaUrCabinetGetCity'           => array(
        ),
    );
    
    public function __construct()
    {
        parent::__construct();
    }
    
    
    private function getMap_addReview()
    {
        $data = new TaxiAddReviewData();
        return $data->asArray();
    }
    
    
    private function typeConversion($commandName, $params)
    {
        $converted = $params;
        $typeConversionMethod = 'typeConversion_' . $commandName;
        if (method_exists($this, $typeConversionMethod)) {
            $converted = $this->{$typeConversionMethod}($commandName, $params);
        }
        return $converted;
    }
    
    private function typeConversion_addReview($commandName, $params)
    {
        $data = new TaxiAddReviewData();
        $data->fillFromArray($params);
        return array($data);
    }
    
    private function defaultOrder($commandName)
    {
        $map = $this->_fixParamsMap[$commandName];
        if ($map === self::SPECIAL) {
            $getMapMethod = 'getMap_' . $commandName;
            $map = $this->{$getMapMethod}();
        }
        return $map;
    }
    
    private function fixOrder($commandName, $params)
    {
        $defaultOrder = $this->defaultOrder($commandName);
        foreach ($params as $param => $value) {
            if (key_exists($param, $defaultOrder)) {
                $defaultOrder[$param] = $params[$param];
            } else {
                throw new TaxiException("Bad param '{$param}' in params set for command: '{$commandName}'");
            }
        }
        return $defaultOrder;
    }
    
    public function fixParams($commandName, $params)
    {
        if (key_exists($commandName, $this->_fixParamsMap)) {
            $params = $this->fixOrder($commandName, $params);
            $params = $this->typeConversion($commandName, $params);
        } else {
            throw new TaxiException("Not found API command '{$commandName}' in fix params list!");
        }
        return $params;
    }
}
