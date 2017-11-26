<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 08.04.15
 * Time: 15:24
 */
CModule::IncludeModule('sale');
CModule::IncludeModule('iblock');
class bhCalendar{

    private static function getActivePeriod(){
        $NextMonth = new dateTime('+1 month');
		$now = new dateTime();
//        $today = new dateTime('tomorrow');
        if ( $now->format('H') >= bhSettings::$block_tomorrow ){
            $today = new dateTime('tomorrow');
        } else {
            $today = new dateTime();
        }
        return array('START' => $today, 'FINISH' => $NextMonth);
    }

    public static function getDates($duration){
        if ( !$duration )
            return false;

        $active = self::getActivePeriod();


        $arCalendar = self::makeDates($active['START'], $active['FINISH']);

        //echo "<pre>"; print_r($arCalendar); echo "</pre>";

        $arDateLimits = self::getSchedule();

        $work_days = array_keys($arCalendar);
        $arCount = bhOrder::getByDatesCount($work_days);
        $arNotify = array();

        foreach ($arCalendar as $date=>&$times){
            if ( isset($arDateLimits[$date]) ){
                if ( $arCount[$date] >= $arDateLimits[$date] || $arDateLimits[$date] == 0){
                    unset($arCalendar[$date]);
                    $arNotify[$date] = $date;
                }
            }
            krsort($times);
            foreach ($times as &$time){
                if ( $time['TIME'] + $duration > bhSettings::$end_of_day ){
                    $time['AV'] = 'N';
                }
            }
            ksort($times);
        }
        foreach ($arCalendar as $date=>$times){
            $return[bhTools::dateFormat($date, 'js')] = $times;
        }

        if ( !empty($arNotify) ){
            self::sendNotify($arNotify);
        }
        return $return;
    }

    private function sendNotify($dates){
        if ( !$dates || empty($dates) || $dates == '') return false;

        CModule::IncludeModule('iblock');
        $db = CIBlockElement::getList(array('NAME'=>'ASC'), array('IBLOCK_ID' => bhSettings::$IBlock_notify ,'NAME'=>$dates));
        while($el = $db->fetch()){
            unset($dates[$el['NAME']]);
        }
        $element = new CIBlockElement;
        foreach($dates as $date){
            $stringSms = 'На '.$date.' достигнут максимум заказов.';
            $phone = bhSettings::$phone_manager;
            if ( strlen($phone) > 0) {
                bhTools::sendSms($phone, $stringSms);
            }
            $token = bhSettings::$mandrillKey;
            $mandrill = new Mandrill($token);
            $mandrill->messages->sendTemplate(
                'max-orders',
                array(),
                array(
                    //'subject'=>Оформлено максимально разрешенное число заказов на *|DATE|*,
                    'to' => array(
                        array(
                            'email' => 'hello@'.$_SERVER['SERVER_NAME'],
                            'name' => 'getTidy',
                            'type' => 'to'
                        )
                    ),
                    'global_merge_vars' => array(
                        array('name'=>'DATE', 'content'=>$date),
                        array('name'=>'DEFAULT_EMAIL_FROM', 'content'=>'hello@'.$_SERVER['SERVER_NAME']),
                        array('name'=>'SITE_NAME', 'content'=>$_SERVER['SERVER_NAME']),
                        array('name'=>'SERVER_NAME', 'content'=>$_SERVER['SERVER_NAME'])
                    ),
                    'merge' => 'Y')
            );
            $element->Add(array('IBLOCK_ID' => bhSettings::$IBlock_notify ,'NAME'=>$date));
        }
    }

    private function makeDates($start, $finish){
        $arDates = array();
        $arTimes = self::getTimes();
        $start = new dateTime(bhTools::dateFormat($start, 'date'));
        while ( $finish >= $start ){
            $day_formated = bhTools::dateFormat($finish, 'date');
            $arDates[$day_formated] =  $arTimes;
            $finish = new dateTime($day_formated.' -1day');
        }
        return $arDates;
    }

    public static function getTimes(){
        $arTimes = array();
        $times = bhSettings::$times;
        foreach($times as $t){
            $arTimes[] = array('TIME' => $t, 'AV' => 'Y');
        }

        return $arTimes;
    }


    private function getSchedule(){
        $arDateLimits = array();

        $active = self::getActivePeriod();
        $start = $active['START'];
        $finish = $active['FINISH'];

        $res = CIBlockElement::GetList(
            array('DATE_ACTIVE_FROM'=>'ASC'),
            array(
                'IBLOCK_ID'=>bhSettings::$IBlock_schedule_2,
                'ACTIVE'=>'Y',
                '>=DATE_ACTIVE_FROM' => $start,
                '>=DATE_ACTIVE_TO' => $start,
            ),
            false, false);
		while($rule = $res->GetNextElement()){
            $fields = $rule->GetFields();
            $props = $rule->GetProperties();
            $max = 0;
            if ( isset($props['MAX_ORDER']['VALUE']) ){
                $max = $props['MAX_ORDER']['VALUE'];
            }

            if ( strlen($props['DATE']['VALUE']) > 0 ){
                if ( isset($arDateLimits[bhTools::dateFormat($props['DATE']['VALUE'], 'date')]) ){
                    /*if ( $arDateLimits[bhTools::dateFormat($props['DATE']['VALUE'], 'date')] <> $max){
                        $max = $arDateLimits[bhTools::dateFormat($props['DATE']['VALUE'], 'date')];
                    }*/
                }
                $arDateLimits[bhTools::dateFormat($props['DATE']['VALUE'], 'date')] = $max;
            } elseif ( strlen($fields['DATE_ACTIVE_FROM']) > 0 ){
                if ( strlen($fields['DATE_ACTIVE_TO']) > 0 ){
                    $finish = new dateTime($fields['DATE_ACTIVE_TO']);
                }
                $new_start = new dateTime($fields['DATE_ACTIVE_FROM'].'-1day');
                if ( $new_start < $start ){
                    $new_start = $start;
                }
                $arCalendar = self::makeDates($new_start, $finish);

                $dates = array_keys($arCalendar);
                $maxN = false;
                foreach($dates as $d){
                    /*if ( isset($arDateLimits[$d]) ) {
                        if ( $arDateLimits[$d] <> $max ) {
                            $maxN = $arDateLimits[$d];
                        }
                    }*/
                    $arDateLimits[$d] = $max;
                }
            } elseif ( strlen($fields['DATE_ACTIVE_TO']) > 0 ){
                $finish = new dateTime($fields['DATE_ACTIVE_TO']);
                $arCalendar = self::makeDates($start, $finish);
                $dates = array_keys($arCalendar);

                foreach($dates as $d){
                    /*if ( isset($arDateLimits[$d]) ) {
                        if ( $arDateLimits[$d] <> $max ) {
                            $maxN = $arDateLimits[$d];
                        }
                    }*/
                    $arDateLimits[$d] = $max;
                }
            } else{

                $arCalendar = self::makeDates($start, $finish);
                $dates = array_keys($arCalendar);
                foreach($dates as $d){
                    if ( !isset($arDateLimits[$d]) ) {
                        /*if ( $arDateLimits[$d] <> $max ) {
                            $max = $arDateLimits[$d];
                        }*/
                        $arDateLimits[$d] = $max;
                    }
                }
            }
        }
        return $arDateLimits;
    }

}
