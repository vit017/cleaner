<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 13.04.15
 * Time: 12:54
 */
if ( !CModule::IncludeModule('sale') ){
    return false;
}
$ID = intVal($arParams['ORDER_ID']);
$arParams['USER_ID'] = intVal($arParams['USER_ID']);
if ( !$arParams['USER_ID'] || $arParams['USER_ID']<=0 ){
    global $USER;
    $arParams['USER_ID'] = $USER->getID();
}
if ( !$ID || $ID <= 0 ){
    return false;
}

$arOrder = CSaleOrder::getByID($ID);
$props = bhOrder::getProps($ID);
$IDs = bhBasket::getItemsByOrderId($ID);
$arOrder['ACTION'] = bhOrder::getActions($arOrder, $props, $arParams['USER_ID']);

if ( $arOrder['ACTION']['DENY'] == 'Y' ){
    localRedirect('/cleaners/my/');
}

$arBasket = bhBasket::getByOrderId($ID);
$PRICE_TYPE = bhTools::getPriceType();
$arOrder["ITEMS"] = bhBasket::getBasket($arOrder['FUSER_ID'], $PRICE_TYPE, false, $IDs);
$arOrder["ITEMS"] = bhBasket::getBasketFormated($arOrder['FUSER_ID'], $PRICE_TYPE, false, $arOrder["ITEMS"], $arOrder['ACTION']['FINISH'] == 'Y'?false:true);

$arOrder['SUMMARY'] = bhOrder::getSummary($arOrder['ID'], $arBasket[$ID],$arOrder["PAY_SYSTEM_ID"]);

$arOrder['PROPS_FORMATED'] = bhOrder::formatProps($props);

$arResult = $arOrder;

if( $arResult['ACTION']['FINISH'] == 'Y' || $arResult['STATUS_ID'] == 'F') {
    $APPLICATION->setTitle('Выполненный заказ');
} elseif ( $arResult['ACTION']['CANCEL'] == 'N' ){
    $APPLICATION->setTitle('Ближайший заказ');
} elseif ( $arResult['CANCELED'] == 'Y' ){
    $APPLICATION->setTitle('Отмененный заказ');
} else {
    $APPLICATION->setTitle('Заказ');
}
//xmp($arResult);
$arResult['CLEANER_ID'] = $arParams['USER_ID'];
$arResult['ORDER_ID'] = $ID;
if ( isset($_REQUEST['SAVED_VERSION']) ){
    $arResult["SAVED_VERSION"] = $_REQUEST['SAVED_VERSION'];
} else {
    $arResult["SAVED_VERSION"] = json_encode($arOrder['ITEMS']);
}
if ( $arOrder['ACTION']['FINISH'] == 'Y' ) {
    $this->IncludeComponentTemplate('finish');
}else{
    $this->IncludeComponentTemplate('');
}

if ($_REQUEST["AJAX_CALL"] == "Y")
{
    die();
}