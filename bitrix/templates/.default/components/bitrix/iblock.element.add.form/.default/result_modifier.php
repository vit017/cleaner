<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 26.05.14
 * Time: 11:04
 */
if (strlen($_REQUEST["ORDER"])<=0){
	localRedirect("/user/history");
} else {
	$orderID = intVal($_REQUEST["ORDER"]);
}
CModule::IncludeModule("sale");
$arOrder = CSaleOrder::GetByID($orderID);
if ($arOrder['USER_ID'] != $USER->GetID() || empty($arOrder) || $arOrder['CANCELED']=='Y'){
	localRedirect('/user/history/');
}

$db = CIBlockElement::GetList(array(), array('IBLOCK_ID'=>bhSettings::$IBlock_comments, 'PROPERTY_ORDER'=>$orderID, 'PROPERTY_AUTHOR'=>$arOrder["USER_ID"]), false, false, array('ID', 'NAME', 'PROPERTY_ORDER', 'PROPERTY_MARK', 'PREVIEW_TEXT', 'CREATED_DATE', 'PROPERTY_DATE'));
if ($review = $db->Fetch()){
	if (strlen($review["CREATED_DATE"])>0){
		$date = preg_split('[\.]', $review["CREATED_DATE"]);
		$months = bhTools::months(true);
		$review['TITLE_DATE'] = $date[2].' '.$months[trim($date[1], '0')].' '.$date[0];
	}
	if (strlen($review["PROPERTY_DATE_VALUE"])>0){
		$date = new dateTime($review["PROPERTY_DATE_VALUE"]);
		$months = bhTools::months(true);
		$review['PROPERTY_DATE_VALUE'] = $date->format('d').' '.$months[trim($date->format('m'), '0')].' '.$date->format('Y');
	}
	$arResult['REVIEW'] = $review;
} else {
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
	$arOrder = CSaleOrder::GetByID($orderID);
	$arResult['ORDER_STATUS'] = $arOrder['STATUS_ID'];

	$arHidden = array();
	$arHidden["NAME"] = "Отзыв к заказу №".$orderID;
	$arHidden["15"] = $orderID;
	$arHidden["16"] = $arProps["Cleaner"];
	$arHidden["18"] = $arOrder["USER_ID"];
	$arHidden["19"] = $arProps["DATE"];
	if (strlen($arProps["DATE"])){
		$date = new DateTime($arProps["DATE"]);
		$months = bhTools::months(true);
		$arResult['TITLE_DATE'] = $date->format('d').' '.$months[trim($date->format('m'), '0')].' '.$date->format('Y');
	}

	$hide = array("NAME", 15, 16, 18);
	$arResult["HIDDEN"] = $arHidden;
	if ($arOrder["STATUS_ID"] == "C"){
		$arResult["NOT_DONE"] = "Y";
		$arHidden["17"] = 0;
		$hide[] = 17;
	} else {
		$arResult['MARK'] = $arResult['PROPERTY_LIST_FULL'][17];
	}
	foreach ($arResult['PROPERTY_LIST'] as $i=>$code){
		if (in_array($code, $hide)){
			unset($arResult['PROPERTY_LIST'][$i]);
		}
	}
}
$arResult["ORDER_ID"] = $orderID;