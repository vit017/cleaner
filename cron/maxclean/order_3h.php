<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
echo '<pre>';

$LOGDIR = $_SERVER["DOCUMENT_ROOT"] . '/cron/logs/';
$LOGNAME = basename(__FILE__, 'php') . '.log';
@file_put_contents($LOGDIR . $LOGNAME , date('d.m.Y H:i:s',time()), FILE_APPEND);
if (CModule::IncludeModule('sale')) {
    $date1 = date('d.m.Y');
    $date2 = date('d.m.Y', strtotime($date1 . ' +1 day'));
    $arFilter = Array(">=PROPERTY_VAL_BY_CODE_DATE" => $date1, "<=PROPERTY_VAL_BY_CODE_DATE" => $date2, "STATUS_ID" => "A", ">PROPERTY_VAL_BY_CODE_Cleaner" => "0");
    $db_sales = CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter, false, false, Array());
    $timeDef = date('H:i');
    while ($ar_sales = $db_sales->Fetch()) {

    	echo "<pre>"; print_r($ar_sales); echo "</pre>";

        $db_props = CSaleOrderPropsValue::GetOrderProps($ar_sales["ID"]);
        while ($arProps = $db_props->Fetch()) {
            if ($arProps["CODE"] == "TIME")
                $time = $arProps["VALUE"];
            if ($arProps["CODE"] == "DATE")
                $date = $arProps["VALUE"];
            if ($arProps["CODE"] == "PERSONAL_PHONE")
                $phone = $arProps["VALUE"];
            if ($arProps["CODE"] == "Cleaner")
                $cleanerId = $arProps["VALUE"];
            if ($arProps["CODE"] == "NAME")
                $name = $arProps["VALUE"];
            if ($arProps["CODE"] == "PERSONAL_STREET")
                $address = $arProps["VALUE"];
            if ($arProps["CODE"] == "SUBWAY")
                $subway = $arProps["VALUE"];
            if ($arProps["CODE"] == "DURATION")
                $duration = $arProps["VALUE"];
        }

        $smsForClientDayBefore = false;
        if ($time < 12) {
            $smsForClientDayBefore = true;
        }
        $nextDay = false;
        if ($smsForClientDayBefore && date('d', strtotime($date)) != date('d', time())) {
            $nextDay = true;
        }
        $toDay = false;     
        if (date('d', strtotime($date)) == date('d', time())) {
            $toDay = true;
        }

        $timeResult = $time - $timeDef;

        echo $ar_sales['ID'] . ' ' . $date . ' ' . $time  . ':00 phone - ' . $phone . PHP_EOL;
        if ($nextDay) {
            echo 'sms day before' . PHP_EOL;
            $dAr = explode('-', date('m-d-Y', time()));
            $time1 = mktime('20', '30', '00', $dAr[0], $dAr[1], $dAr[2]);
            $time2 = $time1 + 1700;
            if ($smsForClientDayBefore && time() >= $time1 && time() < $time2) {
                if ($phone){
                    $phone = str_replace(array("+", "-", "(", ")", " "), "", $phone);
                    if ($phone[0]=="7")
                        $phone[0]="8";
                    $message = 'Уборка уже завтра в '.$time.':00, ожидайте Клинера. Приятного вечера, Ваш MaxClean.';
                    sendsms($phone, $message);
                    echo 'sent sms to client' . PHP_EOL;
                    @file_put_contents($LOGDIR . $LOGNAME , ' - ' . $ar_sales["ID"] . ', sms for client - ' . $message, FILE_APPEND);
                }
            }
        } else if ($timeResult == 2 && $toDay) {
            echo 'current day - ' . $date . PHP_EOL;
            if ($phone && !$smsForClientDayBefore){
                $phone = str_replace(array("+", "-", "(", ")", " "), "", $phone);
                if ($phone[0]=="7")
                    $phone[0]="8";
                $message = 'Уборка уже сегодня в '.$time.':00. Ваш Клинер уже в пути.';
                sendsms($phone, $message);
                echo 'sent sms to client' . PHP_EOL;
                @file_put_contents($LOGDIR . $LOGNAME , ' - ' . $ar_sales["ID"] . ', sms for client - ' . $message, FILE_APPEND);
            }
            if ($cleanerId) {
                $rsUser = CUser::GetByID($cleanerId);
                $arUser = $rsUser->Fetch();
                $user_phone = 8 . $arUser["PERSONAL_PHONE"];

                $sms="Вас ждут на заказе №".$ar_sales["ID"]." к ".$time.":00, ".$name.", ".$phone.", ";
                if ($subway)
                    $sms.=$subway.", ";

                $sms.=$address.", ";

                $dbBasketItems = CSaleBasket::GetList(array( ), array("ORDER_ID" => $ar_sales["ID"]), false, false, array());
                unset($uslugi_line);
                unset($area);
                while ($arItems = $dbBasketItems->Fetch()){
                    if (preg_match("/до /iU", $arItems["NAME"]))
                        $area=$arItems["NAME"];
                    elseif ($arItems["NAME"]=="духовку")
                        $uslugi_line.="Дух, ";
                    elseif ($arItems["NAME"]=="пылесос")
                        $uslugi_line.="Пыл, ";
                    elseif ($arItems["NAME"]=="внутри кухонных шкафчиков")
                        $uslugi_line.="Кух, ";
                    elseif ($arItems["NAME"]=="внутри холодильника")
                        $uslugi_line.="Хол, ";
                    elseif ($arItems["NAME"]=="окна")
                        $uslugi_line.="Окн(".$arProperty['QUANTITY']."), ";
                    elseif ($arItems["NAME"]=="микроволновка")
                        $uslugi_line.="СВЧ, ";
                }
                $sms.=$area.", ".$duration."ч., ".$uslugi_line;

                if ($ar_sales["USER_DESCRIPTION"])
                    $sms.=$ar_sales["USER_DESCRIPTION"].", ";

                $arPaySys = CSalePaySystem::GetByID($ar_sales["PAY_SYSTEM_ID"]);

                if ($arPaySys["NAME"]=="Наличными")
                    $paysystem="Нал";
                else
                    $paysystem=$arPaySys["NAME"];
                $sms.=$paysystem.", ";
                $sms.=(int)$ar_sales["PRICE"]."р., ";
                $cleanerPrice=$duration*350;
                $sms.=$cleanerPrice."р.";
                sendsms($user_phone, $sms);
                echo 'sent sms to cleaner' . PHP_EOL;
                @file_put_contents($LOGDIR . $LOGNAME , ' - ' . $ar_sales["ID"] . ', sms for cleaner - ' . $sms, FILE_APPEND);
            }
        }
    }
}
@file_put_contents($LOGDIR . $LOGNAME , PHP_EOL, FILE_APPEND);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");

