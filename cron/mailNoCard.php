<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 29.07.14
 * Time: 14:43
 */
//30 * * * * /usr/local/bin/php -q $HOME/gettidy.ru/www/cron/mailNoCard.php SERVER_NAME=cleanandaway.ru > /dev/null 2>&1 --delete-after

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
    $today = new DateTime();
    $todayStamp = $today->format('d.m.Y H:i:s');

    $now = new DateTime('-30 minutes');
    $arFilter = Array(
        "LID" => 's1',
        "CANCELED" => "N",
        "STATUS_ID" => "N",
        "PAY_SYSTEM_ID" => 2,
        '>=DATE_INSERT' => $now->format('d.m.Y H:i:s')
    );

    if($_GET['debug']){
        echo 'Filter:';
        xmp($arFilter);
    }

    $arOrders = array();
    $dbOrder = CSaleOrder::GetList(Array("ID" => "DESC"), $arFilter, false, false, Array("ID"));
    while ($arOrder = $dbOrder -> GetNext()) {
        $arOrders[$arOrder['ID']] = $arOrder['USER_ID'];
    }

    $dbPropCardId = CSaleOrderProps::GetList(
        array(),
        array('CODE'=>'CardId', 'ODER_ID' => array_keys($arOrders)),
        false, false,
        array('ID')
    );
    while ($arPropCardId = $dbPropCardId->fetch()){
        if ( strlen($arPropCardId['VALUE']) > 0 )
            unset($arOrders[$arPropCardId['ORDER_ID']]);
    }

    if ( count($arOrders) > 0 ){
        $arUsers = bhTools::formatUser($arOrders);
    }

    foreach ($arOrders as $orderID=>$userID){
        $stringSms = 'Не удалось привязать карту по заказу '.$orderID;
        $phone = bhSettings::$phone_manager;
        if ( strlen($phone) > 0) {
            bhTools::sendSms($phone, $stringSms);
        }

        $user = $arUsers[$userID];
        $globals = array(
            array('name'=>'CLIENT_NAME', 'content'=>$user['NAME']),
            array('name'=>'CLIENT_PHONE', 'content'=>$user['PERSONAL_PHONE']),
            array('name'=>'ORDER_ID', 'content'=>$orderID),
            array('name'=>'DEFAULT_EMAIL_FROM', 'content'=>'hello@'.$_SERVER['SERVER_NAME']),
            array('name'=>'SITE_NAME', 'content'=>$_SERVER['SERVER_NAME'])
        );
        if($_GET['debug']){
            echo $orderID;
            xmp($globals);
        }else{
            $token = bhSettings::$mandrillKey;
            $mandrill = new Mandrill($token);

            $mandrill->messages->sendTemplate(
                'no-card',
                array(),
                array(
                    //'subject'=>Заказ без карты,
                    'to'=>array(
                        array(
                            'email' => 'hello@'.$_SERVER['SERVER_NAME'],
                            'name' => 'getTidy',
                            'type' => 'to'
                        )
                    ),
                    'global_merge_vars'=>$globals,
                    'merge'=>'Y')
            );
            file_put_contents($_SERVER["DOCUMENT_ROOT"].'/cron/logs/mailNoCard_log.txt', $todayStamp.'-'.$orderID."\n", FILE_APPEND);

        }
    }
}
RemindClean();
