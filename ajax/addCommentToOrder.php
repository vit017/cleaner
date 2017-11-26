<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 17.08.2016
 * Time: 17:39
 */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$orderID = $_REQUEST['orderid'];
$db_vals = CSaleOrderPropsValue::GetList(
    array("SORT" => "ASC"),
    array(
        "ORDER_ID" => $orderID,
    )
);
$arProps = array();
while ($arVals = $db_vals->Fetch()){
    $arProps[$arVals["CODE"]] = $arVals["VALUE"];
}


$property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID" => 5, "CODE" => 'MARK'));
$ratingEnumAr = array();
while($enum_fields = $property_enums->GetNext())
{
    $ratingEnumAr[$enum_fields["VALUE"]] = $enum_fields["ID"];
}
$arOrder = CSaleOrder::GetByID($orderID);
$arResult['ORDER_STATUS'] = $arOrder['STATUS_ID'];
$arElement = array();
$arElement["NAME"] = "Отзыв к заказу №".$orderID;
$arElement["PREVIEW_TEXT"] = $_REQUEST['comment'];
$arElement["IBLOCK_ID"] = bhSettings::$IBlock_comments;
$arElement['PROPERTY_VALUES']["15"] = $orderID;
$arElement['PROPERTY_VALUES']["16"] = $arProps["Cleaner"];
$arElement['PROPERTY_VALUES']["17"] = $ratingEnumAr[$_REQUEST['clean-rating']];
$arElement['PROPERTY_VALUES']["18"] = $arOrder["USER_ID"];
$arElement['PROPERTY_VALUES']["19"] = $arProps["DATE"];

$el = new CIBlockElement;
if($PRODUCT_ID = $el->Add($arElement)) {
    http_response_code(200);
} else {
    http_response_code(404);
}
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");