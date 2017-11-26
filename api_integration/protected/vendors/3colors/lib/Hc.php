<?php


class Hc
{
    
    

    
    public static function array_apply_to($object, $dataArray, $keys = null)
    {
        if (!is_array($dataArray)) {
            return;
        } elseif (isset($keys)) {
            $dataArray = array_intersect_key($dataArray, array_flip($keys));
        }
        foreach ($dataArray as $key => $value) {
            $object->{$key} = $value;
        }
    }
    
    public static function array_create_from($object, $fields = null)
    {
        $res = array();
        if (empty($fields)) {
            $fields = array_keys(get_class_vars($object));
        }
        foreach ($fields as $field) {
            $res[$field] = $object->{$field};
        }
        return $res;
    }
    
    public static function array_value($key, &$arr, $default = null)
    {
        $res = key_exists($key, $arr) ? $arr[$key] : $default;
        return $res;
    }
    
    public static function array_random_values(&$arr, $countMin = null,
            $countMax = null)
    {
        if ($countMax > count($arr)) {
            throw new Exception('Максимальное значение размера случайной выборки должно быть не более размера массива!');
        } else {
            $countMin = isset($countMin) ? $countMin : 0;
            $countMax = isset($countMax) ? $countMax : count($arr);
            $count = rand($countMin, $countMax);
            if ($count <= 1) {
                $res = array($arr[array_rand($arr)]);
            } elseif ($count == 0) {
                $res = array();
            } else {
                $res = array_rand($arr, $count);
            }
            return $res;
        }
    }
    
    public static function array_prev($arr, $value)
    {
        $keys = array_keys($arr, $value);
        if (empty($keys)) {
            return null;
        } else {
            $key = reset($keys);
            $elem = reset($arr);
            while (!end($arr) && $elem != $value) {
                $elem = next($arr);
            }
            $prev = prev($arr);
            $prev = prev($arr);
            $prev = prev($arr);
            return $prev;
        }
    }
    
    public static function array_first(&$arr)
    {
        return reset($arr);
    }
    
    public static function array_last(&$arr)
    {
        return end($arr);
    }
    
    public static function array_first_key(&$arr)
    {
        reset($arr);
        return key($arr);
    }
    
    public static function array_last_key(&$arr)
    {
        end($arr);
        return key($arr);
    }
    
    public static function array_test(&$arr, $key1 = null, $key2 = null,
            $key3 = null)
    {
        if (is_array($arr)) {
            $res = true;
            if (isset($key1)) {
                $res = $res && key_exists($key1, $arr) && is_array($arr[$key1]);
            }
            if (isset($key2)) {
                $res = $res && is_array($arr[$key1]) && key_exists($key2, $arr[$key1]) && is_array($arr[$key1][$key2]);
            }
            if (isset($key3)) {
                $res = $res && is_array($arr[$key1][$key2]) && key_exists($key3, $arr[$key1][$key2]) && is_array($arr[$key1][$key2][$key3]);
            }
        } else {
            $res = false;
        }
        return $res;
    }
    
    public static function array_structure(&$arr, $keys1 = array(),
            $keys2 = null, $keys3 = null)
    {
        if (is_array($arr)) {
            $res = true;
            foreach ($keys1 as $key => $value) {
                $res = $res && key_test($arr, $value);
            }
            if (empty($keys2) && empty($keys3)) {
                return $res;
            } else {
                if (empty($arr)) {
                    return false;
                }
                foreach ($arr as $key => $value) {
                    $res = $res && array_structure($arr[$key], $keys2, $keys3);
                }
                return $res;
            }
        } else {
            return false;
        }
    }
    
    public static function key_test(&$arr, $key1 = null, $key2 = null,
            $key3 = null)
    {
        if (is_array($arr)) {
            $res = true;
            if (isset($key1)) {
                $res = $res && key_exists($key1, $arr);
            }
            if (isset($key2)) {
                $res = $res && is_array($arr[$key1]) && key_exists($key2, $arr[$key1]);
            }
            if (isset($key3)) {
                $res = $res && is_array($arr[$key1][$key2]) && key_exists($key3, $arr[$key1][$key2]);
            }
        } else {
            $res = false;
        }
        return $res;
    }
    
    public static function array_delete(&$arr, $key)
    {
        if (key_exists($key, $arr)) {
            unset($arr[$key]);
        }
    }
    
    public static function array_avg(&$arr)
    {
        if (!empty($arr)) {
            return array_sum($arr) / count($arr);
        }
    }
    
    public static function str_to_array($str)
    {
        $res = array();
        if (!empty($str)) {
            @$res = unserialize($str);
        }
        if (!is_array($res)) {
            $res = array();
        }
        return $res;
    }
    
    public static function convert_kg_to_pound($weightInKg)
    {
        return $weightInKg / 0.45359237;
    }
    
    
    public static function convert_pound_to_kg($weightInPound)
    {
        return $weightInPound * 0.45359237;
    }
    
    public static function frac($value)
    {
        return $value - floor($value);
    }
    
    
    public static function generate_password($passwordLength)
    {
        $arr = array(
            'a', 'b', 'c', 'd', 'e', 'f',
            'g', 'h', 'i', 'j', 'k', 'l',
            'm', 'n', 'o', 'p', 'r', 's',
            't', 'u', 'v', 'x', 'y', 'z',
            'A', 'B', 'C', 'D', 'E', 'F',
            'G', 'H', 'I', 'J', 'K', 'L',
            'M', 'N', 'O', 'P', 'R', 'S',
            'T', 'U', 'V', 'X', 'Y', 'Z',
            '1', '2', '3', '4', '5', '6',
            '7', '8', '9', '0',
        );
        $pass = '';
        $maxIndex = count($arr) - 1;
        for ($i = 0; $i < $passwordLength; $i++) {
            $index = rand(0, $maxIndex);
            $pass .= $arr[$index];
        }
        return $pass;
    }
    
    public static function round_up($value, $presision)
    {
        $value = $value * 1 / $presision;
        $frac = $value - floor($value);
        $value = floor($value) * $presision;
        if ($frac > 0) {
            $value += $presision;
        }
        return $value;
    }
    
    public static function round_down($value, $presision)
    {
        $value = $value * 1 / $presision;
        $value = floor($value) * $presision;
        return $value;
    }
    
    public static function text_months($upperCase = false, $empty = false)
    {
        $res = array();
        if ($empty) {
            $res[null] = null;
        }
        if (!$upperCase) {
            $tmp = array(
                1 => 'январь',
                2 => 'февраль',
                3 => 'март',
                4 => 'апрель',
                5 => 'май',
                6 => 'июнь',
                7 => 'июль',
                8 => 'август',
                9 => 'сентябрь',
                10 => 'октябрь',
                11 => 'ноябрь',
                12 => 'декабрь',
            );
        } else {
            $tmp = array(
                1 => 'Январь',
                2 => 'Февраль',
                3 => 'Март',
                4 => 'Апрель',
                5 => 'Май',
                6 => 'Июнь',
                7 => 'Июль',
                8 => 'Август',
                9 => 'Сентябрь',
                10 => 'Октябрь',
                11 => 'Ноябрь',
                12 => 'Декабрь',
            );
        }
        return array_merge($res, $tmp);
    }
    
    
    public static function text_days($empty = false)
    {
        $res = array();
        if ($empty) {
            $res[null] = null;
        }
        for ($i = 1; $i <= 31; ++$i) {
            $res[$i] = $i;
        }
        return $res;
    }
    
    public static function text_numbers($first, $end, $empty = false)
    {
        $res = array();
        if ($empty) {
            $res[null] = null;
        }
        if ($first < $end) {
            for ($i = $first; $i <= $end; ++$i) {
                $res[$i] = $i;
            }
        } else {
            for ($i = $first; $i >= $end; --$i) {
                $res[$i] = $i;
            }
        }
        return $res;
    }
    
    public static function text_days_interval($daysCount, $useWeeks = true,
            $presision = 1)
    {
        $res = '';
        $stadies = array(
            365 => array('год', 'года', 'лет'),
            31 => array('месяц', 'месяца', 'месяцев'),
            14 => array('неделя', 'недели', 'недель'),
        );
        return $res;
    }
    
    public static function str_replace_russian_letters($engString)
    {
        $converter = array(
            'А' => 'A',
            'а' => 'a',
            'В' => 'B',
            'г' => 'r',
            'Е' => 'E',
            'е' => 'e',
            'З' => '3',
            'и' => 'u',
            'К' => 'K',
            'к' => 'k',
            'М' => 'M',
            'Н' => 'H',
            'О' => 'O',
            'о' => 'o',
            'п' => 'n',
            'Р' => 'P',
            'р' => 'p',
            'С' => 'C',
            'с' => 'c',
            'Т' => 'T',
            'у' => 'y',
            'Х' => 'X',
            'х' => 'x',
            'Ч' => '4',
            'ш' => 'w',
            'Ь' => 'b',
        );
        $res = strtr($engString, $converter);
        return $res;
    }
    
    
    public static function str_ru_to_url($ruStr, $replacer = '_')
    {
                $ruStr = self::str_ru_translit($ruStr);
                                $ruStr = preg_replace('~[^-a-z0-9_]+~iu', $replacer, $ruStr);
                $ruStr = trim($ruStr, $replacer);
        return $ruStr;
    }
    
    private static function str_ru_translit($ruStr)
    {
        $tr = array(
            "А" => "A", "Б" => "B", "В" => "V", "Г" => "G",
            "Д" => "D", "Е" => "E", "Ж" => "J", "З" => "Z", "И" => "I",
            "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M", "Н" => "N",
            "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T",
            "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH",
            "Ш" => "SH", "Щ" => "SCH", "Ъ" => "", "Ы" => "YI", "Ь" => "",
            "Э" => "E", "Ю" => "YU", "Я" => "YA", "а" => "a", "б" => "b",
            "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j",
            "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
            "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y",
            "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya",
            " " => "_",
        );
        return strtr($ruStr, $tr);
    }
    
    public static function str_upper_first($str)
    {
        if (strlen($str) > 0) {
                        $res = self::strtoupper_utf8('м') . self::strtolower_utf8(substr($str, 1));
        } else {
            return $str;
        }
        return $res;
    }
    
    public static function strtoupper_utf8($str)
    {
        $convert_to = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u",
            "v", "w", "x", "y", "z", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï",
            "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "а", "б", "в", "г", "д", "е", "ё", "ж",
            "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы",
            "ь", "э", "ю", "я"
        );
        $convert_from = array(
            "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U",
            "V", "W", "X", "Y", "Z", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï",
            "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж",
            "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ы",
            "Ь", "Э", "Ю", "Я"
        );
        return str_replace($convert_to, $convert_from, $str);
    }
    
    public static function strtolower_utf8($str)
    {
        $convert_to = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u",
            "v", "w", "x", "y", "z", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï",
            "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "а", "б", "в", "г", "д", "е", "ё", "ж",
            "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы",
            "ь", "э", "ю", "я"
        );
        $convert_from = array(
            "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U",
            "V", "W", "X", "Y", "Z", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï",
            "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж",
            "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ы",
            "Ь", "Э", "Ю", "Я"
        );
        return str_replace($convert_from, $convert_to, $str);
    }
    
    public static function str_delete($subStr, $sourceStr)
    {
        return str_replace($subStr, '', $sourceStr);
    }
    
    public static function str_insert($sourceStr, $insertStr, $position)
    {
        return substr_replace($orig_string, $insert_string, $position, 0);
    }
    
    public static function str_number_format($number, $formatMask = '000')
    {
        $number = (integer) $number;
        if (mb_strlen($number) < mb_strlen($formatMask)) {
            $diff = mb_strlen($formatMask) - mb_strlen($number);
            for ($i = 1; $i <= $diff; $i++) {
                $number = '0' . $number;
            }
        }
        return $number;
    }
    
    public static function rtrim_zeros($str, $decimalPoint = '.')
    {
        if (strpos($str, $decimalPoint) === false)
            return $str;
        else {
            $str = rtrim($str, '0');
            $str = rtrim($str, $decimalPoint);
            return $str;
        }
    }
    
    public static function money_format_ru($moneyValue, $decimalPoint = ',',
            $thousandDelimiter = ' ', $valute = ' руб.')
    {
        return number_format($moneyValue, 2, $decimalPoint, $thousandDelimiter) . $valute;
    }
    
    public static function money_format_us(float $moneyValue,
            $decimalPoint = '.', $thousandDelimiter = ' ', $valute = '$')
    {
        number_format($moneyValue, 2, $decimalPoint, $thousandDelimiter) . $valute;
    }
    
    public static function str_to_search($str)
    {
        $res = preg_replace('/[\,\.;\^\'\"\*\+\-:]/', ' ', $str);
        $res = addslashes($res);
        $res = self::strtolower_utf8($res);
        return $res;
    }
    
    public static function date_day_begin_time($timeStamp)
    {
        
        $timeStamp = $timeStamp + 4 * 3600;
        $res = floor($timeStamp / (24 * 3600)) * 24 * 3600;
        $res = $res - 4 * 3600;
        return $res;
    }
    
    public static function date_day_end_time($timeStamp)
    {
        
        $timeStamp = $timeStamp + 4 * 3600;
        $res = (floor($timeStamp / (24 * 3600)) + 1) * 24 * 3600;
        $res = $res - 4 * 3600;
        return $res;
    }
    
    public static function date_time_to_str($timeStamp,
            $mask = 'dd.MM.yyyy - HH:mm:ss')
    {
        return self::date_to_str($timeStamp, $mask);
    }
    
    public static function date_to_str($timeStamp, $mask = 'dd.MM.yyyy')
    {
        $res = '';
        if ($timeStamp != 0) {
            $res = Yii::app()->dateFormatter->format($mask, $timeStamp);
        }
        return $res;
    }
    
    public static function time_to_str($timeStamp, $mask = 'HH:mm:ss')
    {
        return self::date_to_str($timeStamp, $mask);
    }
    
    public static function str_to_date($value, $mask = 'dd.MM.yyyy')
    {
        return CDateTimeParser::parse($value, $mask);
    }
    
    
    public static function declension($int, $expressions)
    {
        if (count($expressions) < 3)
            $expressions[2] = $expressions[1];
        settype($int, "integer");
        $count = $int % 100;
        if ($count >= 5 && $count <= 20) {
            $result = $expressions['2'];
        } else {
            $count = $count % 10;
            if ($count == 1) {
                $result = $expressions['0'];
            } elseif ($count >= 2 && $count <= 4) {
                $result = $expressions['1'];
            } else {
                $result = $expressions['2'];
            }
        }
        return $result;
    }
    
    
    public static function was_get($params, $onlyOne = false)
    {
        if (is_array($params)) {
            if (!$onlyOne) {
                $res = true;
                foreach ($params as $param) {
                    $res = $res && isset($_GET[$param]);
                }
            } else {
                $res = false;
                foreach ($params as $param) {
                    $res = $res || isset($_GET[$param]);
                }
            }
            return $res;
        } else {
            return isset($params);
        }
    }
    
    public static function preg_match_all_utf8($pattern, $subject, &$matches)
    {
        @$pattern = iconv('UTF-8', 'Windows-1251', $pattern);
        @$subject = iconv('UTF-8', 'Windows-1251', $subject);
        @$res = preg_match_all($pattern, $subject, $matches);
        if ($res) {
            foreach ($matches as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    @$matches[$key][$key2] = iconv('Windows-1251', 'UTF-8', $value2);
                }
            }
        }
        return $res;
    }
    
    public static function preg_replace_utf8($pattern, $replacement, $subject)
    {
        @$pattern = iconv('UTF-8', 'Windows-1251', $pattern);
        @$replacement = iconv('UTF-8', 'Windows-1251', $replacement);
        @$subject = iconv('UTF-8', 'Windows-1251', $subject);
        $res = preg_replace($pattern, $replacement, $subject);
        @$res = iconv('Windows-1251', 'UTF-8', $res);
        return $res;
    }
    
    public static function preg_match_utf8($pattern, $subject,
            array &$matches = null, $flags = 0, $offset = 0)
    {
        @$pattern = iconv('UTF-8', 'Windows-1251', $pattern);
        @$subject = iconv('UTF-8', 'Windows-1251', $subject);
        $res = preg_match($pattern, $subject, $matches, $flags, $offset);
        if ($res) {
            foreach ($matches as $key => $value) {
                $matches[$key] = iconv('Windows-1251', 'UTF-8', $value);
            }
        }
        return $res;
    }
    
    
    public static function preg_split_uft8($pattern, $subject, $limit = -1,
            $flags = 0)
    {
        @$pattern = iconv('UTF-8', 'Windows-1251', $pattern);
        @$subject = iconv('UTF-8', 'Windows-1251', $subject);
        $res = preg_split($pattern, $subject, $limit, $flags);
        if (is_array($res)) {
            foreach ($res as $key => $value) {
                @$res[$key] = iconv('Windows-1251', 'UTF-8', $value);
            }
        }
        return $res;
    }
    
    public static function find_price(&$str)
    {
        if (!is_string($str)) {
            return false;
        }
        if (preg_match('/\$([\d\. ,]+)/', $str, $m)) {
            $m[1] = str_replace(',', '', $m[1]);
            $m[1] = str_replace(' ', '', $m[1]);
            return $m[1];
        }
        return false;
    }
    
    
    public static function find_prices(&$str)
    {
        $res = array();
        if (!is_array($str)) {
            if (preg_match_all('/\$([\d\. ,]+)/', $str, $m)) {
                foreach ($m[1] as $key => $value) {
                    $value = preg_replace('/,| /U', '', $value);
                    if (!empty($value) && $value > 0) {
                        $res[$value] = $value;
                    }
                }
                return array_keys($res);
            }
        }
        return false;
    }
    
    public static function safe_array_combine(array $keys, array $values)
    {
        $res = array();
        @$res = array_combine($keys, $values);
        if ($res === null) {
            return array();
        } else {
            return $res;
        }
    }
    
    public static function safe_array_intersect_key(&$arr1, &$arr2)
    {
        if (is_array($arr1) && is_array($arr2)) {
            return array_intersect_key($arr1, $arr2);
        } else {
            return false;
        }
    }
    
    public static function safe_preg_all($pattern, &$str)
    {
        if (preg_match_all($pattern, $str, $m)) {
            return $m[1];
        } else {
            return Null;
        }
    }
    
    
    public static function preg_find($pattern, &$str)
    {
        if (preg_match($pattern, $str, $m)) {
            return $m[1];
        } else {
            return Null;
        }
    }
    
    public static function memory_peak_mb($presicion = 0, $realUsage = true)
    {
        return round(memory_get_peak_usage($realUsage) / 1024 / 1024, $presicion);
    }
    
    public static function str_cammel_case_utf8($str)
    {
        return Hc::strtoupper_utf8(mb_substr($str, 0, 1)) .
                Hc::strtolower_utf8(mb_substr($str, 1, mb_strlen($str) - 1));
    }
    
    public static function php_version_5_3()
    {
        return preg_match('/5\.3/', phpversion());
    }
    
    public static function url_modify_current($paramsAddOrReplace,
            $deleteEmpty = true)
    {
        return self::url_replace_params(self::url(), $paramsAddOrReplace, $deleteEmpty);
    }
     
    
    public static function url_replace_params($url, $params,
            $deleteNulls = false)
    {
        $innerParams = self::url_parse_params($url);
        $basePath = self::url_get_base_path($url);
        if (!$deleteNulls) {
            foreach ($params as $key => $value) {
                $innerParams[$key] = $value;
            }
        } else {
            foreach ($params as $key => $value) {
                if (!empty($value)) {
                    $innerParams[$key] = $value;
                } else {
                    unset($innerParams[$key]);
                }
            }
        }
        $url = self::url_create($basePath, $innerParams);
        return $url;
    }
    
    public static function url_parse_params($url)
    {
        $params = array();
        $parts = explode('?', $url);
        if (count($parts) > 1) {
            $ins = explode('&', $parts[1]);
            foreach ($ins as $str) {
                $str = urldecode($str);
                $push = explode('=', $str);
                if (count($push) > 1) {
                    $push[1] = urldecode($push[1]);
                    if (!preg_match('/\[.*\]/', $push[0])) {
                        $params[$push[0]] = $push[1];
                    } else {
                                                $params[$push[0]][] = $push[1];
                    }
                }
            }
        }
        return $params;
    }
    
    public static function url_get_base_path($url)
    {
        $parts = explode('?', $url);
        return $parts[0];
    }
    
    public static function url_create($basePath, $params)
    {
        $res = $basePath;
        if (!empty($params)) {
            $res = $basePath . '?' . self::url_compare_params($params);
        }
        return $res;
    }
    
    public static function url_compare_params($params)
    {
        $res = array();
        foreach ($params as $key => $value) {
            if (!is_array($value)) {
                $res[] = $key . '=' . $value;
            } else {
                $res[] = self::array_implode('&', $value, $key . '=$value');
            }
        }
        $res = implode('&', $res);
        return $res;
    }
    
    public static function array_implode($delimiter, $arr,
            $mask = '$key ($value)')
    {
        foreach ($arr as $key => $value) {
            $text = str_replace('$key', $key, $mask);
            $text = str_replace('$value', $value, $text);
            $arr[$key] = $text;
        }
        $res = implode($delimiter, $arr);
        return $res;
    }
    
    public static function url()
    {
        return $_SERVER['REQUEST_URI'];
    }
    
    public static function url_replace_current($params, $deleteNulls = false)
    {
        return self::url_replace_params(Hc::url(), $params, $deleteNulls);
    }
    
    public static function url_delete_params($url, $paramsList = array())
    {
        $basePath = self::url_get_base_path($url);
        $params = self::url_parse_params($url);
        foreach ($paramsList as $param) {
            unset($params[$param]);
        }
        $res = self::url_create($basePath, $params);
        return $res;
    }
    
    public static function url_replace_base($sourceUrl, $newBasePart)
    {
        $parts = explode('?', $sourceUrl);
        if (count($parts) <= 1) {
            $parts[] = '';
        }
        if (!empty($parts[1])) {
            return $newBasePart . '?' . $parts[1];
        } else {
            return $newBasePart;
        }
    }
}
