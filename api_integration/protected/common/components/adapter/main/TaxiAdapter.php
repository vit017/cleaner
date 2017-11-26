<?php

abstract class TaxiAdapter extends TaxiAdapterFiltersLayer implements ITaxiFunctions
{
    
    public $defaultCity;
    
    public $dbHost = null;
    public $dbName = null;
    public $dbTableName = null;
    public $dbUser = null;
    public $dbPassword = null;
    
    public $adapterClassPath;
    
    public $costCurrency = 'руб.';
    
    public $timeZone = 0;
    
    public $onlyCarModelInfo = false;
    
    public $getOrderListFromAPI = false;
    
    public $localMethods = array();
    
    private $_rawAnswer;
    
    
    
    public function ping()
    {
        return 'Ping is OK to ' . __CLASS__ . ', but real adapter NOT RESPONSE! (' . get_class($this) . ')';
    }
    
    public function validateCommand($commandName, $paramsToValidate)
    {
        $res = new TaxiValidateMethodInfo();
        $res->command = $commandName;
        $res->paramsToValidate = $paramsToValidate;
        $methods = new TaxiMethods();
        $paramsToValidate = $methods->fixParams($commandName, $paramsToValidate);
        $res->fixedParams = $this->applyFilters($commandName, $paramsToValidate);
        $this->validateParams($commandName, $res->fixedParams);
        $res->errorsInfo = $this->getValidationErrorsInfo();
        $res->hasErrors = $res->errorsInfo->count > 0;
        return $res;
    }
    
    
    public function getRawAnswer()
    {
        return $this->_rawAnswer;
    }
    
    public function setRawAnswer($rawAnswer)
    {
        if (is_array($rawAnswer) && count($rawAnswer) > 5) {
            $rawAnswer = array_splice($rawAnswer, 0, 5);
            $rawAnswer[] = '... And more ...';
        }
        if ($this->_rawAnswer) {
            if (!is_array($this->_rawAnswer)) {
                $this->_rawAnswer = array($this->_rawAnswer, $rawAnswer);
            }
            $this->_rawAnswer[] = $rawAnswer;
        } else {
            $this->_rawAnswer = $rawAnswer;
        }
    }
    
    public function getJsInitPath()
    {
        if ($this->adapterClassPath) {
            return $this->adapterClassPath . '/assets/js/init.js';
        }
    }
    
    public function getCustomJsInitPath()
    {
        if (!$this->key) {
            throw new TaxiException("Невозможно погрузить JavaScript настройки для адаптера - " . get_class($this));
        } else {
            return TaxiEnv::$config->getConfigPath() . '/js/' . $this->key . '/init.js';
        }
    }
    
    public function getJsInitializationCode()
    {
        $path1 = $this->getJsInitPath();
        $path2 = $this->getCustomJsInitPath();
        $res = '';
        if (file_exists($path1)) {
            $res .= file_get_contents($path1) . ' ';
        }
        if (file_exists($path2)) {
            $res .= file_get_contents($path2) . ' ';
        }
        return $res;
    }
    
    public function getReviewsLog()
    {
        $reviewLog = new TaxiLog($this);
        $reviewLog->fileName = get_class($this) . '_reviews.log';
        return $reviewLog;
    }
    
    
    public function sendSms($phone, $typeId)
    {
        throw new TaxiSeverException('Метод не реализован в этом адаптере: ' . get_class($this) . ' -> ' . __FUNCTION__);
    }
    
    public function login($phone, $typeId, $smsCode)
    {
        throw new TaxiSeverException('Метод не реализован в этом адаптере: ' . get_class($this) . ' -> ' . __FUNCTION__);
    }
    
    public function getDefaultCity()
    {
        return $this->defaultCity;
    }
    
    public function canGetOrderListFromAPI()
    {
        return $this->getOrderListFromAPI;
    }
    
    public function isLocalMethod($methodName)
    {
        return in_array($methodName, $this->localMethods);
    }
    
    public function addReview($reviewData)
    {
        return 112;
        throw new TaxiSeverException('Метод не реализован в этом адаптере: ' . get_class($this) . ' -> ' . __FUNCTION__);
    }
    
    public function findGeoObjects($streetPart, $maxLimit = 10, $city = null, $type = null)
    {
        
        $streets = $this->findStreets($streetPart, $maxLimit, $city);
        if (empty($streets)) {
            if (!empty($this->dbHost)) {
                try {
                    $streetPart = explode(' ', $streetPart);
                    $formatPart = array();
                    foreach ($streetPart as $value) {
                        $formatPart[] = '*' . trim($value) . '*';
                    }
                    $streetPart = implode("", $formatPart);
                    $dbh = new PDO("mysql:host=$this->dbHost;dbname=$this->dbName;charset=utf8", $this->dbUser, $this->dbPassword);
                    if (empty($city)) {
                        $stm = $dbh->prepare("select *, (match (fulladdress_reverse) against ('$streetPart' IN BOOLEAN MODE)) as  rel from moskow  WHERE MATCH (fulladdress_reverse) against ('$streetPart' IN BOOLEAN MODE) ORDER BY rel DESC LIMIT 0, 10");
                    } else {
                        $streetPart = "*$city*" . $streetPart;
                        $stm = $dbh->prepare("select *, (match (fulladdress_reverse) against ('$streetPart' IN BOOLEAN MODE)) as  rel from moskow  WHERE MATCH (fulladdress_reverse) against ('$streetPart' IN BOOLEAN MODE) ORDER BY rel DESC LIMIT 0, 10");
                    }
                    $stm->execute();
                    $dbObjects = $stm->fetchAll(PDO::FETCH_ASSOC);
                    $objects = array();
                    foreach ($dbObjects as $street) {
                        $object = new TaxiGeoObjectInfo();
                        $object->type = TaxiGeoObjectInfo::TYPE_STREET;
                        $object->typeLabel = 'улица';
                        $object->rawLabel = $object->label = $street['fulladdress_reverse'];
                        $object->address = new TaxiAddressInfo();
                        $city = null;
                        if (!empty($street['selo'])) {
                            $city = $street['selo'];
                        } else if (!empty($street['city'])) {
                            $city = $street['city'];
                        } else if (!empty($street['okrug'])) {
                            $city = $street['okrug'];
                        } else if (!empty($street['region'])) {
                            $city = $street['region'];
                        }
                        $city = strstr(strrev($street['fulladdress']), ',');
                        $city = strrev($city);
                        $city = substr($city, 0, -1);
                        $object->address->city = $city;
                        $object->address->street = $street['street'];
                        $object->address->house = null;
                        $object->address->location = null;
                        $object->address->rawData = $object->rawLabel;
                        $objects[] = $object;
                    }
                    return $objects;
                } catch (PDOException $exception) {
                }
            }
        } else {
            $objects = array();
            foreach ($streets as $street) {
                $object = new TaxiGeoObjectInfo();
                $object->type = TaxiGeoObjectInfo::TYPE_STREET;
                $object->typeLabel = 'улица';
                $object->rawLabel = $object->label = $street;
                $object->address = new TaxiAddressInfo();
                $object->address->city = !empty($city) ? $city : $this->defaultCity;
                $object->address->street = isset($street) ? $street : null;
                $object->address->house = null;
                $object->address->location = null;
                $object->address->rawData = $object->rawLabel;
                $objects[] = $object;
            }
            return $objects;
        }
    }
    
    public function getCurrentCarInfo($orderId)
    {
        $car = new TaxiCarInfo();
        $carResult = new TaxiCarInfo();
        $car->id = 0;
        if ($orderId) {
            $order = $this->getOrderInfo($orderId);
            if ($order && isset($order->carId)) {
                $car = $this->getCarInfo($order->carId);
                $carResult->id = $car->id;
                if ((isset($car->lat)) && (isset($car->lon))) {
                    $carResult->lat = $car->lat;
                    $carResult->lon = $car->lon;
                }
            }
        }
        return $carResult;
    }
}
