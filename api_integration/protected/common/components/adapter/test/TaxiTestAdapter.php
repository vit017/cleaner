<?php




class TaxiTestAdapter extends TaxiSmsAdapter
{
    

    
    public $lat = 43.591381073;

    
    public $lon = 39.7270584106;

    public function __construct()
    {
        parent::__construct();

        $this->label = 'Внутренний тестовый адаптер';
        $this->key = 'test';
        $this->defaultCity = 'Сочи';

        


        $this->useSmsAuthorization = true;
    }

    

    public function callCost($fromCity, $fromStreet, $fromHouse, $fromHousing, $fromPorch, $fromLat, $fromLon, $toCity, $toStreet, $toHouse, $toHousing, $toPorch, $toLat, $toLon, $clientName, $phone, $priorTime, $customCarId, $customCar, $carType, $carGroupId, $tariffGroupId, $comment)
    {
        return null;
    }

      public function findOrderList($phone)
    {
        return null;
    }

    public function getCoords($address = null)
    {
        return null;
    }

    
    private function criticalErrorEmulation()
    {
        switch (rand(0, 4)) {
            case 0:
                $this->errorEmulation->badDie();
                break;
            case 1:
                $this->errorEmulation->exception();
                break;
            case 2:
                $this->errorEmulation->outOfMemory();
                break;
            case 3:
                $this->errorEmulation->stackError();
                break;
            case 4:
                return null;
                break;

            default:
                break;
        }
    }

    
    public function findCars($filterParam = null)
    {

        $cars = array();
        $count = rand(1, 10);
        $first = true;
        for ($i = 1; $i <= $count; $i++) {
            $car = new TaxiCarInfo();

            $car->id = 54 + $i;

            $car->lat = $this->lat + rand(0, 10000) / 100000;
            $car->lon = $this->lon + rand(0, 10000) / 100000;
            $car->crewId = null;
            $car->crewCode = null;
            if ($first) {
                $car->isFree = true;
                $first = false;
            } else {
                $car->isFree = rand(0, 1) == 0;
            }

            $car->statusCode = 'not_defined_test';

            $car->color = 'тест_цвет';
            $car->number = 'гс' . rand(1000, 9999);
            $car->description = 'тестКалина';

            $cars[] = $car;
        }
        return $cars;
    }

    
    public function findStreets($streetPart, $maxLimit = 50, $city = null)
    {
        $res = array();
        for ($i = 1; $i <= $maxLimit; $i++) {
            $res[] = $streetPart . '__' . rand(10000, 10000000);
        }
        return $res;
    }

    
    public function findTariffs()
    {
        $tarifs = array();

        $tmp = new TaxiTariffInfo();
        $tmp->label = 'Загородный';
        $tmp->id = '22';
        $tmp->groupId = '0';

        $tmp = new TaxiTariffInfo();
        $tmp->label = 'Обычный';
        $tmp->id = '1';
        $tmp->groupId = '0';

        $tmp = new TaxiTariffInfo();
        $tmp->label = 'Ночной (тест)';
        $tmp->id = '23';
        $tmp->groupId = '44';

        $tarifs[] = $tmp;

        return $tarifs;
    }

    
    public function createOrder($fromCity, $fromStreet, $fromHouse, $fromHousing, $fromPorch, $fromLat, $fromLon, $toCity, $toStreet, $toHouse, $toHousing, $toPorch, $toLat, $toLon, $clientName, $phone, $priorTime, $customCarId, $customCar, $carType, $carGroupId, $tariffGroupId, $comment, $isMobile = null)
    {
        return 5551100 . rand(11, 99);
    }

    
    public function getOrderInfo($orderId)
    {
        $info = new TaxiOrderInfo();

        if (rand(0, 3) === 2) {
            return 'sdfdsfdsf';
        }

        $info->comment = 'тест - Салон для собаки';
        $info->id = $orderId;
        $info->cost = '200';
        $info->statusLabel = 'заказ принят, ожидание машины';
        $info->rawFrom = 'г.Сочи ул. Телегина, 24а-3';

        if (rand(0, 1) === 1) {
            $info->statusLabel = 'назначен водитель';
            $info->carId = 55;

            $car = $this->getCurrentCarInfo($orderId);
            $info->carInfo = $car;
        }

        return $info;
    }

    
    public function getCarInfo($carId)
    {
        $car = new TaxiCarInfo();

        $car->color = 'красная';
        $car->crewCode = '333';
        $car->driverId = '231';
        $car->driverName = 'Иван';

        $car->isFree = false;
        $car->lat = 43.591381073 + rand(0, 1000) / 100000;
        $car->lon = 39.7270584106 + rand(0, 1000) / 100000;
        $car->crewId = null;
        $car->crewCode = null;
        $car->isFree = rand(0, 1) == 0;
        $car->statusCode = 'not_defined_test';
        $car->id = $carId;

        $car->color = 'тест_цвет';
        $car->number = 'гс' . rand(1000, 9999);
        $car->description = 'Ваз 2101';

        return $car;
    }

    
    public function changeOrderStatus($orderId, $statusCode)
    {
        return rand(0, 1) == 0;
    }

    
    public function rejectOrder($orderId)
    {
        return true;
    }

    
    public function getAccountInfo()
    {
        return false;
    }

    
    public function getSmsLog()
    {
        $log = new TaxiLog($this);
        $log->fileName = 'test_REAL_Sms.log';
        return $log;
    }

    
    public function sendRealSms($toPhone, $message)
    {
        return true;
    }

}
