<?php


class TaxiGeoObjectInfo extends TaxiGeoInfo
{
    const TYPE_CITY = 'city';
    const TYPE_STREET = 'street';
    const TYPE_HOUSE = 'house';
    const TYPE_PUBLIC_PLACE = 'public_place';
    const TYPE_CAR = 'car';
    const TYPE_ROUTE_POINT = 'route_point';
    const TYPE_OTHER = 'other';
    
    public static $typeLabels = array(
        self::TYPE_CAR => 'машина или водитель',
        self::TYPE_OTHER => 'другое',
        self::TYPE_PUBLIC_PLACE => 'публичное место',
        self::TYPE_ROUTE_POINT => 'точка маршрута',
        self::TYPE_CITY => 'город',
        self::TYPE_STREET => 'улица',
        self::TYPE_HOUSE => 'строение',
    );
    
    public $label;
    
    public $rawLabel;
    
    public $type;
    
    public $typeLabel;
    
    public $address;
}
