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

$arResult = array();
$cleaner = new bhCleaner($arParams['USER_ID']);

if ( !isset($arParams['CLEANER']) || !$arParams['CLEANER']){
    $arWishOrders = $cleaner->getWishList();
    $arResult['WISH'] = false;
    if ( count($arWishOrders) > 0 ){
        $arResult['WISH'] = 'Y';
        foreach($arWishOrders as $order){
            $order['PROPS_FORMATED'] = bhOrder::formatProps($order['PROPS']);
            $arResult['WISH_ORDERS'][$order['ID']] = $order;
        }
    }

    $arOrders = $cleaner->getWeekList();
    $cOrders = 0;
    foreach ($arOrders as $day=>$orders){
        foreach ($orders as $i=>&$order){
            if ( $arResult['WISH'] == 'Y' ){
                if ( isset($arResult['WISH_ORDERS'][$order['ID']]) ){
                    unset($orders[$i]);
                    continue;
                }else {
                    $cOrders++;
                }
            } else{
                $cOrders++;
            }
            $order['PROPS_FORMATED'] = bhOrder::formatProps($order['PROPS']);
        }

        if (count($orders)>0){
            $arResult['ORDERS'][] = array('DATE' => $day, 'NAME' => bhTools::dateFormat($day, 'title'), 'ORDERS' => $orders);
        }
    }

    $availOrders = count($arResult['WISH_ORDERS']) + $cOrders;
    bhTools::setAvailOrders($availOrders);

} elseif ( $arParams['CLEANER'] == 'Y' ){
    if ( $arParams['HISTORY'] == 'Y' ){
        $arOrders = $cleaner->getDoneOrders();
        $arBaskets = bhBasket::getByOrderId($cleaner->getDoneOrdersIds());
    } else {
        $arOrders = $cleaner->getNotDoneOrders();
        $arBaskets = bhBasket::getByOrderId($cleaner->getNotDoneOrdersIds());
        bhTools::setActiveOrders(count($cleaner->getNotDoneOrdersIds()));
    }

    foreach($arOrders as $day => $orders){
        foreach ($orders as &$order){
            $order['PROPS_FORMATED'] = bhOrder::formatProps($order['PROPS']);
            $basket = $arBaskets[$order['ID']];
            $order['BASKET'] = $basket;
            $order['SUMMARY'] = bhOrder::getSummary($order['ID'], $basket, $order["PAY_SYSTEM_ID"]);
        }
        $arResult['ORDERS'][] = array('DATE' => $day, 'NAME' => bhTools::dateFormat($day, 'title'), 'ORDERS' => $orders);
    }
}

$arResult['CLEANER_ID'] = $arParams['USER_ID'];
$this->IncludeComponentTemplate();

if ($_REQUEST["AJAX_CALL"] == "Y")
{
    die();
}
