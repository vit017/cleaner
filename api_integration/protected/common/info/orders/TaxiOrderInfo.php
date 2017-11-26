<?php


class TaxiOrderInfo extends TaxiInfo
{
    
    const STATUS_NEW = 'new';
    
    const STATUS_CAR_ASSIGNED = 'car_assigned';
    
    const STATUS_REJECTED = 'rejected';
    
    const STATUS_CAR_AT_PLACE = 'car_at_place';
    
    const STATUS_EXECUTING = 'executing';
    
    const STATUS_COMPLETED = 'completed';
    
    const STATUS_PRIOR = 'prior';
    
    const STATUS_DRIVER_BUSY = 'driver_busy';
    
    public $id;
    
    public $status = '';
    
    public $statusLabel;
    
    public $idLabel;
    
    public $cost;
    
    public $clientName;
    
    public $clientPhone;
    
    public $costCurrency;
    
    public $carDescription;
    
    public $carId;
    
    public $carLat;
    
    public $carLon;
    
    public $carPhotoBase64;
    
    public $driverId;
    
    public $driverFio;
    
    public $driverPhone;
    
    public $driverPhotoBase64;
    
    public $carTime;
    
    public $priorTime;
    
    public $isPrior;
    
    public $statusCode;
    
    public $rawFrom;
    public $fromCity;
    public $fromStreet;
    public $fromHouse;
    public $fromPorch;
    public $fromHousing;
    
    public $rawTo;
    public $toCity;
    public $toStreet;
    public $toHouse;
    public $toPorch;
    public $toHousing;
    
    public $tariffInfo;
    
    public $comment;
    
    public $detailOrderInfo;
    
    public $fromLat;
    
    public $fromLon;
    
    public $toLat;
    
    public $toLon;
    
    public $orderTime;
    
    public $city;
    
    public $rawData;
    
    public $payHtml;
    
    public function __construct()
    {
        $this->status = self::STATUS_NEW;
    }
    
    
    public function afterFill()
    {
        
        /*if ($this->carId && $this->status === self::STATUS_NEW) {
            $this->status = self::STATUS_CAR_ASSIGNED;
        }//*/
        if (($this->status !== self::STATUS_CAR_ASSIGNED) && (isset($this->carTime))) {
            unset($this->carTime);
        }
        if (empty($this->statusLabel)) {
            if ((isset($this->isPrior)) && ($this->isPrior === true)) {
                $this->statusLabel = 'заказ принят - автоинформатор сообщит марку и цвет автомобиля перед подачей';
            } else {
                $this->statusLabel = ' идет поиск авто ...';
            }
        }
    }
}
