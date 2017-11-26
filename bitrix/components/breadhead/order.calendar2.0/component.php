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
$arResult = isset($arParams['arResult']) ? $arParams['arResult'] : array();
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

//print_r($totalTime);
$arResult["DATE_TIME"] = bhCalendar::getDates($totalTime/60);
$ctime = time();
$cdate = date('Y-m-d', $ctime);
$chour = (int)date('H', $ctime);
$cminute = (int)date('i', $ctime) / 60;
$MINORDERTIMEMINUTES = defined('MINORDERTIMEMINUTES') ? MINORDERTIMEMINUTES : 0;
$minTime = $chour + $cminute + $MINORDERTIMEMINUTES / 60;
$accessTime = false;
foreach ($arResult['DATE_TIME'][$cdate] as $timeIndex => $tAr) {
    if ($tAr['TIME'] < $minTime) {
        $arResult['DATE_TIME'][$cdate][$timeIndex]['AV'] = 'N';
    } else if ($arResult['DATE_TIME'][$cdate][$timeIndex]['AV'] == 'Y') {
        $accessTime = true;
    }
}
//debdie($arResult['DATE_TIME'][$cdate], $minTime, $chour);
if (!$accessTime) unset($arResult['DATE_TIME'][$cdate]);

if ($chour >= 19) {//после 19 запрещать заказ на 8 и 10 утра следующего дня
	$ndaydate = date('Y-m-d', strtotime($cdate . ' +1 day'));
	foreach ($arResult['DATE_TIME'][$ndaydate] as $dIndex => $day) {
		if ($day['TIME'] == 8 || $day['TIME'] == 10) {
			$arResult['DATE_TIME'][$ndaydate][$dIndex]['AV'] = 'N';
		}
	}
}



$arResult['PROP_DATE_CODE'] = 'DATE';
$arResult['PROP_TIME_CODE'] = 'TIME';
$this->IncludeComponentTemplate();

if($_REQUEST["AJAX_CALL"] == "Y")
{
	die();
}