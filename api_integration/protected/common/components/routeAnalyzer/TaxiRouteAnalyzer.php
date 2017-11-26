<?php

class TaxiRouteAnalyzer
{
    
    const TIME_MNOZH = 1.3;
    
    const TARIFF_TYPE_CITY = 'CITY';
    
    const TARIFF_TYPE_TRACK = 'TRACK';
    
    const TARIFF_TYPE_AIRPORT_OUT = 'AIRPORT_OUT';
    
    const TARIFF_TYPE_TIME = 'TIME';
    
    const TARIFF_TYPE_DISTANCE = 'DISTANCE';
    
    public $_geocoder;
    
    public $_router;
    
    public $_log;
    
    public $_pointLocation;
    
    public $_dataBaseLayer;
    
    public function init()
    {
    }
    
    public function __construct($tariffDataBaseHost, $tariffDataBasename, $tariffDataBaseUser, $tariffDataBasePassword)
    {
        $this->_geocoder = new TaxiNominatimGeocoder();
        $this->_router = new TaxiOsrmRouter();
        $this->_log = new TaxiLog($this);
        $this->_pointLocation = new TaxiPointLocation();
        $this->_dataBaseLayer = new TaxiRouteDataBaseLayer($tariffDataBaseHost, $tariffDataBasename, $tariffDataBaseUser, $tariffDataBasePassword);
    }
    
    public function getObjectPoligonsFromNominatim($address, $reverse = false)
    {
        $objectPoligons = $this->_geocoder->getObjectPoligons($address, $reverse);
        return $objectPoligons;
    }
    
    public function getBaseObjectPoligons($reverse = false)
    {
        if ($polygon = $this->_dataBaseLayer->getBasePolygon()) {
            $roteData = json_decode($polygon, true);
            $poligons = array();
            if ((isset($roteData['type'])) && (isset($roteData['coordinates']))) {
                $type = $roteData['type'];
                if ($type == "MultiPolygon") {
                    $result = $roteData['coordinates'];
                    $poligons = array();
                    $i = 0;
                    foreach ($result as $res) {
                        foreach ($res[0] as $res2) {
                            $poligons[$i][] = $res2;
                        }
                        $i++;
                    }
                } else if ($type == "Polygon") {
                    $result = $roteData['coordinates'];
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
    
    public function getRoutePoints($fromLat, $fromLon, $toLat, $toLon)
    {
        $pointsArr = $this->_router->getRoutePoints($fromLat, $fromLon, $toLat, $toLon);
        return $pointsArr;
    }
    
    public function getStep($count)
    {
        if ($count <= 10) {
            return 1;
        } else if ($count <= 50) {
            return 1;
        } else if ($count <= 100) {
            return 1;
        } else
            return 1;
    }
    
    public function formatPoligons($polygons)
    {
        $polygonArr = array();
        $i = 0;
        foreach ($polygons as $polygon) {
            foreach ($polygon as $n) {
                $polygonArr[$i][] = $n[0] . ' ' . $n[1];
            }
            $i++;
        }
        return $polygonArr;
    }
    
    public function formatPoints($pointsArr, $toLat, $toLon, $addEndPoint = false)
    {
                        $pointsArr1 = array();
        $count = count($pointsArr);
        $step = $this->getStep($count);
        for ($i = 0; $i < $count - $step; $i = $i + $step) {
            $pointsArr1[] = $pointsArr[$i];
        }
                $points = array();
        foreach ($pointsArr1 as $point) {
            $points[] = $point[0] . ' ' . $point[1];
        }
        if (($addEndPoint == true) && (count($points) > 2)) {
                        $count1 = count($points);
            $endPoint = $toLat . " " . $toLon;
            if ($points[$count1 - 1] !== $endPoint) {
                array_push($points, $endPoint);
            }
        }
        return $points;
    }
    
    public function inlocationArr($polygonArr, $points)
    {
                $inLocation = array();
        $j = 0;
        foreach ($polygonArr as $key => $value) {
            $i = 0;
            foreach ($points as $point) {
                $inLocation[$j][$i]['val'] = $this->_pointLocation->pointStringToCoordinates($point);
                $inLocation[$j][$i]['location'] = $this->_pointLocation->pointInPolygon($point, $polygonArr[$key]);
                $i++;
            }
            $j++;
        }
        return $inLocation;
    }
    
    public function findChangePointsBase($inLocation)
    {
                $changePoints = array();
        $k = 0;
        $numberOfLocaction = 0;
        foreach ($inLocation as $locationArr) {
            for ($i = 0; $i < count($locationArr) - 2; $i++) {
                if ($locationArr[$i]['location'] !== $locationArr[$i + 1]['location']) {
                    $changePoints[$k][0]['coords'] = $locationArr[$i]['val'];
                    $changePoints[$k][0]['loc'] = $locationArr[$i]['location'];
                    $changePoints[$k][1]['coords'] = $locationArr[$i + 1]['val'];
                    $changePoints[$k][1]['loc'] = $locationArr[$i + 1]['location'];
                    $changePoints[$k]['nLoc'] = $numberOfLocaction;
                    $k++;
                }
            }
            $numberOfLocaction++;
        }
        return $changePoints;
    }
    
    public function findChangePoints($changePointsBase, $points, $pointsArr, $polygonArr)
    {
        $position = array();
        $pogranPoints = array();
        $j = 0;
        foreach ($changePointsBase as $node) {
            $point0x = (float) $node['0']['coords'][0];
            $point0y = (float) $node['0']['coords'][1];
            $point0 = array('0' => $point0x, '1' => $point0y);
            $point1x = (float) $node['1']['coords'][0];
            $point1y = (float) $node['1']['coords'][1];
            $point1 = array('0' => $point1x, '1' => $point1y);
            $nLock = $node['nLoc'];
            $k = 0;
            foreach ($pointsArr as $point) {
                if (((string) $point[0] == (string) $point0[0]) && ((string) $point[1] == (string) $point0[1])) {
                    $low = $k;
                }
                if (((string) $point[0] == (string) $point1[0]) && ((string) $point[1] == (string) $point1[1])) {
                    $hight = $k;
                }
                $k++;
            }
            for ($i = $low; $i <= $hight; $i++) {
                $pos[$i] = $this->_pointLocation->pointInPolygon($points[$i], $polygonArr[$nLock]);
                if (($i !== $low) && ($pos[$i] !== $pos[$i - 1])) {
                    $position[$j]['val'] = $this->_pointLocation->pointStringToCoordinates($points[$i]);
                    if ($pos[$i] == 'outside') {
                        $position[$j]['loc'] = 'fromInToOut';
                    } else {
                        $position[$j]['loc'] = 'fromOutToIn';
                    }
                    $j++;
                }
            }
        }
        return $position;
    }
    
    public function findPointsOfIntersection($fromLat, $fromLon, $toLat, $toLon)
    {
                $polygons = $this->getBaseObjectPoligons(true);
        $polygonArr = $this->formatPoligons($polygons);
                $pointsArr = $this->getRoutePoints($fromLat, $fromLon, $toLat, $toLon);
                $points = $this->formatPoints($pointsArr, $toLat, $toLon, true);
                $inLocation = $this->inlocationArr($polygonArr, $points);
                $changePointsBase = $this->findChangePointsBase($inLocation);
        $changePoints = array();
        if (!empty($changePointsBase)) {
                        $points = array();
            foreach ($pointsArr as $point) {
                $points[] = $point[0] . ' ' . $point[1];
            }
            $changePoints = $this->findChangePoints($changePointsBase, $points, $pointsArr, $polygonArr);
        }
        $endPointPos = $this->findPointLocation($toLat, $toLon);
        $endPoint = array(
            'val' => array(
                '0' => $toLat,
                '1' => $toLon,
            ),
            'loc' => $endPointPos,
        );
        array_push($changePoints, $endPoint);
        $startPointPos = $this->findPointLocation($fromLat, $fromLon);
        $startPoint = array(
            'val' => array(
                '0' => $fromLat,
                '1' => $fromLon,
            ),
            'loc' => $startPointPos,
        );
        array_unshift($changePoints, $startPoint);
        return $changePoints;
    }
    
    function getRouteInfo($fromCity, $fromLat, $fromLon, $toLat, $toLon)
    {
        $result = $this->_router->getRouteInfo($fromLat, $fromLon, $toLat, $toLon);
        $this->_log->info("route" . CVarDumper::dumpAsString($result));
        return $result;
    }
    
    public function findPointLocation($lat, $lon)
    {
        $polygons = $this->getBaseObjectPoligons(true);
        $polygonArr = array();
        $i = 0;
        foreach ($polygons as $polygon) {
            foreach ($polygon as $n) {
                $polygonArr[$i][] = $n[0] . ' ' . $n[1];
            }
            $i++;
        }
        $point = array("{$lat} {$lon}");
        $inLocation = array();
        foreach ($polygonArr as $key => $value) {
            $inLocation[] = $this->_pointLocation->pointInPolygon($point['0'], $polygonArr[$key]);
        }
        foreach ($inLocation as $location) {
            if ($location == 'inside') {
                return 'inside';
            } else if (($location == 'boundary') || ($location == 'vertex')) {
                return 'inside';
            }
        }
        return 'outside';
    }
    
    public function findPointLocationInParking($lat, $lon, $parkings)
    {
        $polygonArr = array();
        foreach ($parkings as $key => $value) {
            $polygons = $parkings[$key];
            $i = 0;
            foreach ($polygons as $polygon) {
                foreach ($polygon as $n) {
                    $polygonArr[$key][$i][] = $n[0] . ' ' . $n[1];
                }
                $i++;
            }
        }
        $point = array("{$lat} {$lon}");
        $inLocation = array();
        foreach ($polygonArr as $key => $value) {
            $polygonArr2 = $polygonArr[$key];
            foreach ($polygonArr2 as $key2 => $value2) {
                                if (sizeof($polygonArr2[$key2]) == 1) {
                    $loc = $this->_pointLocation->pointInPoint($point['0'], $polygonArr2[$key2]);
                    if ($loc) {
                        $inLocation[$key] = 'inside';
                    }
                                    } else {
                    $loc = $this->_pointLocation->pointInPolygon($point['0'], $polygonArr2[$key2]);
                    if ($loc == 'inside') {
                        $inLocation[$key] = 'inside';
                    }
                }
            }
        }
        return $inLocation;
    }
    
    public function formatParkings($parkings, $reverse = false)
    {
        $resArr = array();
        foreach ($parkings as $parking) {
            $parkingId = $parking['parking_id'];
            $poligons = array();
            $roteData = json_decode($parking['polygon'], true);
            $type = $roteData['type'];
            if ($type == "MultiPolygon") {
                $result = $roteData['coordinates'];
                $poligons = array();
                $i = 0;
                foreach ($result as $res) {
                    foreach ($res[0] as $res2) {
                        $poligons[$i][] = $res2;
                    }
                    $i++;
                }
            } else if ($type == "Polygon") {
                $result = $roteData['coordinates'];
                $poligons = array();
                foreach ($result['0'] as $res) {
                    $poligons['0'][] = $res;
                }
            } else if ($type == "Point") {
                $result = $roteData['coordinates'];
                $poligons = array();
                foreach ($result as $res) {
                    $poligons['0'][] = $res;
                }
            }
            $resArr[$parkingId] = $poligons;
        }
        if ($reverse == true) {
            $reverse = array();
            foreach ($resArr as $key => $value) {
                $poligons = $resArr[$key];
                $i = 0;
                $j = 0;
                foreach ($poligons as $poligon) {
                                        foreach ($poligon as $poligon2) {
                        $reverse[$key][$i][$j]['0'] = $poligon2['1'];
                        $reverse[$key][$i][$j]['1'] = $poligon2['0'];
                        $j++;
                    }
                    $i++;
                    $j = 0;
                }
            }
            return $reverse;
        }
        return $resArr;
    }
    
    public function getParkings()
    {
        $parkings = $this->_dataBaseLayer->getParkings();
        $parkings = $this->formatParkings($parkings, true);
        return $parkings;
    }
    
    public function getParkingTypeByid($parkingId)
    {
        return $this->_dataBaseLayer->getParkingTypeByid($parkingId);
    }
    
    public function findOneParking($fromPointParkingInArr)
    {
        $parkingId = null;
        $countParkings = count($fromPointParkingInArr);
        if ($countParkings == 1) {
            $parkingId = array_search('inside', $fromPointParkingInArr);
        } else {
            foreach ($fromPointParkingInArr as $key => $value) {
                $parkingType = $this->getParkingTypeByid($key);
                if (($parkingType == 'airport') || ($parkingType == 'station')) {
                    $parkingId = $key;
                }
            }
            if (!$parkingId) {
                reset($fromPointParkingInArr);
                $parkingId = key($fromPointParkingInArr);
            }
        }
        return $parkingId;
    }
    
    public function findFixCost($fromPointParkingId, $toPointParkingId, $fixTariffs)
    {
        foreach ($fixTariffs as $tariff) {
            if (($tariff['from'] == $fromPointParkingId) && ($tariff['to'] == $toPointParkingId)) {
                return $tariff['price_to'];
            } else if (($tariff['from'] == $toPointParkingId) && ($tariff['to'] == $fromPointParkingId)) {
                return $tariff['price_back'];
            }
        }
        return null;
    }
    
    public function analyzeRoute($tenantId, $fromCity, $fromStreet, $fromHouse, $fromHousing, $fromLat, $fromLon, $toCity, $toStreet, $toHouse, $toHousing, $toLat, $toLon, $additional, $tariffGroupId, $isDay, $needDecompozeRoute)
    {
        $this->_dataBaseLayer->setTenantId($tenantId);
                $coords = $this->findCoords($fromCity, $fromStreet, $fromHouse, $fromHousing, $fromLat, $fromLon, $toCity, $toStreet, $toHouse, $toHousing, $toLat, $toLon);
        $fromLat = $coords['fromLat'];
        $fromLon = $coords['fromLon'];
        $toLat = $coords['toLat'];
        $toLon = $coords['toLon'];
                $needBaseAnalyze = true;
                $airportOutToOutAnalyze = false;
                $airportOutToOutSpecialAnalyze = false;
                $routeInfo = array(
            'summaryTime'     => null,
            'summaryDistance' => null,
            'summaryCost'     => null,
            'cityTime'        => null,
            'cityDistance'    => null,
            'cityCost'        => null,
            'outCityTime'     => null,
            'outCityDistance' => null,
            'outCityCost'     => null,
            'isFix'           => 0,
        );
                $fixTariffs = $this->findFixTariffs($tariffGroupId);
        if ($fixTariffs) {
                        $parkings = $this->getParkings();
                        $fromPointParkingInArr = $this->findPointLocationInParking($fromLat, $fromLon, $parkings);
                        $toPointParkingInArr = $this->findPointLocationInParking($toLat, $toLon, $parkings);
                        if (!empty($fromPointParkingInArr) && !empty($toPointParkingInArr)) {
                                $fromPointParkingId = $this->findOneParking($fromPointParkingInArr);
                $toPointParkingId = $this->findOneParking($toPointParkingInArr);
                $cost = $this->findFixCost($fromPointParkingId, $toPointParkingId, $fixTariffs);
                                if (!empty($cost)) {
                    $routeData = $this->getRouteInfo($fromCity, $fromLat, $fromLon, $toLat, $toLon);
                    $routeInfo['summaryTime'] = round(($routeData['time'] / 60) * $this::TIME_MNOZH, 1);
                    $routeInfo['summaryDistance'] = round($routeData['distance'] / 1000, 1);
                    $routeInfo['summaryCost'] = $cost;
                    $routeInfo['isFix'] = 1;
                    $needBaseAnalyze = false;
                } else {
                                        $needBaseAnalyze = true;
                }
                            } else if (!empty($fromPointParkingInArr) || !empty($toPointParkingInArr)) {
                $airportOutToOutAnalyze = false;
                $fromPointParkingId = $this->findOneParking($fromPointParkingInArr);
                $toPointParkingId = $this->findOneParking($toPointParkingInArr);
                $fromPointType = $this->getParkingTypeByid($fromPointParkingId);
                $toPointType = $this->getParkingTypeByid($toPointParkingId);
                                if (($fromPointType == 'airport') && (empty($toPointType))) {
                    $airportOutToOutAnalyze = true;
                    $needBaseAnalyze = false;
                    $airportWay = 'fromAirport';
                } else if ((empty($fromPointType)) && ($toPointType == 'airport')) {
                    $airportOutToOutAnalyze = true;
                    $needBaseAnalyze = false;
                    $airportWay = 'toAirport';
                }
            }
        }
                if ($airportOutToOutAnalyze) {
            $pointFromLocation = $this->findPointLocation($fromLat, $fromLon);
                        $changePoints = $this->findPointsOfIntersection($fromLat, $fromLon, $toLat, $toLon);
                        if (count($changePoints) == 2) {
                $airportOutToOutSpecialAnalyze = true;
                $routeData = $this->getRouteInfo($fromCity, $fromLat, $fromLon, $toLat, $toLon);
                $routeInfo['outCityTime'] = round(($routeData['time'] / 60) * $this::TIME_MNOZH, 1);
                $routeInfo['outCityDistance'] = round($routeData['distance'] / 1000, 1);
                $routeInfo['summaryTime'] = $routeInfo['outCityTime'];
                $routeInfo['summaryDistance'] = $routeInfo['outCityDistance'];
                $costInfo = $this->calcCost($routeInfo['summaryTime'], $routeInfo['summaryDistance'], $routeInfo['cityTime'], $routeInfo['cityDistance'], $routeInfo['outCityTime'], $routeInfo['outCityDistance'], $tariffGroupId, $isDay, $pointFromLocation, $airportOutToOutSpecialAnalyze);
                $routeInfo['summaryCost'] = $costInfo['summaryCost'];
                $routeInfo['outCityCost'] = $costInfo['summaryCost'];
                            } if (count($changePoints) > 2) {
                $airportOutToOutSpecialAnalyze = false;
                if ($airportWay == 'toAirport') {
                                        $changePointAirportWay = $changePoints[1];
                    $changePointX = $changePointAirportWay['val']['0'];
                    $changePointY = $changePointAirportWay['val']['1'];
                                        $routeData = $this->getRouteInfo($fromCity, $fromLat, $fromLon, $changePointX, $changePointY);
                                        $routeInfo['outCityTime'] = round(($routeData['time'] / 60) * $this::TIME_MNOZH, 1);
                    $routeInfo['outCityDistance'] = round($routeData['distance'] / 1000, 1);
                    $costInfo = $this->calcCost($routeInfo['summaryTime'], $routeInfo['summaryDistance'], $routeInfo['cityTime'], $routeInfo['cityDistance'], $routeInfo['outCityTime'], $routeInfo['outCityDistance'], $tariffGroupId, $isDay, $pointFromLocation, $airportOutToOutSpecialAnalyze);
                    $routeInfo['outCityCost'] = $costInfo['outCityCost'];
                                        $changePointParkingInArr = $this->findPointLocationInParking($changePointX, $changePointY, $parkings);
                    $changePointParkingId = $this->findOneParking($changePointParkingInArr);
                                        $cost = $this->findFixCost($changePointParkingId, $toPointParkingId, $fixTariffs);
                    if (!empty($cost)) {
                        $routeData = $this->getRouteInfo($fromCity, $changePointX, $changePointY, $toLat, $toLon);
                        $routeInfo['cityTime'] = round(($routeData['time'] / 60) * $this::TIME_MNOZH, 1);
                        $routeInfo['cityDistance'] = round($routeData['distance'] / 1000, 1);
                        $routeInfo['cityCost'] = $cost;
                        $routeInfo['isFix'] = 0;
                    }
                    $routeInfo['summaryTime'] = round($routeInfo['cityTime'] + $routeInfo['outCityTime'], 1);
                    $routeInfo['summaryDistance'] = round($routeInfo['cityDistance'] + $routeInfo['outCityDistance'], 1);
                    $routeInfo['summaryCost'] = $routeInfo['cityCost'] + $routeInfo['outCityCost'];
                }
                if ($airportWay == 'fromAirport') {
                                        $changePointAirportWay = $changePoints[count($changePoints) - 2];
                    $changePointX = $changePointAirportWay['val']['0'];
                    $changePointY = $changePointAirportWay['val']['1'];
                                        $changePointParkingInArr = $this->findPointLocationInParking($changePointX, $changePointY, $parkings);
                    $changePointParkingId = $this->findOneParking($changePointParkingInArr);
                                        $cost = $this->findFixCost($fromPointParkingId, $changePointParkingId, $fixTariffs);
                    if (!empty($cost)) {
                        $routeData = $this->getRouteInfo($fromCity, $fromLat, $fromLon, $changePointX, $changePointY);
                        $routeInfo['cityTime'] = round(($routeData['time'] / 60) * $this::TIME_MNOZH, 1);
                        $routeInfo['cityDistance'] = round($routeData['distance'] / 1000, 1);
                        $routeInfo['cityCost'] = $cost;
                        $routeInfo['isFix'] = 0;
                    }
                                        $routeData = $this->getRouteInfo($fromCity, $changePointX, $changePointY, $toLat, $toLon);
                    $routeInfo['outCityTime'] = round(($routeData['time'] / 60) * $this::TIME_MNOZH, 1);
                    $routeInfo['outCityDistance'] = round($routeData['distance'] / 1000, 1);
                    $costInfo = $this->calcCost($routeInfo['summaryTime'], $routeInfo['summaryDistance'], $routeInfo['cityTime'], $routeInfo['cityDistance'], $routeInfo['outCityTime'], $routeInfo['outCityDistance'], $tariffGroupId, $isDay, $pointFromLocation, $airportOutToOutSpecialAnalyze);
                    $routeInfo['outCityCost'] = $costInfo['outCityCost'];
                    $routeInfo['summaryTime'] = round($routeInfo['cityTime'] + $routeInfo['outCityTime'], 1);
                    $routeInfo['summaryDistance'] = round($routeInfo['cityDistance'] + $routeInfo['outCityDistance'], 1);
                    $routeInfo['summaryCost'] = $routeInfo['cityCost'] + $routeInfo['outCityCost'];
                }
            }
        }
                else if ($needBaseAnalyze) {
                                    $pointFromLocation = $this->findPointLocation($fromLat, $fromLon);
            $this->_log->info("pointFromLocation" . CVarDumper::dumpAsString($pointFromLocation));
            $pointToLocation = $this->findPointLocation($toLat, $toLon);
            $this->_log->info("pointToLocation" . CVarDumper::dumpAsString($pointToLocation));
                                                            if (!$needDecompozeRoute) {
                $routeData = $this->getRouteInfo($fromCity, $fromLat, $fromLon, $toLat, $toLon);
                if (($pointFromLocation == 'outside') || ($pointToLocation == 'outside')) {
                    $routeInfo['outCityTime'] +=$routeData['time'];
                    $routeInfo['outCityDistance'] +=$routeData['distance'];
                } else {
                    $routeInfo['cityTime'] +=$routeData['time'];
                    $routeInfo['cityDistance'] +=$routeData['distance'];
                }
            }
            if ($needDecompozeRoute) {
                                $changePoints = $this->findPointsOfIntersection($fromLat, $fromLon, $toLat, $toLon);
                for ($i = 0; $i < count($changePoints) - 1; $i++) {
                    $fromLat = $changePoints[$i]['val']['0'];
                    $fromLon = $changePoints[$i]['val']['1'];
                    $toLat = $changePoints[$i + 1]['val']['0'];
                    $toLon = $changePoints[$i + 1]['val']['1'];
                    $routeData = $this->getRouteInfo($fromCity, $fromLat, $fromLon, $toLat, $toLon);
                    if (($changePoints[$i]['loc'] == 'inside') || ($changePoints[$i]['loc'] == 'fromOutToIn')) {
                        $routeInfo['cityTime'] +=$routeData['time'];
                        $routeInfo['cityDistance'] +=$routeData['distance'];
                    } else {
                        $routeInfo['outCityTime'] +=$routeData['time'];
                        $routeInfo['outCityDistance'] +=$routeData['distance'];
                    }
                }
            }
            $routeInfo['cityTime'] = round(($routeInfo['cityTime'] / 60) * $this::TIME_MNOZH, 1);
            $routeInfo['cityDistance'] = round($routeInfo['cityDistance'] / 1000, 1);
            $routeInfo['outCityTime'] = round(($routeInfo['outCityTime'] / 60) * $this::TIME_MNOZH, 1);
            $routeInfo['outCityDistance'] = round($routeInfo['outCityDistance'] / 1000, 1);
            $routeInfo['summaryTime'] = round($routeInfo['cityTime'] + $routeInfo['outCityTime'], 1);
            $routeInfo['summaryDistance'] = round($routeInfo['cityDistance'] + $routeInfo['outCityDistance'], 1);
            $costInfo = $this->calcCost($routeInfo['summaryTime'], $routeInfo['summaryDistance'], $routeInfo['cityTime'], $routeInfo['cityDistance'], $routeInfo['outCityTime'], $routeInfo['outCityDistance'], $tariffGroupId, $isDay, $pointFromLocation, $airportOutToOutSpecialAnalyze);
            $routeInfo['summaryCost'] = $costInfo['summaryCost'];
            $routeInfo['cityCost'] = $costInfo['cityCost'];
            $routeInfo['outCityCost'] = $costInfo['outCityCost'];
        }
                if (!empty($additional)) {
            foreach ($additional as $add) {
                $addCost = $this->getAdditionalCost($add, $tariffGroupId);
                $routeInfo['summaryCost']+= $addCost;
            }
        }
        $this->_log->info("routeInfo" . CVarDumper::dumpAsString($routeInfo));
        return $routeInfo;
    }
    
    public function getAdditionalCost($add, $tariffGroupId)
    {
        $addCost = $this->_dataBaseLayer->getAdditionalCost($add, $tariffGroupId);
        return !empty($addCost) ? $addCost : 0;
    }
    
    public function findCoordsByAddress($city, $street, $house, $housing)
    {
        $result = array();
        $result['lat'] = null;
        $result['lon'] = null;
        $address = $city . ',' . $street . ',' . $house . ',' . $housing;
        $geoData = $this->_geocoder->findCoordsByAddress($address);
        return $result;
    }
    
    public function findCoords($fromCity, $fromStreet, $fromHouse, $fromHousing, $fromLat, $fromLon, $toCity, $toStreet, $toHouse, $toHousing, $toLat, $toLon)
    {
        $result = array();
        if ((!empty($fromLat)) && (!empty($fromLon))) {
            $result['fromLat'] = $fromLat;
            $result['fromLon'] = $fromLon;
        } else {
            $pointFromData = $this->findCoordsByAddress($fromCity, $fromStreet, $fromHouse, $fromHousing);
            $result['fromLat'] = isset($pointFromData['fromLat']) ? $pointFromData['fromLat'] : null;
            $result['fromLon'] = isset($pointFromData['fromLon']) ? $pointFromData['fromLon'] : null;
        }
        if ((!empty($toLat)) && (!empty($toLon))) {
            $result['toLat'] = $toLat;
            $result['toLon'] = $toLon;
        } else {
            $pointToData = $this->findCoordsByAddress($toCity, $toStreet, $toHouse, $toHousing);
            $result['toLat'] = isset($pointToData['toLat']) ? $pointToData['toLat'] : null;
            $result['toLon'] = isset($pointToData['toLon']) ? $pointToData['toLon'] : null;
        }
        return $result;
    }
    
    public function calcCostByTime($cityTime, array $tariffData, $isDay, $isRouteBreaked, $pointFromLocation, $callFor)
    {
        $cost = null;
        if ($isDay == '1') {
            $planting = !empty($tariffData['planting_price_day']) ? $tariffData['planting_price_day'] : 0;
            $included = !empty($tariffData['planting_include_day']) ? $tariffData['planting_include_day'] : 0;
            $nextMin = !empty($tariffData['next_km_price_day']) ? $tariffData['next_km_price_day'] : 0;
            $minPrice = !empty($tariffData['min_price_day']) ? $tariffData['min_price_day'] : 0;
            $rounding = !empty($tariffData['rounding_day']) ? $tariffData['rounding_day'] : 0;
        } else {
            $planting = !empty($tariffData['planting_price_night']) ? $tariffData['planting_price_night'] : 0;
            $included = !empty($tariffData['planting_price_night']) ? $tariffData['planting_price_night'] : 0;
            $nextMin = !empty($tariffData['next_km_price_night']) ? $tariffData['next_km_price_night'] : 0;
            $minPrice = !empty($tariffData['min_price_night']) ? $tariffData['min_price_night'] : 0;
            $rounding = !empty($tariffData['rounding_night']) ? $tariffData['rounding_night'] : 0;
        }
                if ($isRouteBreaked) {
                        if (($pointFromLocation == 'inside') && ($callFor == 'city')) {
            } else
                        if (($pointFromLocation == 'outside') && ($callFor == 'outCity')) {
            } else {
                
            }
        }
        if ($cityTime > $included) {
            $cost = $planting + ($cityTime - $included) * $nextMin;
        } else {
            $cost = $planting;
        }
        if ($minPrice > $cost) {
            $cost = $minPrice;
        }
        if (!empty($rounding)) {
            $cost = ceil($cost / $rounding) * $rounding;
        }
        return $cost;
    }
    
    public function calcCostByDistance($cityDistance, array $tariffData, $isDay, $isRouteBreaked, $pointFromLocation, $callFor)
    {
        $cost = null;
        if ($isDay == '1') {
            $planting = !empty($tariffData['planting_price_day']) ? $tariffData['planting_price_day'] : 0;
            $included = !empty($tariffData['planting_include_day']) ? $tariffData['planting_include_day'] : 0;
            $nextKm = !empty($tariffData['next_km_price_day']) ? $tariffData['next_km_price_day'] : 0;
            $minPrice = !empty($tariffData['min_price_day']) ? $tariffData['min_price_day'] : 0;
            $rounding = !empty($tariffData['rounding_day']) ? $tariffData['rounding_day'] : 0;
        } else {
            $planting = !empty($tariffData['planting_price_night']) ? $tariffData['planting_price_night'] : 0;
            $included = !empty($tariffData['planting_price_night']) ? $tariffData['planting_price_night'] : 0;
            $nextKm = !empty($tariffData['next_km_price_night']) ? $tariffData['next_km_price_night'] : 0;
            $minPrice = !empty($tariffData['min_price_night']) ? $tariffData['min_price_night'] : 0;
            $rounding = !empty($tariffData['rounding_night']) ? $tariffData['rounding_night'] : 0;
        }
                if ($isRouteBreaked) {
                        if (($pointFromLocation == 'inside') && ($callFor == 'city')) {
            } else
                        if (($pointFromLocation == 'outside') && ($callFor == 'outCity')) {
            } else {
                
            }
        }
        if ($cityDistance > $included) {
            $cost = $planting + ($cityDistance - $included) * $nextKm;
        } else {
            $cost = $planting;
        }
        if ($minPrice > $cost) {
            $cost = $minPrice;
        }
        if (!empty($rounding)) {
            $cost = ceil($cost / $rounding) * $rounding;
        }
        return $cost;
    }
    
    public function calcCost($summaryTime, $summaryDistance, $cityTime, $cityDistance, $outCityTime, $outCityDistance, $tariffGroupId, $isDay, $pointFromLocation, $airportOutToOutSpecialAnalyze)
    {
        $costInfo = array(
            'summaryCost' => null,
            'cityCost'    => null,
            'outCityCost' => null,
        );
        $isRouteBreaked = false;
        if (!empty($cityTime) && !empty($outCityTime)) {
            $isRouteBreaked = true;
        }
        $rounding = !empty($tariffData['rounding_day']) ? $tariffData['rounding_day'] : 0;
                if (!empty($cityTime) && !empty($cityDistance)) {
            $costInCity = null;
            $zone = $this::TARIFF_TYPE_CITY;
            $tariffData = $this->getTariffData($tariffGroupId, $zone);
            if (!empty($tariffData)) {
                                if ($tariffData['accrual'] == $this::TARIFF_TYPE_TIME) {
                    $costInCity = $this->calcCostByTime($cityTime, $tariffData, $isDay, $isRouteBreaked, $pointFromLocation, $callFor = 'city');
                } else if ($tariffData['accrual'] == $this::TARIFF_TYPE_DISTANCE) {
                                        $costInCity = $this->calcCostByDistance($cityDistance, $tariffData, $isDay, $isRouteBreaked, $pointFromLocation, $callFor = 'city');
                }
            }
            $costInfo['cityCost'] = $costInCity;
        }
                if (!empty($outCityTime) && !empty($outCityDistance)) {
            $outCityCost = null;
            if ($airportOutToOutSpecialAnalyze) {
                $zone = $this::TARIFF_TYPE_AIRPORT_OUT;
            } else {
                $zone = $this::TARIFF_TYPE_TRACK;
            }
            $tariffData = $this->getTariffData($tariffGroupId, $zone);
            if (!empty($tariffData)) {
                                if ($tariffData['accrual'] == $this::TARIFF_TYPE_TIME) {
                    $outCityCost = $this->calcCostByTime($outCityTime, $tariffData, $isDay, $isRouteBreaked, $pointFromLocation, $callFor = 'outCity');
                } else if ($tariffData['accrual'] == $this::TARIFF_TYPE_DISTANCE) {
                                        $outCityCost = $this->calcCostByDistance($outCityDistance, $tariffData, $isDay, $isRouteBreaked, $pointFromLocation, $callFor = 'outCity');
                }
            }
            $costInfo['outCityCost'] = $outCityCost;
        }
        $costInfo['summaryCost'] = $costInfo['outCityCost'] + $costInfo['cityCost'];
        return $costInfo;
    }
    
    public function getTariffData($tariffGroupId, $zone)
    {
        $tariffData = $this->_dataBaseLayer->getTariffData($tariffGroupId, $zone);
        return $tariffData;
    }
    
    public function findFixTariffs($tariffGroupId)
    {
        return $this->_dataBaseLayer->findFixTariffs($tariffGroupId);
    }
}
