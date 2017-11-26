<?php
class TaxiCityInfo extends TaxiObject
{
    public $id;
    public $cityName;
    public $lat;
    public $lon;
    public $dispatcherTel;
    public $dispatcherTelFull;
    public $airports = array();
    public $stations = array();
    public $busStations = array();
    public $extStations = array();
}
