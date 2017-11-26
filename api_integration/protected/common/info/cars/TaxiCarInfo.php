<?php


class TaxiCarInfo extends TaxiInfo
{
    
    public $id;
    
    public $lat;
    
    public $lon;
    
    public $number = '';
    
    public $color = '';
    public $productionYear = '';
    
    public $description = '';
    
    public $photoId;
     
    public $photoBase64;
    
    public $driverId;
    
    public $driverName = '';
    
    public $driverPhoto;
    
     public $driverPhone;
    
    public $isFree;
    
    public $statusCode;
    
    public $statusLabel = '';
    
    public $crewId;
    
    public $crewCode;
    
    public $rawData;

    public $course;
}
