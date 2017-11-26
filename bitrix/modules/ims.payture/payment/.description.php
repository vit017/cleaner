<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
IncludeModuleLangFile(__FILE__);

$psTitle = GetMessage("PAYTURE_DTITLE");
$psDescription = GetMessage("PAYTURE_DDESCR");

$arPSCorrespondence = array(
		"SHOP_ID" => array(
				"NAME" => GetMessage("SHOP_ID"),
				"DESCR" => GetMessage("SHOP_ID_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"HOST_CONNECT" => array(
				"NAME" => GetMessage("HOST_CONNECT"),
				"DESCR" => GetMessage("HOST_CONNECT_DESCR"),
				"VALUE" => "sandbox.payture.com",
				"TYPE" => ""
			),
		"SHOP_KEY" => array(
				"NAME" => GetMessage("SHOP_KEY"),
				"DESCR" => GetMessage("SHOP_KEY_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"ORDER_ID" => array(
				"NAME" => GetMessage("ORDER_ID"),
				"DESCR" => GetMessage("ORDER_ID_DESCR"),
				"VALUE" => "ID",
				"TYPE" => "ORDER"
			),		
		"ORDER_DATE" => array(
				"NAME" => GetMessage("ORDER_DATE"),
				"DESCR" => GetMessage("ORDER_DATE_DESCR"),
				"VALUE" => "DATE_INSERT_DATE",
				"TYPE" => "ORDER"
			),
		"SHOULD_PAY" => array(
				"NAME" => GetMessage("SHOULD_PAY"),
				"DESCR" => GetMessage("SHOULD_PAY_DESCR"),
				"VALUE" => "PRICE",
				"TYPE" => "ORDER"
			),
		"EMAIL" => array(
				"NAME" => "EMAIL",
				"DESCR" => "User's email for this order",
				"TYPE" => ""
			),
		"FINAL_URL" => array(
				"NAME" => GetMessage("FINAL_URL"),
				"DESCR" => GetMessage("FINAL_URL_DESC"),
				"VALUE" => "https://".$_SERVER["HTTP_HOST"]."/order/result/?result={success}",
				"TYPE" => ""
			),
		"SESSION_TYPE" => array(
				"NAME" => GetMessage("SESSION_TYPE"),
				"DESCR" => GetMessage("SESSION_TYPE_DESCR"),
				"VALUE" => "Pay",
				"TYPE" => "SELECT",
				"VALUE" => array(
					"Pay" => array(
						"NAME" => GetMessage("SESSION_PAY_TYPE"),
					),
					"Block" => array(
						"NAME" => GetMessage("SESSION_BLOCK_TYPE"),
					),
					
			),
		),
		"VIEW_TYPE" => array(
				"NAME" => GetMessage("VIEW_TYPE"),
				"DESCR" => GetMessage("VIEW_TYPE_DESCR"),
				"DVALUE" => "button",
				"TYPE" => "SELECT",
				"VALUE" => array(
					"button" => array(
						"NAME" => GetMessage("VIEW_TYPE_BUTTON"),
					),
					"current" => array(
						"NAME" => GetMessage("VIEW_TYPE_CURRENT"),
					),
					"iframe" => array(
						"NAME" => GetMessage("VIEW_TYPE_IFRAME"),
					),
					
			),
		),
		"IFRAME_WIDTH" => array(
				"NAME" => GetMessage("IFRAME_WIDTH"),
				"DESCR" => "",
				"VALUE" => "550",
				"TYPE" => ""
		),
		"IFRAME_HEIGHT" => array(
				"NAME" => GetMessage("IFRAME_HEIGHT"),
				"DESCR" => "",
				"VALUE" => "400",
				"TYPE" => ""
		),
	);
?>