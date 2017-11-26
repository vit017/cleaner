<?php


class TaxiNominatimGeocoder
{
    
    public function sendGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3');
        $output = curl_exec($ch);
        curl_close($ch);
        $routeData = $output;
        $roteData = json_decode($routeData, true);
        return $roteData;
    }
    
    public function findCoordsByAddress($address)
    {
        $result['lat'] = null;
        $result['lon'] = null;
        $url = "http://nominatim.openstreetmap.org/search?q={$address}&format=json&polygon=0&addressdetails=1&accept-language=ru";
        $geoData = $this->sendGet($url);
        if (isset($geoData['lat'])) {
            $result['lat'] = $geoData['lat'];
        }
        if (isset($geoData['lon'])) {
            $result['lon'] = $geoData['lon'];
        }
        return $result;
    }
    
    public function getObjectPoligons($address, $reverse = false)
    {
        $address = urlencode($address);
        $url = "http://nominatim.openstreetmap.org/search?q=$address&format=json&addressdetails=1&accept-language=ru&limit=1&polygon_geojson=1";
        $roteData = $this->sendGet($url);
        $poligons = array();
        if ((isset($roteData['0']['geojson']['type'])) && (isset($roteData['0']['geojson']['coordinates']))) {
            $type = $roteData['0']['geojson']['type'];
            if ($type == "MultiPolygon") {
                $result = $roteData['0']['geojson']['coordinates'];
                $poligons = array();
                $i = 0;
                foreach ($result as $res) {
                    foreach ($res[0] as $res2) {
                        $poligons[$i][] = $res2;
                    }
                    $i++;
                }
            } else if ($type == "Polygon") {
                $result = $roteData['0']['geojson']['coordinates'];
                $poligons = array();
                foreach ($result['0'] as $res) {
                    $poligons['0'][] = $res;
                }
            }
            if ($reverse == true) {
                $reverse = array();
                $i = 0;
                $j = 0;
                foreach ($poligons as $poligon) {
                    $reverse[$i] = array();
                    foreach ($poligon as $poligon2) {
                        $reverse[$i][$j]['0'] = $poligon2['1'];
                        $reverse[$i][$j]['1'] = $poligon2['0'];
                        $j++;
                    }
                    $i++;
                    $j = 0;
                }
                return $reverse;
            }
        }
        return $poligons;
    }
}
