<?php

class TaxiYandexSuggest extends TaxiObject
{
    public $city;
    public $streetPart;
    public $maxLimit;
    public static function findStreets($streetPart, $maxLimit = 50, $city = null)
    {
        if (empty($city)) {
            $part = $streetPart;
        } else {
            $part = $city . ', ' . $streetPart;
        }
        $part = urlencode($part);
        $url = "http://suggest-maps.yandex.ru/suggest-geo?jsonp=jQuery19104147204724140465_1401687188238&part={$part}&lang=ru-RU&search_type=all&ll=64.22769438476561%2C62.20712366243136&spn=82.61718750000001%2C32.68027802373878&fullpath=1&v=5";
        $result = file_get_contents($url);
        $result = str_replace("suggest.apply(", "", $result);
        $result = str_replace(")", "", $result);
        $result = json_decode($result);
        $streets = array();
        if (isset($result['1'])) {
            foreach ($result['1'] as $res) {
                foreach ($res as $key => $value) {
                    if ($key == '1') {
                        $street = substr($res[$key], 0, strpos($res[$key], ','));
                        $isIn = strpos(strtoupper($street), strtoupper($streetPart));
                        if ($isIn !== false) {
                            $streets[] = $street;
                        }
                    }
                }
            }
        }
        $streets = array_unique($streets);
        return $streets;
    }
}
