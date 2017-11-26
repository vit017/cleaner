<?php




interface ITaxiFunctions
{

    
    public function findCars($filterParam = null);

    
    public function findStreets($streetPart, $maxLimit = 50, $city = null);

    
    public function findGeoObjects($streetPart, $maxLimit = 50, $city = null, $type = null);

    
    public function getCoords ($address = null);

    
    public function findTariffs();

    
    public function createOrder($fromCity, $fromStreet, $fromHouse,
            $fromHousing, $fromBuilding, $fromPorch, $fromLat, $fromLon, $toCity, $toStreet,
            $toHouse, $toHousing, $toBuilding, $toPorch, $toLat, $toLon, $clientName, $phone,
            $priorTime, $customCarId, $customCar, $carType, $carGroupId,
            $tariffGroupId, $comment,$isMobile, $additional);

     
    public function callCost($fromCity, $fromStreet, $fromHouse,
            $fromHousing, $fromBuilding, $fromPorch, $fromLat, $fromLon, $toCity, $toStreet,
            $toHouse, $toHousing, $toBuilding, $toPorch, $toLat, $toLon, $clientName, $phone,
            $priorTime, $customCarId, $customCar, $carType, $carGroupId,
            $tariffGroupId, $comment,$isMobile, $additional);





    
    public function getOrderInfo($orderId);

    
    public function getCarInfo($carId);

    
    public function getCurrentCarInfo($orderId);

    
    public function changeOrderStatus($orderId, $statusCode);

    
    public function rejectOrder($orderId);

    
    public function validateCommand($command, $paramsToValidate);

    
    public function ping();

    
    public function sendSms($phone, $typeId);

    
    public function login($phone, $typeId, $smsCode);

    
    public function needSendSms($phone = null);

    
    public function getAccountInfo();

    
    public function getDefaultCity();

    
    public function getJsInitializationCode();



    
    public function addReview($reviewData);

}
