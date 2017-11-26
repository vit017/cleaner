<?php
class Gootax_custom
{
    public function getCityId($city)
    {
        switch ($city) {
            case '31':
                $cityId = '211239';
                break;
            case '32':
                $cityId = '211240';
                break;
            case '33':
                $cityId = '1';
                break;
            case '34':
                $cityId = '1';
                break;
            case '35':
                $cityId = '1';
                break;
            default:
                $cityId = '35258';
                break;
        }

        return $cityId;
    }
}
