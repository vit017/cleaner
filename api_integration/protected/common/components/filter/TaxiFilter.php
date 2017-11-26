<?php




class TaxiFilter extends TaxiFilterBase
{

    
    public function filterAny_strSafe($str)
    {
        if (is_string($str)) {
            $str = urldecode($str);
            
            $str = mb_strcut($str, 0, 512);
            $str = str_replace('\\', '/', $str);
            $str = trim(strip_tags(stripcslashes($str)));
            //$str = Hc::preg_replace_utf8('/[^A-Za-zА-Яа-яЙйЁёa-zA-ZäöüßÄÖÜẞ\s\d-.,;:+^%*#!()|\/]+/', ' ', $str);
        }
        return $str;
    }

     
    public function filterAny_toUpperCase($str)
    {
        $str = Hc::strtoupper_utf8($str);
        return $str;
    }

    
    public function filterAny_toLowerCase($str)
    {
        $str = Hc::strtolower_utf8($str);
        return $str;
    }

    
    public function filterAny_dateTime($dateTime)
    {
        $dateTime = preg_replace('/(\d{4})[\.-](\d{2})[\.-](\d{2})/U', '$3-$2-$1', $dateTime);
        $time = strtotime($dateTime);
        if ($time) {
            $dateTime = date('d.m.Y H:i:00', $time);
            return trim($dateTime);
        } else {
            return null;
        }
    }

    
    public function filterAny_priorTime($dateTime)
    {
        $dateTime = preg_replace('/(\d{4})[\.-](\d{2})[\.-](\d{2})/U', '$3-$2-$1', $dateTime);
        $time = strtotime($dateTime);
        if ($time) {
            $dateTime = date('d.m.Y H:i:00', $time);
            return trim($dateTime);
        } else {
            return null;
        }
    }

    
    public function filterAny_phone($phone)
    {
        $phone = preg_replace('/[^+\d]/', '', $phone);
        $phone = preg_replace('/^\+7/U', '8', $phone);
        return $phone;
    }

    
    public function filterAnyPhonePlus7($value)
    {
        return preg_replace('/^8/', '+7', $value);
    }

    
    public function filterAnyPhoneTo7($value)
    {
        return preg_replace('/^8/', '7', $value);
    }

    public function filterResult_findStreets($filterData)
    {
        $streets = $filterData->result;
        return $filterData->result = $streets;
    }

}
