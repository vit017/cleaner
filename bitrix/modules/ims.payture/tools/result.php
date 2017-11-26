<?
//error_reporting (E_ALL);
define("STOP_STATISTICS", true);
define("NOT_CHECK_PERMISSIONS", true);
define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ims.payture/log.txt");

if(!require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"))
    die('prolog_before.php not found!');
if(!CModule::IncludeModule("sale")) die('sale module not found');

if(!CModule::IncludeModule("ims.payture")) die('ims.payture module not found');


IncludeModuleLangFile(__FILE__);
function ob_exit($status = null)
{
	if($status) {
		ob_end_flush();
		isset($_REQUEST['debug']) ? exit($status) : exit();
	}
	else {
		ob_end_clean();
		header("HTTP/1.0 200 OK");
		echo "OK";
		exit();
	}
	AddMessage2Log($status, $module_id);
}

function debug_file()
{
	header('Content-type: text/plain; charset=utf-8');
	echo file_get_contents(__FILE__);
}

function check_referer()
{
	// IP нотификаций
	// 93.91.18.128/28 (93.91.18.128 - 93.91.18.143)    
	if(ip2long($_SERVER['REMOTE_ADDR'])>=ip2long('93.91.18.128') && ip2long($_SERVER['REMOTE_ADDR'])<=ip2long('93.91.18.143'))
		return true;
		
	// 195.122.19.192/28 (195.122.19.192 - 195.122.19.207)	
	if(ip2long($_SERVER['REMOTE_ADDR'])>=ip2long('195.122.19.192') && ip2long($_SERVER['REMOTE_ADDR'])<=ip2long('195.122.19.207'))
		return true;
		
	return false;
}

function from_request($name)
{
	return isset($_REQUEST[$name]) ? trim(stripslashes($_REQUEST[$name])) : null;
}

if($_REQUEST['debug_file']) {
	//if(check_im())
		debug_file();
		
}
else
{
	ob_start();
    $module_id = "ims.payture";
	  
    list($SessionType, $OrderId, $Total, $Amount, $CardNumber,
		$Success, $Notification, $MerchantContract, $OrderNumber) =
	array(
		from_request('SessionType'), from_request('OrderId'), from_request('Total'), 
		from_request('Amount'), from_request('CardNumber'), from_request('Success'), 
		from_request('Notification'), from_request('MerchantContract'), from_request('OrderNumber')
	);
   
	$arOrder = CSaleOrder::GetByID(IntVal($OrderNumber));
			 
	
	// ORDER ID CHECK
	if (!$arOrder) {
        AddMessage2Log(GetMessage("IMS.PAYTURE_WRONG_ORDER_ID - ".$OrderNumber, array("#ORDER_ID#" => $OrderNumber)), $module_id);
        SendError(GetMessage("IMS.PAYTURE_WRONG_ORDER_ID", array("#ORDER_ID#" => $OrderNumber)), $module_id);
		$err = "ERROR: ORDER NOT EXISTS!\n";
		ob_exit($err);
    }
	if (!$SessionType) {
        AddMessage2Log(GetMessage("IMS.PAYTURE_WRONG_SESSIONTYPE"), $module_id);
        SendError(GetMessage("IMS.PAYTURE_WRONG_SESSIONTYPE"), $module_id);
		$err = "ERROR: WRONG SESSION TYPE!\n";
		ob_exit($err);
    }
    
	$order_amount = $arOrder["PRICE"];
	
	$order_amount = number_format($order_amount, 2, '', '');
	$ps_sum = number_format($Total, 2, '.', '');	
		
	$arFieldsFailed = array(
		"PS_STATUS" => "N",
		"STATUS_ID" => "N",
		"PS_RESPONSE_DATE" => date("d-m-Y H:i:s"),
		"USER_ID" => $arOrder["USER_ID"]
	);

	$arFieldsBlock = array(
		"PAYED" => "Y",
		"PS_STATUS" => "Y",
		"PS_STATUS_CODE" => "Block",
		"PS_STATUS_DESCRIPTION" => GetMessage("IMS.PAYTURE_PS_STATUS_DESC_BLOCK"),
		"PS_SUM" => $Total,
		"PS_RESPONSE_DATE" => date("d-m-Y H:i:s"),
		"USER_ID" => $arOrder["USER_ID"]
	);
	
	$arFieldsSuccess = array(
		"PAYED" => "Y",
		"PS_STATUS" => "Y",
		"PS_STATUS_CODE" => "Pay",
		"PS_STATUS_DESCRIPTION" => GetMessage("IMS.PAYTURE_PS_STATUS_DESC_PAY"),
		"STATUS_ID" => "P",
		"PS_SUM" => $Total,
		"PS_RESPONSE_DATE" => date("d-m-Y H:i:s"),
		"USER_ID" => $arOrder["USER_ID"]
	);

	// AMOUNT and CURRENCY CODE CHECK
	if( $order_amount != $Amount )
	{
		$arFieldsFailed["PS_STATUS_MESSAGE"] = GetMessage("IMS.PAYTURE_AMOUNT_DONT_MATCH", array("#ORDER_ID#" => $OrderNumber));
		CSaleOrder::Update($arOrder["ID"], $arFieldsFailed);
		
		$err = "ERROR: AMOUNT MISMATCH!\n";
		$err .= "Amount: $Amount; order_amount: $order_amount;\n\n";
		ob_exit($err);
	}
	
	
	// Проверка по ключу (пока не сделана)
	if($MerchantContract)
	{
		//Проверка источника данных 
		/*if($secretKey != $order_secretKey)
		{
			$arFieldsFailed["PS_STATUS_MESSAGE"] = GetMessage("IMS.PAYTURE_SECRET_KEY_DONT_MATCH", array("#ORDER_ID#" => $OrderId));
			CSaleOrder::Update($arOrder["ID"], $arFieldsFailed);
			
			$err = "ERROR: SECRET_KEY MISMATCH!\n";
			$err .= check_im() ? ("secretKey: $secretKey; order_secretKey: ".$order_secretKey.";\n\n") : "\n";
			ob_exit($err);
		}*/		
		
	}
	
	if ($Success == "True") {
	if ($SessionType == 'Block')
	{
		$arFieldsBlock["PS_STATUS_MESSAGE"] = GetMessage("IMS.PAYTURE_PAYMENT_FOR_ORDER_BLOCK", array("#ORDER_ID#" => $OrderNumber, "#CARDTYPE#" => $carttype, "#CARDNUMBER#" => $CardNumber));
		$arFieldsBlock["PS_STATUS_DESCRIPTION"] = $arFieldsBlock["PS_STATUS_MESSAGE"];
		CSaleOrder::Update($arOrder["ID"], $arFieldsBlock);
		CSaleOrder::PayOrder($arOrder["ID"], "Y");
		unset($_SESSION['PAYTURE_PAYMENT_'.$arOrder["ID"]]);
		ob_exit();
	}
	if($SessionType == 'Pay')
	{
		$arFieldsSuccess["PS_STATUS_MESSAGE"] = GetMessage("IMS.PAYTURE_PAYMENT_FOR_ORDER_SUCCESFUL", array("#ORDER_ID#" => $OrderNumber, "#CARDTYPE#" => $carttype, "#CARDNUMBER#" => $CardNumber));
		$arFieldsSuccess["PS_STATUS_DESCRIPTION"] = $arFieldsSuccess["PS_STATUS_MESSAGE"];
		 AddMessage2Log($arFieldsBlock["PS_STATUS_DESCRIPTION"], $module_id);
		CSaleOrder::PayOrder($arOrder["ID"], "Y");
		CSaleOrder::Update($arOrder["ID"], $arFieldsSuccess);
		unset($_SESSION['PAYTURE_PAYMENT_'.$arOrder["ID"]]);
		ob_exit();
	}
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>