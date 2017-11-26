<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("sale")){
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}
if (!CModule::IncludeModule("catalog")){
	ShowError(GetMessage("CATALOG_MODULE_NOT_INSTALL"));
	return;
}



session_start();

if ($_POST["ORDER_PROP_DATE"]){
	$arResult["ORDER_PROP_DATE"]=$_POST["ORDER_PROP_DATE"];
	$_SESSION["ORDER_PROP_DATE"]=$_POST["ORDER_PROP_DATE"];
}

if ($_POST["ORDER_PROP_TIME"]){
	$arResult["ORDER_PROP_TIME"]=$_POST["ORDER_PROP_TIME"];
	$_SESSION["ORDER_PROP_TIME"]=$_POST["ORDER_PROP_TIME"];
}

if ($_POST["ORDER_PROP_PERSONAL_CITY"]){
	$arResult["ORDER_PROP_PERSONAL_CITY"]=$_POST["ORDER_PROP_PERSONAL_CITY"];
	$_SESSION["ORDER_PROP_PERSONAL_CITY"]=$_POST["ORDER_PROP_PERSONAL_CITY"];
}

if ($_POST["ORDER_PROP_PERSONAL_STREET"]){
	$arResult["ORDER_PROP_PERSONAL_STREET"]=$_POST["ORDER_PROP_PERSONAL_STREET"];
	$_SESSION["ORDER_PROP_PERSONAL_STREET"]=$_POST["ORDER_PROP_PERSONAL_STREET"];
}

if ($_POST["ORDER_PROP_NAME"]){
	$arResult["ORDER_PROP_NAME"]=$_POST["ORDER_PROP_NAME"];
	$_SESSION["ORDER_PROP_NAME"]=$_POST["ORDER_PROP_NAME"];
}

if ($_POST["ORDER_PROP_PERSONAL_PHONE"]){
	$arResult["ORDER_PROP_PERSONAL_PHONE"] = str_replace(array("+", "-", "(", ")", " "), "", $_POST["ORDER_PROP_PERSONAL_PHONE"]);
	if ($arResult["ORDER_PROP_PERSONAL_PHONE"][0]=="7")
		$arResult["ORDER_PROP_PERSONAL_PHONE"][0]="8";
	$_SESSION["ORDER_PROP_PERSONAL_PHONE"]=$arResult["ORDER_PROP_PERSONAL_PHONE"];
}

if ($_POST["ORDER_PROP_USER_LOGIN"]){
	$arResult["ORDER_PROP_USER_LOGIN"]=$_POST["ORDER_PROP_USER_LOGIN"];
	$_SESSION["ORDER_PROP_USER_LOGIN"]=$_POST["ORDER_PROP_USER_LOGIN"];
}

if ($_POST["ORDER_PROP_USER_PASSWORD"])
	$arResult["ORDER_PROP_USER_PASSWORD"]=$_POST["ORDER_PROP_USER_PASSWORD"];

if ($_POST["PAY_SYSTEM_ID"])
	$arResult["PAY_SYSTEM_ID"]=$_POST["PAY_SYSTEM_ID"];

$timeNowPlus2Hours=date('H:i',time()+(2*60*60));

if ($_SESSION["DURATION"] == (int)$_SESSION["DURATION"])
	$duration=$_SESSION["DURATION"].":00";
else
	$duration=round($_SESSION["DURATION"], 0, PHP_ROUND_HALF_DOWN).":30";

$tomorrow=date("d.m.Y", mktime(0, 0, 0, date("m"), date("d")+1, date("Y")));
$timeArray=array("08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30", "12:00", "12:30", "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30", "17:00", "17:30", "18:00", "18:30", "19:00", "19:30", "20:00");
$actualTime=array();

if ($_SESSION["ORDER_PROP_DATE"]){
	if ($_SESSION["ORDER_PROP_DATE"]==date("d.m.Y")){
		foreach ($timeArray as $arTime){
			if ($arTime>$timeNowPlus2Hours){
				$res = strtotime($arTime) + strtotime($duration) -strtotime("00:00:00");
				$res2300=strtotime(date($_SESSION["ORDER_PROP_DATE"]." 23:00"));
				if ($res2300>=$res)
					$actualTime[]=$arTime;
			}
		}
	}elseif ($_SESSION["ORDER_PROP_DATE"]==$tomorrow){
		foreach ($timeArray as $arTime){
			$res = strtotime(date($_SESSION["ORDER_PROP_DATE"]." ".$arTime)) + strtotime($duration) -strtotime("00:00:00");
			$res2300=strtotime(date($_SESSION["ORDER_PROP_DATE"]." 23:00"));

			if ($res2300>=$res){
				if(date("H:i")>"19:00"){
					if ($arTime>="12:00")
						$actualTime[]=$arTime;
				}else
					$actualTime[]=$arTime;
			}
		}
	}else{
		foreach ($timeArray as $arTime){
			$res = strtotime(date($_SESSION["ORDER_PROP_DATE"]." ".$arTime)) + strtotime($duration) -strtotime("00:00:00");
			$res2300=strtotime(date($_SESSION["ORDER_PROP_DATE"]." 23:00"));

			if ($res2300>=$res)
				$actualTime[]=$arTime;
		}
	}
}

$arResult["actualTime"]=$actualTime;
$arResult["step"]="form";

if ($USER->IsAuthorized() || $_SESSION['LAZYLINK']){
	$_SESSION["confirmPhone"]=1;
}


if ($_POST["confirm_code"] && !$_SESSION["confirmPhone"]){
	if (trim($_POST["confirm_code"])==$_SESSION["checkCode"]){
		$arResult["step"]="payment";
		$_SESSION["confirmPhone"]=1;
	}else{
		$arResult["step"]="checkPhone";
		$arResult["errorText"]="Неверный код!";
	}
}elseif (!$_SESSION["confirmPhone"] && $_POST["submit"] && $arResult["ORDER_PROP_DATE"] && $arResult["ORDER_PROP_TIME"] && $arResult["ORDER_PROP_PERSONAL_CITY"] && $arResult["ORDER_PROP_PERSONAL_STREET"] && $arResult["ORDER_PROP_NAME"] && $arResult["ORDER_PROP_PERSONAL_PHONE"] && $arResult["ORDER_PROP_USER_LOGIN"] && ($arResult["ORDER_PROP_USER_PASSWORD"] || $USER->IsAuthorized())){

	if (!$USER->IsAuthorized()){
		$rsUser = CUser::GetByLogin($arResult["ORDER_PROP_USER_LOGIN"]);
		$arUser = $rsUser->Fetch();
		if ($arUser["ID"])
			$arResult["errorLogin"]="Пользователь с таким логином уже зарегистрирован!";
	}

	if ($arResult["errorLogin"])
		$arResult["step"]="form";
	else{
		$arResult["step"]="checkPhone";
		$code=rand(100, 999);
		$arResult["checkCode"]=$code;
		$_SESSION["checkCode"]=$code;
		$sms="Ваш код подтверждения MaxClean: ".$code;
		sendsms($arResult["ORDER_PROP_PERSONAL_PHONE"], $sms);
	}
}elseif ($_SESSION["confirmPhone"] && $_POST["submit"] && $arResult["ORDER_PROP_DATE"] && $arResult["ORDER_PROP_TIME"] && $arResult["ORDER_PROP_PERSONAL_CITY"] && $arResult["ORDER_PROP_PERSONAL_STREET"] && $arResult["ORDER_PROP_NAME"] && $arResult["ORDER_PROP_PERSONAL_PHONE"] && $arResult["ORDER_PROP_USER_LOGIN"] && ($arResult["ORDER_PROP_USER_PASSWORD"] || $USER->IsAuthorized())){
	$arResult["step"]="payment";
}

if ($_GET["ORDER_ID"]){

	$arOrder = CSaleOrder::GetByID($_SESSION["ORDER_ID"]);

	$arResult["ORDER"]=$arOrder;

	$arResult["PAY_SYSTEM_ID"]=$arOrder["PAY_SYSTEM_ID"];

	if ($arOrder){
		if (IntVal($arOrder["PAY_SYSTEM_ID"]) > 0){
			$dbPaySysAction = CSalePaySystemAction::GetList(
				array(),
				array(
					"PAY_SYSTEM_ID" => $arOrder["PAY_SYSTEM_ID"],
					"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"]
				),
				false,
				false,
				array("NAME", "ACTION_FILE", "NEW_WINDOW", "PARAMS", "ENCODING")
			);
			if ($arPaySysAction = $dbPaySysAction->Fetch()){
				$arPaySysAction["NAME"] = htmlspecialcharsEx($arPaySysAction["NAME"]);
				if (strlen($arPaySysAction["ACTION_FILE"]) > 0)
				{
					if ($arPaySysAction["NEW_WINDOW"] != "Y")
					{
						CSalePaySystemAction::InitParamArrays($arOrder, $ID, $arPaySysAction["PARAMS"]);

						$pathToAction = $_SERVER["DOCUMENT_ROOT"].$arPaySysAction["ACTION_FILE"];

						$pathToAction = str_replace("\\", "/", $pathToAction);
						while (substr($pathToAction, strlen($pathToAction) - 1, 1) == "/")
							$pathToAction = substr($pathToAction, 0, strlen($pathToAction) - 1);

						if (file_exists($pathToAction))
						{
							if (is_dir($pathToAction) && file_exists($pathToAction."/payment.php"))
								$pathToAction .= "/payment.php";

							$arPaySysAction["PATH_TO_ACTION"] = $pathToAction;
						}

						if ( strlen($arPaySysAction["ENCODING"]) > 0)
						{
							define("BX_SALE_ENCODING", $arPaySysAction["ENCODING"]);
							AddEventHandler("main", "OnEndBufferContent", "ChangeEncoding");
							function ChangeEncoding($content)
							{
								global $APPLICATION;
								header("Content-Type: text/html; charset=".BX_SALE_ENCODING);
								$content = $APPLICATION->ConvertCharset($content, SITE_CHARSET, BX_SALE_ENCODING);
								$content = str_replace("charset=".SITE_CHARSET, "charset=".BX_SALE_ENCODING, $content);
							}
						}
					}
				}
				$arResult["PAY_SYSTEM"] = $arPaySysAction;
			}
		}

		$arResult["ORDER"] = $arOrder;

		$basketRes = CSaleBasket::GetList(Array("ID"=>"ASC"), Array("ORDER_ID"=>$arOrder["ID"]));
		$basket = array();
		while ($basketItem = $basketRes->fetch()) {
			if (check_square_id($basketItem['PRODUCT_ID'])) {
				$basketItem['PROPERTIES'] = array('MUSTBE' => array('VALUE' => 'да'));
			}
			$basket[] = $basketItem;
		}
		$arResult['BASKET_ITEMS'] = $basket;
		$arDateInsert = explode(" ", $arOrder["DATE_INSERT"]);
		if (is_array($arDateInsert) && count($arDateInsert) > 0)
			$arResult["ORDER"]["DATE_INSERT_FORMATED"] = $arDateInsert[0];
		else
			$arResult["ORDER"]["DATE_INSERT_FORMATED"] = $arOrder["DATE_INSERT"];

		if ( CModule::IncludeModule("statistic"))
		{
			$event1 = "eStore";
			$event2 = "order_confirm";
			$event3 = $arResult["ORDER"]["ID"];

			$e = $event1."/".$event2."/".$event3;

			if ( !is_array($_SESSION["ORDER_EVENTS"]) || (is_array($_SESSION["ORDER_EVENTS"]) && !in_array($e, $_SESSION["ORDER_EVENTS"]))) // check for event in session
			{
				CStatistic::Set_Event($event1, $event2, $event3);
				$_SESSION["ORDER_EVENTS"][] = $e;
			}
		}

		foreach(GetModuleEvents("sale", "OnSaleComponentOrderComplete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, Array($arOrder["ID"], $arOrder));
	}

	$arResult["step"]="ok";
}

if ($_SESSION["confirmPhone"] && $arResult["PAY_SYSTEM_ID"] && $arResult["ORDER_PROP_DATE"] && $arResult["ORDER_PROP_TIME"] && $arResult["ORDER_PROP_PERSONAL_CITY"] && $arResult["ORDER_PROP_PERSONAL_STREET"] && $arResult["ORDER_PROP_NAME"] && $arResult["ORDER_PROP_PERSONAL_PHONE"] && $arResult["ORDER_PROP_USER_LOGIN"] && ($arResult["ORDER_PROP_USER_PASSWORD"] || $USER->IsAuthorized())){
	if (!$USER->IsAuthorized()){
		$arAuthResult = $USER->Register($arResult["ORDER_PROP_USER_LOGIN"], $arResult["ORDER_PROP_NAME"], '', $arResult["ORDER_PROP_USER_PASSWORD"], $arResult["ORDER_PROP_USER_PASSWORD"], $arResult["ORDER_PROP_USER_LOGIN"], false);


		if ($arAuthResult != False && $arAuthResult["TYPE"] == "ERROR" ){
			$arResult["ERROR_MESSAGE"] .= GetMessage("STOF_ERROR_REG").((strlen($arAuthResult["MESSAGE"]) > 0) ? ": ".$arAuthResult["MESSAGE"] : ".<br />" );
		}else {
			$USER->IsAuthorized();
		}
	}

	$USER_ID = $USER->getId();

	$fieldsUser = Array(
		"NAME"              => $arResult["ORDER_PROP_NAME"],
		"PERSONAL_PHONE"    => $arResult["ORDER_PROP_PERSONAL_PHONE"],
		"PERSONAL_STREET"	=> $arResult["ORDER_PROP_PERSONAL_STREET"],
		"PERSONAL_CITY"		=> $arResult["ORDER_PROP_PERSONAL_CITY"]
	);

	if ($_SESSION["MNOGORU"])
		$fieldsUser["UF_MNOGORU"]=$_SESSION["MNOGORU"];


	$USER->Update($USER_ID, $fieldsUser);


	$arFields = array(
		"LID" => SITE_ID,
		"PERSON_TYPE_ID" => 1,
		"PAYED" => "N",
		"CANCELED" => "N",
		"STATUS_ID" => "N",
		"PRICE" => 0,
		"CURRENCY" => "RUB",
		"USER_ID" => IntVal($USER_ID),
		"PAY_SYSTEM_ID" => $arResult["PAY_SYSTEM_ID"],
		"PRICE_DELIVERY" => 0,
		"DELIVERY_ID" => false,
		"DISCOUNT_VALUE" => 0
	);

	$arResult["ORDER_ID"] = CSaleOrder::Add($arFields);

	if ($arResult["ORDER_ID"]){
		$arResult["ORDER_PROP_podpiska"]=$_SESSION["periodName"];
		$arResult["ORDER_PROP_DURATION"]=$_SESSION["DURATION"];
		$arResult["ORDER_PROP_EMAIL"]=$arResult["ORDER_PROP_USER_LOGIN"];
		$arResult["ORDER_PROP_Cleaner"]=0;

		$FUSER_ID = CSaleBasket::GetBasketUserID();
		CSaleBasket::OrderBasket($arResult["ORDER_ID"], $FUSER_ID, SITE_ID, false);
		$dbOrderProperties = CSaleOrderProps::GetList(
			array(),
			array("PERSON_TYPE_ID" => 1, "ACTIVE" => "Y", "UTIL" => "N"),
			false,
			false,
			array("ID", "NAME", "CODE")
		);

		while ($arOrderProperties = $dbOrderProperties->Fetch()){
			$curVal = $arResult["ORDER_PROP_".$arOrderProperties["CODE"]];
			if (strlen($curVal) > 0){
				if ($arOrderProperties["CODE"]=="PERSONAL_CITY"){
					if ($arResult["ORDER_PROP_".$arOrderProperties["CODE"]]=="Москва")
						$value=618;
					elseif ($arResult["ORDER_PROP_".$arOrderProperties["CODE"]]=="Санкт-Петербург")
						$value=617;
				}else
					$value=$arResult["ORDER_PROP_".$arOrderProperties["CODE"]];

				$arFields = array(
					"ORDER_ID" => $arResult["ORDER_ID"],
					"ORDER_PROPS_ID" => $arOrderProperties["ID"],
					"NAME" => $arOrderProperties["NAME"],
					"CODE" => $arOrderProperties["CODE"],
					"VALUE" => $value
				);
				CSaleOrderPropsValue::Add($arFields);
			}
		}

		$arFieldsOrderUpdate = array(
			"STATUS_ID" => "N",
			"PRICE" => $_SESSION["periodTotalPrice"],
			"DISCOUNT_VALUE" => $_SESSION["periodDiscount"]
		);
		CSaleOrder::Update($arResult["ORDER_ID"], $arFieldsOrderUpdate);

		if ($_SESSION["periodName"]=="Один раз"){
			$smsClient="Спасибо, мы приняли ваш заказ #" . $arResult["ORDER_ID"] . " по адресу: "
				. $arResult["ORDER_PROP_PERSONAL_STREET"] . " на " . $arResult["ORDER_PROP_DATE"] . " " . $arResult["ORDER_PROP_TIME"] . ", " . $_SESSION["periodTotalPrice"] . " р. "
				."Подробнее 88002228330. С чистой душой, Ваш MaxClean.";
		}else{
			$smsClient="Вы успешно оформили подписку. Ближайшая уборка запланирована на " . $arResult["ORDER_PROP_DATE"] . " в " . $arResult["ORDER_PROP_TIME"] . ". Оставайтесь с Maxclean! 88002228330, Maxclean.help";
		}
		sendsms($arResult["ORDER_PROP_PERSONAL_PHONE"], $smsClient);

		$smsCurator="Новый заказ #".$arResult["ORDER_ID"].", " .$_SESSION["periodTotalPrice"]. "р., ".$arResult["ORDER_PROP_DATE"]." ".$arResult["ORDER_PROP_TIME"].", необходимо назначить клинера.";

		$phoneManager=array();
		if ($arResult["ORDER_PROP_PERSONAL_CITY"]=="Москва"){
			$phoneManager[]=MANAGER_PHONE_MSK;
			$phoneManager[]=MANAGER_PHONE_MSK2;
		}
		elseif ($arResult["ORDER_PROP_PERSONAL_CITY"]=="Санкт-Петербург")
			$phoneManager[]=MANAGER_PHONE_SPB;
		$phoneManager[]=VASILIEV_PHONE;

		foreach ($phoneManager as $arPhone)
			sendsms($arPhone, $smsCurator);

		$basketRes = CSaleBasket::GetList(Array("ID"=>"ASC"), Array("ORDER_ID"=>$arResult["ORDER_ID"]));
		$basket = array();
		while ($basketItem = $basketRes->fetch()){
			$basket[] = $basketItem;
		}
		$arResult['BASKET_ITEMS'] = $basket;

		$durationArray = explode(".", $arResult["ORDER_PROP_DURATION"]);
		if ($durationArray[1])
			$duration2=":30";
		else
			$duration2=":00";
		$duration=$durationArray[0].$duration2;
		$secs = strtotime($duration) - strtotime("00:00:00");
		$base = strtotime(mb_ereg('\:00', $arResult["ORDER_PROP_TIME"]) ? $arResult["ORDER_PROP_TIME"] : $arResult["ORDER_PROP_TIME"] . ':00');
		$timeFinish=date("H:i", $base + $secs);

		foreach ($arResult["BASKET_ITEMS"] as $arItemBasket){
			if (check_square_id($arItemBasket["PRODUCT_ID"]))
				$ploshad=$arItemBasket["NAME"];
			else{
				if ($arItemBasket["NAME"]=="Помыть окна")
					$uslugiLine.="<p>".$arItemBasket["NAME"]." (".intval($arItemBasket["QUANTITY"])." шт.)</p>";
				elseif ($arItemBasket["NAME"]=="Убраться на балконе")
					$uslugiLine.="<p>".$arItemBasket["NAME"]."</p>";
				else
					$uslugiLine.="<p>Помыть ".$arItemBasket["NAME"]."</p>";
			}
		}

		$arEventFields = array(
			"ORDER_ID"  	=>  $arResult["ORDER_ID"],
			"EMAIL"  		=>  $arResult["ORDER_PROP_EMAIL"],
			"NAME"			=>	$arResult["ORDER_PROP_NAME"],
			"DATE"			=>	$arResult["ORDER_PROP_DATE"],
			"PLOSHAD"		=>	$ploshad. " кв.м.",
			"USLUGI_LINE"	=>	$uslugiLine,
			"ADDRESS"		=>	$arResult["ORDER_PROP_PERSONAL_CITY"].", ".$arResult["ORDER_PROP_PERSONAL_STREET"],
			"PHONE"			=>	$arResult["ORDER_PROP_PERSONAL_PHONE"],
			"PRICE"			=>	number_format($_SESSION["periodTotalPrice"], 0, '.', '&nbsp;'),
			"TOTAL_TIME"	=>	$arResult["ORDER_PROP_DURATION"],
			"TIME"			=>	$arResult["ORDER_PROP_TIME"] ." - ".$timeFinish." (~".$arResult["ORDER_PROP_DURATION"].")"
		);


		if ($arResult["ORDER_PROP_PERSONAL_CITY"]=="Москва")
			$arEventFields["EMAIL_MANAGER"] = "v.sazhnev@naibecar.com, d.martemyanov@naibecar.com";
		elseif ($arResult["ORDER_PROP_PERSONAL_CITY"]=="Санкт-Петербург")
			$arEventFields["EMAIL_MANAGER"] = "v.zdobnyakova@naibecar.com";


		CEvent::Send("NEW_ORDER", "s1", $arEventFields, "N", 50);
		CEvent::Send("NEW_ORDER", "s1", $arEventFields, "N", 51);

		$_SESSION["ORDER_ID"]=$arResult["ORDER_ID"];

		CCatalogDiscountCoupon::ClearCoupon();

		LocalRedirect($arParams["PATH_TO_ORDER"]."?ORDER_ID=".$arResult["ORDER_ID"]);
	}
}

if ($arResult["step"]=="payment"){
	$arResult["PAY_SYSTEM"] = Array();
	$arFilter = array(
		"ACTIVE" => "Y",
		"PERSON_TYPE_ID" => $arResult["PERSON_TYPE"],
		"PSA_HAVE_PAYMENT" => "Y"
	);
	$deliv = $arResult["DELIVERY_ID"];
	if (is_array($arResult["DELIVERY_ID"]))
		$deliv = $arResult["DELIVERY_ID"][0].":".$arResult["DELIVERY_ID"][1];
	if ( !empty($arParams["DELIVERY2PAY_SYSTEM"])){
		foreach($arParams["DELIVERY2PAY_SYSTEM"] as $val){
			if ( is_array($val[$deliv])){
				foreach($val[$deliv] as $v)
					$arFilter["ID"][] = $v;
			}
			elseif (IntVal($val[$deliv])>0)
				$arFilter["ID"][] = $val[$deliv];
		}
	}

	//select delivery to pay
	$bShowDefault = False;
	$arD2P = array();
	$dbRes = CSaleDelivery::GetDelivery2PaySystem(array("DELIVERY_ID" => $deliv));
	while ($arRes = $dbRes->Fetch())
	{
		$arD2P[] = $arRes["PAYSYSTEM_ID"];
		$bShowDefault = True;
	}


	$dbPaySystem = CSalePaySystem::GetList(
		array("SORT" => "ASC", "PSA_NAME" => "ASC"),
		$arFilter
	);

	$bFirst = True;
	while ($arPaySystem = $dbPaySystem->Fetch())
	{
		if (!$bShowDefault || in_array($arPaySystem["ID"], $arD2P))
		{
			if ($arPaySystem["PSA_LOGOTIP"] > 0)
				$arPaySystem["PSA_LOGOTIP"] = CFile::GetFileArray($arPaySystem["PSA_LOGOTIP"]);

			if (IntVal($arResult["PAY_SYSTEM_ID"]) == IntVal($arPaySystem["ID"]) || IntVal($arResult["PAY_SYSTEM_ID"]) <= 0 && $bFirst)
				$arPaySystem["CHECKED"] = "Y";
			$arPaySystem["PSA_NAME"] = htmlspecialcharsEx($arPaySystem["PSA_NAME"]);
			$arResult["PAY_SYSTEM"][] = $arPaySystem;
			$bFirst = false;
		}
	}
}

//echo "<pre>"; print_r($arBasketItems); echo "</pre>";
//echo "<pre>"; print_r($_POST); echo "</pre>";
//echo "<pre>"; print_r($_SESSION); echo "</pre>";
//echo "<pre>"; print_r($_COOKIE); echo "</pre>";
//echo "<pre>"; print_r($arResult); echo "</pre>";

$this->IncludeComponentTemplate();
?>