<?php




class TaxiTestAdapterFilter extends TaxiFilter
{

    
    public function filter_findStreets_city($city)
    {
        return $this->filterAny_toUpperCase($city);
    }

    
    public function filterAny_coords($coord)
    {
        if (preg_match('/\d*\.\d{5}/', $coord, $m)) {
            return $m[0];
        }
    }

    
    public function filter_createOrder($params)
    {
        return $this->applyFilterRule($params, 'filterAny_coords', array('fromLat', 'fromLon', 'toLat', 'toLon'));
    }

}
