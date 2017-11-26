<?php


class TaxiOsrmRouter
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
    
    public function getRouteInfo($fromLat, $fromLon, $toLat, $toLon)
    {
        $result = array();
        $result['time'] = null;
        $result['distance'] = null;
        $url = "http://router.project-osrm.org/viaroute?loc={$fromLat},{$fromLon}&loc={$toLat},{$toLon}";
        $geoData = $this->sendGet($url);
        if (isset($geoData['route_summary'] ['total_time'])) {
            $result['time'] = $geoData['route_summary'] ['total_time'];
        }
        if (isset($geoData['route_summary']['total_distance'])) {
            $result['distance'] = $geoData['route_summary']['total_distance'];
        }
        if (empty($result['time']) && empty($result['distance'])) {
            $url = "http://maps.googleapis.com/maps/api/directions/json?origin=$fromLat,$fromLon&destination=$toLat,$toLon&sensor=false";
            $geoData = $this->sendGet($url);
            if (isset($geoData['routes']['0']['legs']['0']['distance']['value'])) {
                $result['distance'] = $geoData['routes']['0']['legs']['0']['distance']['value'];
            }
            if (isset($geoData['routes']['0']['legs']['0']['duration']['value'])) {
                $result['time'] = $geoData['routes']['0']['legs']['0']['duration']['value'];
            }
        }
        return $result;
    }
    
    public function getRoutePoints($fromLat, $fromLon, $toLat, $toLon)
    {
        $url = "http://router.project-osrm.org/viaroute?loc={$fromLat},{$fromLon}&loc={$toLat},{$toLon}";
        $geoData = $this->sendGet($url);
        if (isset($geoData['route_geometry'])) {
            $routePoints = $this->decodePolylineToArray($geoData['route_geometry'], 'osrm');
            $startPoint = array("0" => $fromLat, "1" => $fromLon);
            array_unshift($routePoints, $startPoint);
            $endPoint = array("0" => $toLat, "1" => $toLon);
            array_push($routePoints, $endPoint);
            return $routePoints;
        } else {
            $url = "http://maps.googleapis.com/maps/api/directions/json?origin=$fromLat,$fromLon&destination=$toLat,$toLon&sensor=false";
            $geoData = $this->sendGet($url);
            if (isset($geoData['routes']['0']['overview_polyline']['points'])) {
                $geoData = $geoData['routes']['0']['overview_polyline']['points'];
                $routePoints = $this->decodePolylineToArray($geoData, 'google');
                $startPoint = array("0" => $fromLat, "1" => $fromLon);
                array_unshift($routePoints, $startPoint);
                $endPoint = array("0" => $toLat, "1" => $toLon);
                array_push($routePoints, $endPoint);
                return $routePoints;
            }
        }
        return array();
    }
    
    function decodePolylineToArray($encoded, $type)
    {
        $length = strlen($encoded);
        $index = 0;
        $points = array();
        $lat = 0;
        $lng = 0;
        while ($index < $length) {
                        $b = 0;
                                                $shift = 0;
            $result = 0;
            do {
                                                                                $b = ord(substr($encoded, $index++)) - 63;
                                                                                                                                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            }
                                    while ($b >= 0x20);
                                    $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
                        $lat += $dlat;
                        $shift = 0;
            $result = 0;
            do {
                $b = ord(substr($encoded, $index++)) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lng += $dlng;
                                                            if ($type == 'osrm') {
                $pow = '1e-6';
            } else if ($type == 'google') {
                $pow = '1e-5';
            }
            $points[] = array($lat * $pow, $lng * $pow);
        }
        return $points;
    }
}
