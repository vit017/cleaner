<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 29.07.14
 * Time: 14:43
 */
//03 21 * * * /usr/local/bin/php -q $HOME/cleanandaway.ru/www/cron/mailNotTakenOrders.php SERVER_NAME=gettidy.ru > /dev/null 2>&1 --delete-after

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

CModule::IncludeModule('sale');
$today = new DateTime();
$todayStamp = $today->format('d.m.Y H:i:s');
$tomorrow = new DateTime('tomorrow');
$tomorrow = $tomorrow->format('d.m.Y');
$arFilter = Array(
    "LID" => 's1',
    "CANCELED" => "N",
    "STATUS_ID" => "A",
    'PROPERTY_VAL_BY_CODE_DATE' =>$tomorrow
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
    $line = 'Есть '.$cNotTaken;
    $line .= ' '.bhTools::words($cNotTaken, array('невзятый', 'невзятых', 'невзятых'));
    $line .= ' '.bhTools::words($cNotTaken, array('заказ', 'заказа', 'заказов'));
    $line .= ' на завтра: ';
    $i = 0;
    foreach($arOrders as $id=>$val){
        $line .= '<a href="'.$_SERVER['SERVER_NAME'].'/bitrix/admin/sale_order_detail.php?ID='.$id.'&filter=Y&set_filter=Y&lang=ru" target="_blank">'.$id.'</a>';
        $i++;
        if ($i < $cNotTaken){
            $line .= ', ';
        }
    }
    $globals = array(
        array('name'=>'ORDERS', 'content'=>$line),
        array('name'=>'DEFAULT_EMAIL_FROM', 'content'=>'hello@'.$_SERVER['SERVER_NAME']),
        array('name'=>'SITE_NAME', 'content'=>$_SERVER['SERVER_NAME']),
        array('name'=>'SERVER_NAME', 'content'=>$_SERVER['SERVER_NAME'])
    );

    $to = array(
       array(
           'email' => 'hello@'.$_SERVER['SERVER_NAME'],
           'name' => 'GetTidy',
           'type' => 'to'
       )
    );

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
                //'subject'=>Невзятые заказы,
                'to'=>$to,
                'global_merge_vars'=>$globals,
                'merge'=>'Y')
        );
        file_put_contents($_SERVER["DOCUMENT_ROOT"].'/cron/logs/mailNotTakenOrders_log.txt', $todayStamp.'-'.$cleaner['EMAIL']."\n", FILE_APPEND);

    }
}
