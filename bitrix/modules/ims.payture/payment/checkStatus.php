<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 02.06.14
 * Time: 15:55
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?


IncludeModuleLangFile(__FILE__);
/*if($_REQUEST['ID']){
	$orderID = $_REQUEST['ID'];
	checkPayment($orderID);
}else return false;*/
function checkPayment($orderID){
	if(!CModule::IncludeModule("ims.payture")) return false;
	if(!CModule::IncludeModule("sale")) return false;
	if($orderID<=0)return false;

	$arOrder = CSaleOrder::GetByID($orderID); //получаем номер оплаченого заказа от ПС
	$dbPSinfo = CSalePaySystemAction::GetList(array(), array('ID'=>$arOrder['PAY_SYSTEM_ID'])); // получаем массив со свойствами обработчика ПС

    if($PSinfo = $dbPSinfo->Fetch()){
	    $PSinfo = unserialize($PSinfo["PARAMS"]); // обрабатываем масив
	    $ShopID = bhSettings::$p_merchID;
	    $host = bhSettings::$p_host;
	}else{
		return false;
    }

	// init payture

	$initData = urlencode('OrderId='.$orderID);
	$initVars = 'VWID='.$ShopID.'&Data='.$initData;

	$status = 'https://'.$host.'/vwapi/PayStatus?'.$initVars;
	$initXML = new CDataXML();
	$initXML->LoadString(file_get_contents($status));
	$arInitResult = $initXML->GetArray();
    //xmp($arInitResult);
	if($arInitResult['PayStatus']['@']['Success'] == 'True' /*&& $arInitResult['PayStatus']['@']['Amount'] == $Sum*/){
        return $arInitResult['PayStatus']['@']['CardId'];
	}else{
	    return 'ERROR';
    }
}




