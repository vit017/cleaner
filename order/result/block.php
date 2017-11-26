<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 07.11.14
 * Time: 16:35
 */
//initiation from PAYTURE
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('sale');
$line = '';
foreach($_POST as $k=>$v){
    $line .= $k.'='.$v.';';
}

file_put_contents($_SERVER["DOCUMENT_ROOT"].'/logs/payture_logs.txt', $line."\n", FILE_APPEND);
if ( $_REQUEST['debug'] ){
    $_POST = $_GET;
}

if($_POST['SessionType']=='Block' && $_POST['Notification']=='CustomerAddSuccess' && $_POST['MerchantContract']==bhSettings::$p_merchID && $_POST['Success'] == 'True' && $_POST['OrderId']>0){
    $orderID = intVal($_POST['OrderId']);
    $cardName = $_POST['CardNumber'];
    file_put_contents($_SERVER["DOCUMENT_ROOT"].'/logs/payture_logs.txt', $orderID."\n", FILE_APPEND);

    $arOrder = CSaleOrder::getByID($orderID);
    $cardID = bhPayture::getCardIDbyNumber($orderID, $cardName);

    if ( !$cardID ) {
        if ( bhOrder::setProp($orderID, 'CardNumber', $cardName, $arOrder["PERSON_TYPE_ID"]) ){
            file_put_contents($_SERVER["DOCUMENT_ROOT"].'/cron/orders.txt', $orderID."\n", FILE_APPEND);
        }
    } else {
        if ( bhOrder::setProp($orderID, 'CardId', $cardID, $arOrder["PERSON_TYPE_ID"]) ){
            file_put_contents($_SERVER["DOCUMENT_ROOT"].'/cron/orders_done.txt', $orderID."\n", FILE_APPEND);
            bhOrder::setStatusA($arOrder, array());
        }
    }
}

if($_POST['SessionType']=='Block' && $_POST['Notification']=='CustomerPaySuccess' && $_POST['MerchantContract']==bhSettings::$p_merchID && $_POST['Success'] == 'True' && $_POST['OrderId']>0){
    $orderID = intVal($_POST['OrderId']);
    $cardName = $_POST['CardNumber'];
    file_put_contents($_SERVER["DOCUMENT_ROOT"].'/logs/payture_logs.txt', $orderID."\n", FILE_APPEND);

    $arOrder = CSaleOrder::getByID($orderID);
    $cardID = bhPayture::getCardIDbyNumber($orderID, $cardName);

    if ( !$cardID ) {
        if ( bhOrder::setProp($orderID, 'CardNumber', $cardName, $arOrder["PERSON_TYPE_ID"]) ){
            file_put_contents($_SERVER["DOCUMENT_ROOT"].'/cron/orders.txt', $orderID."\n", FILE_APPEND);
        }
    } else {
        if ( bhOrder::setProp($orderID, 'CardId', $cardID, $arOrder["PERSON_TYPE_ID"]) ){
            file_put_contents($_SERVER["DOCUMENT_ROOT"].'/cron/orders_done.txt', $orderID."\n", FILE_APPEND);
            bhOrder::setStatusA($arOrder, array());
        }
    }
}
