<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 12.11.14
 * Time: 16:33
 */
//*/5 * * * * /usr/local/bin/php -q $HOME/gettidy.ru/www/cron/setCartID.php SERVER_NAME=gettidy.ru > /dev/null 2>&1 --delete-after
//*/5 * * * * /usr/local/bin/php -q $HOME/cleanandaway.ru/www/cron/setCartID.php SERVER_NAME=cleanandaway.ru > /dev/null 2>&1 --delete-after
//*/5 * * * * /usr/local/bin/php -q $HOME/dev.gettidy.ru/www/cron/setCartID.php SERVER_NAME=dev.gettidy.ru > /dev/null 2>&1 --delete-after
//*/5 * * * * /usr/local/bin/php -q $HOME/dev2.gettidy.ru/www/cron/setCartID.php SERVER_NAME=dev2.gettidy.ru > /dev/null 2>&1 --delete-after
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

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$tmlLine = '';

if (CModule::IncludeModule('sale')){
    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/xml.php");
    $arOrders = array();
    $db = CSaleOrder::getList(array('ID'=>'ASC'), array('PAY_SYSTEM_ID'=>2, '!PROPERTY_VAL_BY_CODE_CardNumber'=>'', 'CANCELED' => 'N'));
    while ($arOrder = $db->fetch()){
        $orderID = $arOrder['ID'];
        $nextOrder = false;
        $cardNumber = false;
        $dbOrderProp = CSaleOrderPropsValue::GetList(Array(), Array("ORDER_ID" => $orderID));
        while ($arOrderProp = $dbOrderProp->Fetch()){
            switch ($arOrderProp['CODE']){
                case 'CardNumber':
                    $cardNumber = trim($arOrderProp['VALUE']);
                    break;
                case 'GET_CARDID':
                    if(strlen(trim($arOrderProp['VALUE']))>0){
                        $nextOrder = true;
                    }
                    break;
                case 'CardId':
                    if(strlen(trim($arOrderProp['VALUE']))>0){
                        $nextOrder = true;
                    }
                    break;
            }
        }
        if ($nextOrder){
            continue;
        }

        if ($cardNumber) {
            $cardID = bhPayture::getCardIDbyNumber($orderID, $cardNumber);
            xmp($cardID);
            if ( $cardID ) {
                if(bhOrder::setProp($orderID, 'CardId', $cardID, $arOrder["PERSON_TYPE_ID"])){
                    $tmlLine = $orderID . "\n";
                    bhOrder::setStatusA($arOrder, array());
                }
            }
        } else {
            bhOrder::setProp($orderID, 'GET_CARDID', 'N', $arOrder["PERSON_TYPE_ID"]);
            $tmlLine = $orderID . "\n";
        }
    }
    if (strlen($tmlLine)>0){
        if ($_GET['debug']){
            echo 'Orders:<br>';
            echo $tmlLine;
        }else{
            file_put_contents($_SERVER["DOCUMENT_ROOT"].'/cron/logs/orders_done.txt', $tmlLine, FILE_APPEND);
        }
    }
}