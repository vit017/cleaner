<?php

class TaxiPointLocation
{
    var $pointOnVertex = true;
    function pointLocation()
    {
    }
    
    function pointInPoint($point, $point2)
    {
        $point = $this->pointStringToCoordinates($point);
        $point2 = $this->pointStringToCoordinates($point2[0]);
        $pointX = round($point[0], 2);
        $pointY = round($point[1], 2);
        $point2X = round($point2[0], 2);
        $point2Y = round($point2[1], 2);
        if (($pointX == $point2X ) && ( $pointY == $point2Y )) {
            return true;
        }
        return false;
    }
    
    function pointInPolygon($point, $polygon, $pointOnVertex = true)
    {
        $this->pointOnVertex = $pointOnVertex;
        $point = $this->pointStringToCoordinates($point);
        $vertices = array();
        foreach ($polygon as $vertex) {
            $vertices[] = $this->pointStringToCoordinates($vertex);
        }
        if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
            return "vertex";
        }
        $intersections = 0;
        $vertices_count = count($vertices);
        for ($i = 1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i - 1];
            $vertex2 = $vertices[$i];
            if ($vertex1['1'] == $vertex2['1'] and $vertex1['1'] == $point['1'] and $point['0'] > min($vertex1['0'], $vertex2['0']) and $point['0'] < max($vertex1['0'], $vertex2['0'])) {                 return "boundary";
            }
            if ($point['1'] > min($vertex1['1'], $vertex2['1']) and $point['1'] <= max($vertex1['1'], $vertex2['1']) and $point['0'] <= max($vertex1['0'], $vertex2['0']) and $vertex1['1'] != $vertex2['1']) {
                $xinters = ($point['1'] - $vertex1['1']) * ($vertex2['0'] - $vertex1['0']) / ($vertex2['1'] - $vertex1['1']) + $vertex1['0'];
                if ($xinters == $point['0']) {
                    return "boundary";
                }
                if ($vertex1['0'] == $vertex2['0'] || $point['0'] <= $xinters) {
                    $intersections++;
                }
            }
        }
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }
    
    function pointOnVertex($point, $vertices)
    {
        foreach ($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }
    }
    
    function pointStringToCoordinates($pointString)
    {
        $coordinates = explode(" ", $pointString);
        return array("0" => $coordinates[0], "1" => $coordinates[1]);
    }
    
    function pointCoordsToString($point)
    {
        return $point['0'] . " " . $point['1'];
    }
}
