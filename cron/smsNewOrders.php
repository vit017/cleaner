<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 29.07.14
 * Time: 14:43
 */
//00 22 * * * /usr/local/bin/php -q $HOME/gettidy.ru/www/cron/mailNewOrders.php SERVER_NAME=gettidy.ru > /dev/null 2>&1 --delete-after
//00 22 * * * /usr/local/bin/php -q $HOME/gettidy.ru/www/cron/mailNewOrders.php SERVER_NAME=cleanandaway.ru > /dev/null 2>&1 --delete-after

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

function RemindNewOrders()
{
    CModule::IncludeModule('sale');
    $today = new DateTime();
    $todayStamp = $today->format('d.m.Y H:i:s');

    $arFilter = Array(
        "LID" => 's1',
        "CANCELED" => "N",
        "STATUS_ID" => "A",
        '>=DATE_INSERT' => $today->format('d.m.Y 00:00:00'),
    );

    if($_GET['debug']){
        echo 'Filter:';
        xmp($arFilter);
    }

    $arOrders = array();
    $dbOrder = CSaleOrder::GetList(Array("ID" => "DESC"), $arFilter, false, false, Array("ID"));
    while ($arOrder = $dbOrder -> GetNext()) {
        $arOrders[$arOrder['ID']] = $arOrder['ID'];
    }

    $dbPropCleaner = CSaleOrderPropsValue::GetList(
        array(),
        array('CODE'=>'Cleaner', 'ORDER_ID' => $arOrders),
        false, false
    );
    while ($arPropCleaner = $dbPropCleaner->fetch()){
        if ( strlen($arPropCleaner['VALUE']) > 0 ){
            unset($arOrders[$arPropCleaner['ORDER_ID']]);
        }
    }

    $cNotTaken = count($arOrders);

    if ( $cNotTaken > 0 ){
        $line = bhTools::words($cNotTaken, array('Доступен', 'Доступно', 'Доступно')).' ';
        $line .= $cNotTaken;
        $line .= ' '.bhTools::words($cNotTaken, array('новый', 'новых', 'новых'));
        $line .= ' '.bhTools::words($cNotTaken, array('заказ', 'заказа', 'заказов'));

        $arCleaners = bhTools::getCleaners();
        foreach ($arCleaners as $cleaner){
            if ( $cleaner['ACTIVE'] != 'Y' ) continue;
            if ( strlen($cleaner['PERSONAL_PHONE']) <= 0 ) continue;
            else
                $phone = trim($cleaner['PERSONAL_PHONE']);
            if ( !isset($arPhones[$phone]) ){
                $arPhones[$phone] = $arPhones;
            } else continue;
            $stringSms = $line.'. Успей взять свой!';
            if($_GET['debug']){
                echo $phone;
                xmp($stringSms);
            }else{
                bhTools::sendSms($phone, $stringSms);
                file_put_contents($_SERVER["DOCUMENT_ROOT"].'/cron/logs/mailNewOrders_log.txt', $todayStamp.'-'.$cleaner['EMAIL']."\n", FILE_APPEND);

            }
        }


    }
}
RemindNewOrders();
