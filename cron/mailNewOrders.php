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

    $dbPropCleaner = CSaleOrderProps::GetList(
        array(),
        array('CODE'=>'Cleaner', 'ODER_ID' => $arOrders),
        false, false,
        array('ID')
    );
    while ($arPropCleaner = $dbPropCleaner->fetch()){
        if ( strlen($arPropCleaner['VALUE']) > 0 ){
            unset($arOrders[$arPropCleaner['ORDER_ID']]);
        }
    }

    $cNotTaken = count($arOrders);

    if ( $cNotTaken > 0 ){
        $line = $cNotTaken;
        $line .= ' '.bhTools::words($cNotTaken, array('интересный', 'интересных', 'интересных'));
        $line .= ' '.bhTools::words($cNotTaken, array('заказ', 'заказа', 'заказов'));
        $globals = array(
            array('name'=>'ORDERS', 'content'=>$line),
            array('name'=>'DEFAULT_EMAIL_FROM', 'content'=>'hello@'.$_SERVER['SERVER_NAME']),
            array('name'=>'SITE_NAME', 'content'=>$_SERVER['SERVER_NAME']),
            array('name'=>'SERVER_NAME', 'content'=>$_SERVER['SERVER_NAME'])
        );
        $arCleaners = bhTools::getCleaners();
        foreach ($arCleaners as $cleaner){
            if ( $cleaner['ACTIVE'] != 'Y' ) continue;
            if ( strlen($cleaner['EMAIL']) <= 0 ) continue;
            $to = array(
               array(
                   'email' => $cleaner['EMAIL'],
                   'name' => $cleaner['NAME'],
                   'type' => 'to'
               )
            );
            $globals[] =  array('name'=>'NAME', 'content'=>$cleaner['NAME']);
            if($_GET['debug']){
                xmp(array(
                    'to'=>$to,
                    'global_merge_vars'=>$globals,
                    'merge'=>'Y'));
            }else{
                $token = bhSettings::$mandrillKey;
                $mandrill = new Mandrill($token);

                $mandrill->messages->sendTemplate(
                    'new-orders',
                    array(),
                    array(
                        //'subject'=>Интересные заказы. Успей выбрать себе!,
                        'to'=>$to,
                        'global_merge_vars'=>$globals,
                        'merge'=>'Y')
                );
                file_put_contents($_SERVER["DOCUMENT_ROOT"].'/cron/logs/mailNewOrders_log.txt', $todayStamp.'-'.$cleaner['EMAIL']."\n", FILE_APPEND);

            }
        }


    }
}
RemindNewOrders();
