<?
/*
 * 1C-Bitrix
 * Модуль для подключения платежной системы Payture
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!CModule::IncludeModule("ims.payture")) return;
IncludeModuleLangFile(__FILE__);

$CSalePaySystemAction=new CSalePaySystemAction();
// params
$Sum = $CSalePaySystemAction->GetParamValue("SHOULD_PAY", "");
$ShopID = $CSalePaySystemAction->GetParamValue("SHOP_ID", "");
$host = $CSalePaySystemAction->GetParamValue("HOST_CONNECT", "sandbox.payture.com");
$customerNumber = $CSalePaySystemAction->GetParamValue("ORDER_ID", "");
$orderDate = $CSalePaySystemAction->GetParamValue("ORDER_DATE", "");
$orderNumber = $CSalePaySystemAction->GetParamValue("ORDER_ID", "");
$SessionType = $CSalePaySystemAction->GetParamValue("SESSION_TYPE", "pay");
$FinalUrl = $CSalePaySystemAction->GetParamValue("FINAL_URL", "/");
$ViewType = $CSalePaySystemAction->GetParamValue("VIEW_TYPE", "");

// iframe
$iframe_width = $CSalePaySystemAction->GetParamValue("IFRAME_WIDTH", "0");
$iframe_height = $CSalePaySystemAction->GetParamValue("IFRAME_HEIGHT", "0");
//

$Sum_print = number_format($Sum, 2, '.', '');
$Sum = number_format($Sum, 2, '', '');

$orderId = $orderNumber.'-'.randString(5);

// init payture
if (!$PAYTURE_PAYMENT) {
$initUrl = "/apim/Init";
$initData = urlencode('SessionType='.$SessionType.';OrderId='.$orderId.';Product=Заказ №'.$orderNumber.';Amount='.$Sum.';IP='.$_SERVER['REMOTE_ADDR'].';Url='.$FinalUrl.';Total='.$Sum_print.';OrderNumber='.$orderNumber);
$initVars = 'Key='.$ShopID.'&Data='.$initData;
$initResult = QueryGetData($host, 443, $initUrl, $initVars, $errno, $errstr, "GET", "ssl://");

if ($initResult <> "")
{
// reject payture xml
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/xml.php");
	$initXML = new CDataXML();
	$initXML->LoadString($initResult);
	$arInitResult = $initXML->GetArray();
	if (count($arInitResult)>0 && $arInitResult["Init"]["@"]["Success"] == "True")
	{
	 	//$_SESSION['PAYTURE_PAYMENT_'.$orderNumber] = $arInitResult["Init"]["@"]["SessionId"];	
		$PAYTURE_PAYMENT = $arInitResult["Init"]["@"]["SessionId"];	
	}
	else {
	ShowError('Ошибка инициализации: '.$arInitResult["Init"]["@"]["ErrCode"]);
	}
}
}

// load payture interface
//if ($_SESSION['PAYTURE_PAYMENT_'.$orderNumber] <> "")
if ($PAYTURE_PAYMENT)
{
$PayAddress = 'https://'.$host.'/apim/Pay?SessionId='.$PAYTURE_PAYMENT;
if ($ViewType == "iframe") {
?>
<iframe src="<?=$PayAddress?>" width="<?=$iframe_width?>px" height="<?=$iframe_height?>px" frameBorder="0"></iframe>
<?
}
if ($ViewType == "button") {
?>
<button onclick="window.open('<?=CUtil::JSEscape($PayAddress)?>')">Оплатить</button>
<?
}
if ($ViewType == "current") {
?>
<script type="text/javascript">
window.top.location.href='<?=CUtil::JSEscape($PayAddress)?>';

</script>
<?
}
}
?>