<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 29.07.14
 * Time: 14:43
 */
//0 6,8,10,12,14,16,18 * * * /usr/local/bin/php -q $HOME/gettidy.ru/www/cron/smsSender.php > /dev/null 2>&1 --delete-after
//0 6,8,10,12,14,16,18 * * * /usr/local/bin/php -q $HOME/cleanandaway.ru/www/cron/smsSender.php SERVER_NAME=cleanandaway.ru > /dev/null 2>&1 --delete-after
//0 6,8,10,12,14,16,18 * * * /usr/local/bin/php -q $HOME/dev.gettidy.ru/www/cron/smsSender.php SERVER_NAME=dev.gettidy.ru > /dev/null 2>&1 --delete-after
//0 6,8,10,12,14,16,18 * * * /usr/local/bin/php -q $HOME/dev2.gettidy.ru/www/cron/smsSender.php SERVER_NAME=dev2.gettidy.ru > /dev/null 2>&1 --delete-after
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("LANG", "s1");
foreach ($argv as $arg) {
    $e=explode("=",$arg);
    if (count($e)==2)
        $_GET[$e[0]]=$e[1];
    else
        $_GET[$e[0]]=0;
}
if ($_GET['SERVER_NAME']){
    $_SERVER['SERVER_NAME'] = $_GET['SERVER_NAME'];
}

if ( !isset($_SERVER['DOCUMENT_ROOT']) || $_SERVER['DOCUMENT_ROOT'] == '' ){
    $_SERVER['DOCUMENT_ROOT'] = '/home/u429586/'.$_SERVER['SERVER_NAME'].'/www/';
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

function RemindClean()
{
    CModule::IncludeModule('sale');
    $today = new DateTime('now +2hours');
    $todayStamp = $today->format('d.m.Y H:i:s');
    $todayD = $today->format('d.m.Y');
    $timeStart = $today->format('H');
    if (strlen($timeStart)==1){
        $timeStart = $today->format('h');
    }

    $arFilter = Array(
        "LID" => 's1',
        "CANCELED" => "N",
        "STATUS_ID" => "A",
        '!PROPERTY_VAL_BY_CODE_cleaner' =>'',
        'PROPERTY_VAL_BY_CODE_DATE' =>$todayD,
        'PROPERTY_VAL_BY_CODE_TIME'=>$timeStart,
    );
    if ($_GET['debug']){
        echo 'Filter:';
        xmp($arFilter);
    }
    $excludeIds = array();
    $arCleaners = bhTools::getCleaners();
    $arPropSmsByPersonType = array();
    $dbPropSms = CSaleOrderProps::GetList(array(), array('CODE'=>'REMIND_SMS'), false, false, array('ID', 'PERSON_TYPE_ID'));
    while ($arPropSms = $dbPropSms->fetch()){
        $arPropSmsByPersonType[$arPropSms['PERSON_TYPE_ID']] = $arPropSms['ID'];
    }

    $bSend = false;
    $dbOrder = CSaleOrder::GetList(Array("ID" => "DESC"), $arFilter, false, false, Array("ID", "DATE_INSERT", "PAYED", "USER_ID", "LID", "PRICE", "CURRENCY", "ACCOUNT_NUMBER", "PERSON_TYPE_ID"));
    while($arOrder = $dbOrder -> GetNext())
    {
        $nextOrder = false;
        $dbOrderProp = CSaleOrderPropsValue::GetList(Array(), Array("ORDER_ID" => $arOrder["ID"]));
        while ($arOrderProp = $dbOrderProp->Fetch()){
            switch ($arOrderProp['CODE']){
                case "Cleaner":
                    $cleanerID = $arOrderProp['VALUE'];
                    break;
                case 'PERSONAL_PHONE':
                    $phone = trim($arOrderProp['VALUE']);
                    break;
                case 'TIME':
                    $time = trim($arOrderProp['VALUE']);
                    break;
                case 'REMIND_SMS':
                    if(strlen(trim($arOrderProp['VALUE']))>0){
                        $nextOrder = true;
                    }
                    break;
                case 'PERSONAL_STREET':
                    if(strlen(trim($arOrderProp['VALUE']))>0){
                        $address = $arOrderProp['VALUE'];
                    }
                    break;
                case 'NAME':
                    if(strlen(trim($arOrderProp['VALUE']))>0){
                        $user_name = $arOrderProp['VALUE'];
                    }
                break;

            }
        }
        if ($nextOrder){
            continue;
        }
        $arCleaner['PERSONAL_PHONE'] = '';
        if (isset($arCleaners[$cleanerID])) {
            $arCleaner = $arCleaners[$cleanerID];
            if ($arCleaner['NAME'] == 'Нет'){
                continue;
            }
            if(strlen($arCleaner['PERSONAL_PHONE'])){
                //$arCleaner['PERSONAL_PHONE'] = ' +'.preg_replace('/[ -.()]/', '', $arCleaner['PERSONAL_PHONE']);
                $arCleaner['PERSONAL_PHONE'] = ' +'.$arCleaner['PERSONAL_PHONE'];
            }
        }else{
            continue;
        }

        $bSend = false;
        if(!in_array($arOrder['ID'],$excludeIds))
            $bSend = true;

        if ($bSend)
        {
            if (strlen($phone)>0){
                $stringSms = 'Уборка уже сегодня в '.$time.':00. Ваш клинер - '.$arCleaner["NAME"].$arCleaner['PERSONAL_PHONE'];

                $cleanerString = 'У вас сегодня уборка в '.$time.':00. Клиент: '.$user_name.'. Адрес: '.$address.'. Телефон клиента: +'.preg_replace('/[ -.()]/', '', $phone);

                if ($_GET['debug']){
                    echo $phone;
                    xmp($arOrder['ID'].$stringSms);
                    echo 'For cleaner: '.$arCleaner['PERSONAL_PHONE'];
                    xmp($cleanerString);
                }else{
                    bhTools::sendSms($phone, $stringSms);
                    bhTools::sendSms($arCleaner['PERSONAL_PHONE'], $cleanerString);

                    file_put_contents($_SERVER["DOCUMENT_ROOT"].'/cron/logs/smsSender_log.txt', $todayStamp.'-'.$arOrder['ID']."\n", FILE_APPEND);
                    $arFields = array(
                        "ORDER_ID" => $arOrder['ID'],
                        "ORDER_PROPS_ID" => $arPropSmsByPersonType[$arOrder['PERSON_TYPE_ID']],
                        "NAME" => "REMIND_SMS",
                        "CODE" => "REMIND_SMS",
                        "VALUE" => $todayStamp
                    );
                    CSaleOrderPropsValue::Add($arFields);
                }
                $excludeIds[] = $arOrder['ID'];
            }
        }

    }
    if (!$bSend){
        file_put_contents($_SERVER["DOCUMENT_ROOT"].'/cron/logs/smsSender_log_nosend.txt', $todayStamp."\n", FILE_APPEND);
    }
}
RemindClean();
