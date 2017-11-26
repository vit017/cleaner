<?php



class TaxiAppleFakeAdapter
{

    public $_cache;
    public $defaultCars;

    public function __construct()
    {
        $this->_cache = new TaxiFileCache('/' . get_class($this) . '.dat');
        $this->defaultCars = array(
            '1' => array(
                'id'          => '11',
                'isFree'      => true,
                'color'       => 'черный',
                'number'      => 'х999хх',
                'description' => 'ауди а8',
                'driverId'    => 21,
                'driverName'  => 'Игорь Сергеевич',
                'statusCode'  => '0',
                'statusLabel' => 'Свободен',
                'crewId'      => null,
                'crewCode'    => '107',
            ),
            '2' => array(
                'id'          => '12',
                'isFree'      => true,
                'color'       => 'серый',
                'number'      => 'а111аа',
                'description' => 'ваз 21012',
                'driverId'    => 22,
                'driverName'  => 'Иван Иванович',
                'statusCode'  => '0',
                'statusLabel' => 'Свободен',
                'crewId'      => null,
                'crewCode'    => '108',
            ),
            '3' => array(
                'id'          => '13',
                'isFree'      => rand(0, 1) == 1,
                'color'       => 'белый',
                'number'      => 'у333уу',
                'description' => 'toyota corolla',
                'driverId'    => 23,
                'driverName'  => 'Сергей сергеевич',
                'statusCode'  => '0',
                'statusLabel' => 'Свободен',
                'crewId'      => null,
                'crewCode'    => '109',
            ),
            '4' => array(
                'id'          => '14',
                'isFree'      => rand(0, 1) == 1,
                'color'       => 'черный',
                'number'      => 'ф222фф',
                'description' => 'lada kalina',
                'driverId'    => 24,
                'driverName'  => 'Иван Петрович',
                'statusCode'  => '0',
                'statusLabel' => 'Свободен',
                'crewId'      => null,
                'crewCode'    => '110',
            ),
            '5' => array(
                'id'          => '15',
                'isFree'      => false,
                'color'       => 'зеленый',
                'number'      => 'а001аа',
                'description' => 'lada kalina',
                'driverId'    => 25,
                'driverName'  => 'Иван Петрович',
                'statusCode'  => '1',
                'statusLabel' => 'На заказе',
                'crewId'      => null,
                'crewCode'    => '111',
            ),
        );
    }

    public function createOrder($fromCity, $fromStreet, $fromHouse, $fromHousing, $fromBuilding, $fromPorch, $fromLat, $fromLon, $toCity, $toStreet, $toHouse, $toHousing, $toBuilding, $toPorch, $toLat, $toLon, $clientName, $phone, $priorTime, $customCarId, $customCar, $carType, $carGroupId, $tariffGroupId, $comment, $isMobile)
    {
        if (!$priorTime) {
            $priorTime1 = new DateTime();
            $priorTime1->add(new DateInterval('PT' . 10 . 'M'));
            $priorTime = $priorTime1->format('Y-m-d H:i:s');
        }
        if (!preg_match('/\q(.+)\q/', $comment, $m)) {
            $cost = rand(100, 900);
            $comment = "q{$cost}q";
        }

        $fromHouse = ',' . $fromHouse;
        $toHouse = ',' . $toHouse;
        $source = $this->createAddress(array(
            $fromCity    => array(),
            $fromStreet  => array(),
            $fromHouse   => array('noSpaceBefore' => true),
            $fromHousing => array('prefix' => 'к'),
            $fromPorch   => array('prefix' => 'п'),
        ));

        $destination = $this->createAddress(array(
            $toCity    => array(),
            $toStreet  => array(),
            $toHouse   => array('noSpaceBefore' => true),
            $toHousing => array('prefix' => 'к'),
            $toPorch   => array('prefix' => 'п'),
        ));

        $orderData = array(
            'source'        => $source,
            'dest'          => $destination,
            'fromCity'      => $fromCity,
            'fromStreet'    => $fromStreet,
            'fromHouse'     => $fromHouse,
            'fromHousing'   => $fromHousing,
			'fromBuilding'  => $fromBuilding,
            'fromPorch'     => $fromPorch,
            'fromLat'       => $fromLat,
            'fromLon'       => $fromLon,
            'toCity'        => $toCity,
            'toStreet'      => $toStreet,
            'toHouse'       => $toHouse,
            'toHousing'     => $toHousing,
			'toBuilding'    => $toBuilding,
            'toPorch'       => $toPorch,
            'toLat'         => $toLat,
            'toLon'         => $toLon,
            'clientName'    => $clientName,
            'phone'         => $phone,
            'priorTime'     => $priorTime,
            'customCarId'   => $customCarId,
            'customCar'     => $customCar,
            'carType'       => $carType,
            'carGroupId'    => $carGroupId,
            'tariffGroupId' => $tariffGroupId,
            'comment'       => $comment
        );

        $orderId = 1000 + rand(1, 999);
        $this->_cache->setValue('orderData' . $orderId, $orderData);
        $this->_cache->setValue('iterator' . $orderId, 1);
        return (string) $orderId;
    }

    protected function createAddress($options)
    {
        $parts = array();
        foreach ($options as $word => $flags) {
            $prefix = isset($flags['prefix']) ? $flags['prefix'] : '';
            $noSpaceBefore = isset($flags['noSpaceBefore']) && $flags['noSpaceBefore'];
            $word = trim($word);
            if (!empty($word)) {
                if ($noSpaceBefore) {
                    $word = '###' . $word;
                }
                $parts[] = $prefix . $word;
            }
        }
        $res = '';
        $res = implode(' ', $parts);
        $res = str_replace(' ###', '', $res);
        $res = str_replace('###', '', $res);
        return $res;
    }

    
    public function getOrderInfo($orderId)
    {

        $iterator = $this->getInterator($orderId);
        $orderData = $this->_cache->getValue('orderData' . $orderId);
        $info = new TaxiOrderInfo();
        $info->costCurrency = '';
        if ($iterator <= 6) {             $info->id = (string) $orderId;
            $info->orderTime = $orderData['priorTime'];
            if (preg_match('/\q(.+)\q/', $orderData['comment'], $m)) {
                $info->cost = (integer) $m[1];
            }
            $info->rawFrom = $orderData['source'];
            $info->rawTo = $orderData['dest'];
            $info->statusLabel = "идет поиск авто...";
            $info->status = TaxiOrderInfo::STATUS_NEW;
        }
        if (($iterator >= 7) and ( $iterator <= 13)) {             $info->id = (string) $orderId;
            $info->orderTime = $orderData['priorTime'];
            if (preg_match('/\q(.+)\q/', $orderData['comment'], $m)) {
                $info->cost = (integer) $m[1];
            }
            $info->rawFrom = $orderData['source'];
            $info->rawTo = $orderData['dest'];
            $carCache = $this->_cache->getValue('car' . $orderId);
            $this->getRandomCar($orderId);
            $carRandom = $this->_cache->getValue('carRandom' . $orderId);
            $car = new TaxiCarInfo();
            $car->id = $carRandom->id;
            $car->lat = null;
            $car->lon = null;
            $car->crewId = $carRandom->crewId;
            $car->crewCode = $carRandom->crewCode;
            $car->isFree = false;
            $car->statusCode = 1;
            $car->statusLabel = 'На заказе';
            $car->color = $carRandom->color;
            $car->number = $carRandom->number;
            $car->driverId = $carRandom->driverId;
            $car->driverName = $carRandom->driverName;
            $car->description = $carRandom->description;
            $info->carId = (integer) $car->id;
            $info->driverFio = $car->driverName;
            $info->carTime = (integer) rand(10, 15);
            $info->carDescription = $car->description . " " . $car->color . " " . $car->number;
            $this->_cache->setValue('car' . $orderId, $car);
            $this->_cache->setValue('carLive' . $car->id, $car);
            $this->_cache->setValue('carIterator' . $car->id, $iterator);
            $this->_cache->setValue('carFromLat' . $car->id, $orderData['fromLat']);
            $this->_cache->setValue('carFromLon' . $car->id, $orderData['fromLon']);
            $this->_cache->setValue('carToLat' . $car->id, $orderData['toLat']);
            $this->_cache->setValue('carToLon' . $car->id, $orderData['toLon']);
            $info->statusLabel = "Назначен автомобиль";
            $info->status = TaxiOrderInfo::STATUS_CAR_ASSIGNED;
        }

        if (($iterator >= 14) and ( $iterator <= 20)) {
            $info->id = (string) $orderId;
            $info->orderTime = $orderData['priorTime'];
            if (preg_match('/\q(.+)\q/', $orderData['comment'], $m)) {
                $info->cost = (integer) $m[1];
            }
            $info->rawFrom = $orderData['source'];
            $info->rawTo = $orderData['dest'];
            $carCache = $this->_cache->getValue('car' . $orderId);
            if (isset($carCache)) {
                $car = $carCache;
                $info->carId = (integer) $car->id;
                $info->driverFio = $car->driverName;
                $info->carDescription = $car->description . " " . $car->color . " " . $car->number;
            }
            $info->statusLabel = "Водитель подъехал";
            $info->status = TaxiOrderInfo::STATUS_CAR_AT_PLACE;
            $this->_cache->setValue('carIterator' . $car->id, $iterator);
        }

        if (($iterator >= 21) and ( $iterator <= 32)) {
            $info->id = (string) $orderId;
            $info->orderTime = $orderData['priorTime'];
            if (preg_match('/\q(.+)\q/', $orderData['comment'], $m)) {
                $info->cost = (integer) $m[1];
            }
            $info->rawFrom = $orderData['source'];
            $info->rawTo = $orderData['dest'];

            $carCache = $this->_cache->getValue('car' . $orderId);
            if (isset($carCache)) {
                $car = $carCache;
                $info->carId = (integer) $car->id;
                $info->driverFio = $car->driverName;
                $info->carDescription = $car->description . " " . $car->color . " " . $car->number;
            }
            $info->statusLabel = "Таксометр включен";
            $info->status = TaxiOrderInfo::STATUS_EXECUTING;
            $this->_cache->setValue('carIterator' . $car->id, $iterator);
        }
        if (($iterator >= 33)) {
            $info->id = (string) $orderId;
            $info->orderTime = $orderData['priorTime'];
            if (preg_match('/\q(.+)\q/', $orderData['comment'], $m)) {
                $info->cost = (integer) $m[1];
            }
            $info->rawFrom = $orderData['source'];
            $info->rawTo = $orderData['dest'];

            $carCache = $this->_cache->getValue('car' . $orderId);
            if (isset($carCache)) {
                $car = $carCache;
                $info->carId = (integer) $car->id;
                $info->driverFio = $car->driverName;
                $info->carDescription = $car->description . " " . $car->color . " " . $car->number;
                $this->_cache->setValue('carIsFree' . $car->id, 1);

                $car->isFree = true;
                $car->statusCode = 0;
                $car->statusLabel = 'Свободен';
                $this->_cache->setValue('car' . $orderId, $car);
                $this->_cache->setValue('carLive' . $car->id, $car);
            }
            $info->statusLabel = "Выполнен";
            $info->status = TaxiOrderInfo::STATUS_COMPLETED;
            $this->_cache->setValue('carIterator' . $car->id, $iterator);
        }
        $orderRejected = $this->_cache->getValue('orderRejected' . $orderId);
        if (isset($orderRejected)) {
            $info->statusLabel = "Отменен";
            $info->status = TaxiOrderInfo::STATUS_REJECTED;
            $info->carId = null;
            $info->driverFio = null;
            $info->carDescription = null;
            $info->cost = null;
        }
        return $info;
    }

    
    public function getInterator($orderId)
    {
        $iterator = $this->_cache->getValue('iterator' . $orderId);
        $this->_cache->setValue('iterator' . $orderId, $iterator + 1);
        $iterator = $this->_cache->getValue('iterator' . $orderId);
        return $iterator;
    }

    public function getRandomCar($orderId)
    {
        $carRandomId = rand(1, 5);
        $defaultCar = $this->defaultCars["{$carRandomId}"];
        $car = new TaxiCarInfo();
        $car->id = $defaultCar['id'];
        $car->crewId = $defaultCar['crewId'];
        $car->crewCode = $defaultCar['crewCode'];
        $car->isFree = $defaultCar['isFree'];
        $car->statusCode = $defaultCar['statusCode'];
        $car->statusLabel = $defaultCar['statusLabel'];
        $car->color = $defaultCar['color'];
        $car->number = $defaultCar['number'];
        $car->driverId = $defaultCar['driverId'];
        $car->driverName = $defaultCar['driverName'];
        $car->description = $defaultCar['description'];
        $isFree = $this->_cache->getValue('carIsFree' . $car->id);
        if (isset($isFree)) {
            $isFree = (boolean) $isFree;
        } else {
            $isFree = (boolean) $car->isFree;
        }
        $this->_cache->setValue('carRandom' . $orderId, $car);
    }

    
    public function getCarInfo($carId)
    {
        foreach ($this->defaultCars as $defaultCar) {
            if ($defaultCar['id'] == $carId) {
                $car = new TaxiCarInfo();
                $car->id = $defaultCar['id'];
                $car->lat = null;
                $car->lon = null;
                $car->crewId = $defaultCar['crewId'];
                $car->crewCode = $defaultCar['crewCode'];
                $car->isFree = false;
                $car->statusCode = $defaultCar['statusCode'];
                $car->statusLabel = $defaultCar['statusLabel'];
                $car->color = $defaultCar['color'];
                $car->number = $defaultCar['number'];
                $car->driverId = $defaultCar['driverId'];
                $car->driverName = $defaultCar['driverName'];
                $car->description = $defaultCar['description'];
                return $car;
            }
        }
    }

    
    public function rejectOrder($orderId)
    {
        sleep(2);
        $response = $this->_cache->setValue('orderRejected' . $orderId, 1);
        sleep(2);
        if ($response === false) {
            return 0;
        } else {
            return 1;
        }
    }

}
