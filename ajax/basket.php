<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if ( $_REQUEST['order_update'] == 'Y' ){
    $realBasket = bhBasket::getItemsByOrderId($_REQUEST['ORDER_ID']);
    $price_type = bhTools::getPriceType();
    if ( $price_type )
        $arBasket = bhBasket::getBasket($arOrder['FUSER_ID'], $price_type, false, $realBasket);
    $update = false;
    if (count($_REQUEST['PRODUCT'])>0){
        foreach ($_REQUEST['PRODUCT'] as $id){
            if ( $id == 0 ) continue;
            $arFields = array();
            $basket_id = false;
            $qnt = 1;
            if ( isset($_REQUEST['QUANTITY_'.$id]) && strlen($_REQUEST['QUANTITY_'.$id]) > 0 ){
                $qnt = intVal(trim($_REQUEST['QUANTITY_'.$id]));
            }

            if (isset($realBasket[$id])){
                $basket_id = $realBasket[$id]['ID'];
            }

            if ( !$basket_id ) {
                $arFields = array(
                    "PRODUCT_ID" => $id,
                    "CURRENCY" => $arBasket[$id]["CURRENCY"],
                    "QUANTITY" => $qnt,
                    "LID" => 's1',
                    "NAME" => $arBasket[$id]['NAME'],
                    "ORDER_ID" => $_REQUEST['ORDER_ID'],
                    "PRICE" => $arBasket[$id]["PRICE"]
                );

                CSaleBasket::Add($arFields);
                $update = true;
            } else {
                $toUpdate[$basket_id] = $qnt;
                $update = true;
                unset($realBasket[$id]);
            }
        }
    }
    foreach ($realBasket as $bskId => $fields){
        CSaleBasket::Delete($fields['ID']);
        $update = true;
    }

    if ( !empty($toUpdate) ){
        foreach($toUpdate as $id=>$qnt){
            CSaleBasket::Update($id, array('QUANTITY' => $qnt));
        }
    }

    if ( $update ){
        $realBasket = bhBasket::getItemsByOrderId($_REQUEST['ORDER_ID']);
        $arOrder = array(
            'BASKET_ITEMS' => $realBasket,
            'SITE_ID' => 's1',
        );
        $props = bhOrder::getProps($_REQUEST['ORDER_ID']);
        //check and set discounts
        if ( strlen($props["SALE_COUPON_UTM"]['VALUE']) > 0 ) {
            $db = CCatalogDiscountCoupon::GetList(array(),
                array(
                    'COUPON' => $props["SALE_COUPON_UTM"]['VALUE'],
                    'ACTIVE' => 'Y'
                )
            );
            if ( $arCoupon = $db->fetch() ) {
                $arDiscount = CCatalogDiscount::getById($arCoupon['DISCOUNT_ID']);
                $arOrder["PERSON_TYPE_ID"] = $arDiscount["SORT"];
            }
        }
        $arErrors = array();
        CSaleDiscount::DoProcessOrder($arOrder, array(), $arErrors);
        CSaleOrder::Update($_REQUEST['ORDER_ID'], array('PRICE' => $arOrder['ORDER_PRICE']));
        $APPLICATION->IncludeComponent('breadhead:trade.detail', '', array(
            "ORDER_ID" => $_REQUEST['ORDER_ID'],
            "USER_ID" => $USER->getID()
        ));
    }

} else {

    $APPLICATION->IncludeComponent("breadhead:basket.catalog2.0", "orderNew", array(
        "IBLOCK_TYPE" => "main",
        "IBLOCK_ID" => bhSettings::$IBlock_catalog,
        "SECTION_ID" => "",
        "INCLUDE_SUBSECTIONS" => "Y",
        "SHOW_ALL_WO_SECTION" => "Y",
        "PRICE_CODE" => array(
            0 => "base",
        ),
        "PROPERTY_CHECK" => "MUSTBE",
        "BASKET_URL" => "/order/",
        "ACTION_VARIABLE" => "action",
        "ACTION_NAME" => "refreshBasket",
        "PRODUCT_ID_VARIABLE" => "id",
        "USE_PRODUCT_MINIMUM" => "Y",
        "PROPERTY_MINIMUM" => "ORDER_MIN",
        "PROPERTY_NAME_FORMS" => "NAME_FORMS",
        "PROPERTY_DURATION" => "DURATION",
        "SUBMIT_TITLE" => "Выбрать дату",
        "PRODUCT_QUANTITY_VARIABLE" => "quantity"
    ),
        false
    );
}