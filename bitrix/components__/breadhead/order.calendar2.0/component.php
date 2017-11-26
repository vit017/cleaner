<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 15.05.14
 * Time: 18:00
 */
if (!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALL"));
	return;
}
if (!CModule::IncludeModule("catalog"))
{
	ShowError(GetMessage("CATALOG_MODULE_NOT_INSTALL"));
	return;
}
if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}
if($_REQUEST["AJAX_CALL"] == "Y")
{
	$APPLICATION->RestartBuffer();
}

if(!$arParams['ORDER_ID']){
	$arParams['ORDER_ID'] = false;
}

$props = array('DATE', 'TIME');
if( $_REQUEST['save'] && $arParams['ORDER_ID'] > 0 ){
	$db_vals = CSaleOrderPropsValue::GetList(
		array("SORT" => "ASC"),
		array(
			"ORDER_ID" => $arParams['ORDER_ID'],
			"CODE" => $props
		)
	);
	while ($arVals = $db_vals->Fetch()){
		CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$_REQUEST['ORDER_PROP_'.$arVals['ORDER_PROPS_CODE']]));
	}
	localRedirect($APPLICATION->GetCurPage().'?ID='.$arParams['ORDER_ID']);
}

$FUSER_ID = CSaleBasket::GetBasketUserID();
$arBasket = bhBasket::getRealBasket($FUSER_ID);
$ProdIDs = array();
$cnt = array();
foreach($arBasket as $basket){
	$ProdIDs[] = $basket['PRODUCT_ID'];
	$cnt[$basket['PRODUCT_ID']] = $basket['QUANTITY'];
}
$totalTime = bhBasket::getDuration($ProdIDs, $cnt);
$arResult["DATE_TIME"] = bhCalendar::getDates($totalTime/60);

$arResult['PROP_DATE_CODE'] = 'DATE';
$arResult['PROP_TIME_CODE'] = 'TIME';
$this->IncludeComponentTemplate();

if($_REQUEST["AJAX_CALL"] == "Y")
{
	die();
}
