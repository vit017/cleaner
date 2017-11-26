<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}
if (!CModule::IncludeModule("catalog"))
{
	ShowError(GetMessage("CATALOG_MODULE_NOT_INSTALL"));
	return;
}
$FUSER_ID = CSaleBasket::GetBasketUserID();
$arParams["PATH_TO_BASKET"] = Trim($arParams["PATH_TO_BASKET"]);
if (strlen($arParams["PATH_TO_BASKET"]) <= 0)
	$arParams["PATH_TO_BASKET"] = "basket.php";

$arParams["PATH_TO_PERSONAL"] = Trim($arParams["PATH_TO_PERSONAL"]);
if (strlen($arParams["PATH_TO_PERSONAL"]) <= 0)
	$arParams["PATH_TO_PERSONAL"] = "index.php";

$arParams["PATH_TO_PAYMENT"] = Trim($arParams["PATH_TO_PAYMENT"]);
if (strlen($arParams["PATH_TO_PAYMENT"]) <= 0)
	$arParams["PATH_TO_PAYMENT"] = "payment.php";

$arParams["PATH_TO_AUTH"] = Trim($arParams["PATH_TO_AUTH"]);
if (strlen($arParams["PATH_TO_AUTH"]) <= 0)
	$arParams["PATH_TO_AUTH"] = "/auth.php";

$arParams["ALLOW_PAY_FROM_ACCOUNT"] = (($arParams["ALLOW_PAY_FROM_ACCOUNT"] == "N") ? "N" : "Y");

$arParams["COUNT_DELIVERY_TAX"] = (($arParams["COUNT_DELIVERY_TAX"] == "Y") ? "Y" : "N");
$arParams["COUNT_DISCOUNT_4_ALL_QUANTITY"] = (($arParams["COUNT_DISCOUNT_4_ALL_QUANTITY"] == "Y") ? "Y" : "N");
$arParams["PATH_TO_ORDER"] = $APPLICATION->GetCurPage();
$arParams["SHOW_MENU"] = ($arParams["SHOW_MENU"] == "N" ? "N" : "Y" );
$arParams["ALLOW_EMPTY_CITY"] = ($arParams["CITY_OUT_LOCATION"] == "N" ? "N" : "Y" );

$arParams["SHOW_AJAX_LOCATIONS"] = $arParams["SHOW_AJAX_LOCATIONS"] == 'N' ? 'N' : 'Y';

$arParams['PRICE_VAT_SHOW_VALUE'] = $arParams['PRICE_VAT_SHOW_VALUE'] == 'N' ? 'N' : 'Y';

$arParams["ONLY_FULL_PAY_FROM_ACCOUNT"] = (($arParams["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y") ? "Y" : "N");
$arParams["SEND_NEW_USER_NOTIFY"] = (($arParams["SEND_NEW_USER_NOTIFY"] == "N") ? "N" : "Y");
$arResult["AUTH"]["new_user_registration_email_confirmation"] = ((COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y") ? "Y" : "N");
$arResult["AUTH"]["new_user_registration"] = ((COption::GetOptionString("main", "new_user_registration", "Y") == "Y") ? "Y" : "N");

$hours = array('час', 'часа', 'часов');
$PRICE_TYPE = bhTools::getPriceType();
$bUseAccountNumber = (COption::GetOptionString("sale", "account_number_template", "") !== "") ? true : false;

if ( strlen($arResult["POST"]["ORDER_PRICE"])>0)
	$arResult["ORDER_PRICE"]  = doubleval($arResult["POST"]["ORDER_PRICE"]);
if ( strlen($arResult["POST"]["ORDER_WEIGHT"])>0)
	$arResult["ORDER_WEIGHT"] = doubleval($arResult["POST"]["ORDER_WEIGHT"]);

$arResult["WEIGHT_UNIT"] = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_unit', "", SITE_ID));
$arResult["WEIGHT_KOEF"] = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_koef', 1, SITE_ID));

$GLOBALS['CATALOG_ONETIME_COUPONS_BASKET']=null;
$GLOBALS['CATALOG_ONETIME_COUPONS_ORDER']=null;

$allCurrency = CSaleLang::GetLangCurrency(SITE_ID);
if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid())
{
	foreach($_POST as $k => $v)
	{
		if ( !is_array($v))
		{
			$arResult["POST"][$k] = htmlspecialcharsex($v);
			$arResult["POST"]['~'.$k] = $v;
		}
		else
		{
			foreach($v as $kk => $vv)
			{
				$arResult["POST"][$k][$kk] = htmlspecialcharsex($vv);
				$arResult["POST"]['~'.$k][$kk] = $vv;
			}
		}
//        if ($k == 'profilechanges' && (int)$_POST['ORDER_PROP_PERSONAL_CITY'] != $_SESSION['CITY_ID']) {
//            $_SESSION['CITY_ID'] = (int)$_POST['ORDER_PROP_PERSONAL_CITY'];
//        }
	}

	if ( !empty($arResult["POST"]) && !isset($_REQUEST["ORDER_ID"]))
		$_SESSION['ORDER'][$FUSER_ID] = $arResult["POST"];

	//костыли для города, адреса и e-mail
	if(isset($_SESSION['street'])){
		$arResult['PRINT_PROPS_FORM']['USER_PROPS_N']['PERSONAL_STREET']['VALUE']=$arResult['PERSONAL_STREET']['VALUE']=$_SESSION['street'];
	}
	if(isset($_SESSION['CITY_ID'])){
		$arResult['PRINT_PROPS_FORM']['USER_PROPS_N']['PERSONAL_CITY']['VALUE']=$_SESSION['CITY_ID'];
	}
}
if ( !empty($_SESSION['ORDER'][$FUSER_ID]) && empty($arResult["POST"]))
	$arResult["POST"] = $_SESSION['ORDER'][$FUSER_ID];

$arResult["SKIP_SECOND_STEP"] = (($arResult["POST"]["SKIP_SECOND_STEP"] == "Y") ? "Y" : "N");
$arResult["SKIP_THIRD_STEP"] = (($arResult["POST"]["SKIP_THIRD_STEP"] == "Y") ? "Y" : "N");
$arResult["SKIP_FORTH_STEP"] = (($arResult["POST"]["SKIP_FORTH_STEP"] == "Y") ? "Y" : "N");
$arResult["ERRORS"] = array();
if ( strlen($arResult["POST"]["PERSON_TYPE"])>0)
	$arResult["PERSON_TYPE"] = IntVal($arResult["POST"]["PERSON_TYPE"]);

$arResult["PROFILE_ID"] = 0;

if ( strlen($arResult["POST"]["DELIVERY_ID"])>0)
{
	if (strpos($arResult["POST"]["DELIVERY_ID"], ":") === false)
		$arResult["DELIVERY_ID"] = IntVal($arResult["POST"]["DELIVERY_ID"]);
	else
		$arResult["DELIVERY_ID"] = explode(":", $arResult["POST"]["DELIVERY_ID"]);
}
if ( strlen($arResult["POST"]["PAY_SYSTEM_ID"])>0)
	$arResult["PAY_SYSTEM_ID"] = IntVal($arResult["POST"]["PAY_SYSTEM_ID"]);
if ( strlen($arResult["POST"]["PAY_CURRENT_ACCOUNT"])>0)
	$arResult["PAY_CURRENT_ACCOUNT"] = $arResult["POST"]["PAY_CURRENT_ACCOUNT"];
else
	$arResult["PAY_CURRENT_ACCOUNT"] = "N";
if ( strlen($arResult["POST"]["TAX_EXEMPT"])>0)
	$arResult["TAX_EXEMPT"] = $arResult["POST"]["TAX_EXEMPT"];
if ( strlen($arResult["POST"]["ORDER_DESCRIPTION"])>0)
	$arResult["ORDER_DESCRIPTION"] = trim($arResult["POST"]["ORDER_DESCRIPTION"]);
if ( strlen($arResult["POST"]["VALID_COUPON"])>0)
	$arResult["VALID_COUPON"] = trim($arResult["POST"]["VALID_COUPON"]);

$arResult["BACK"] = (($arResult["POST"]["BACK"] == "Y") ? "Y" : "");

if ( $arResult["BACK"] != 'Y' ){
	if ($_REQUEST["CurrentStep"] < 6 && (('Y' == $arResult["PAY_CURRENT_ACCOUNT"] && strlen($arResult["POST"]["current_b"]) == 0) || ($arResult["PAY_CURRENT_ACCOUNT"] == 'N' && strlen($arResult["POST"]["current_b"]) > 0))) {
		$_REQUEST["CurrentStep"] = $arResult["POST"]["CurrentStep"] = $arResult["CurrentStepTmp"] = 4;

	} elseif ($_REQUEST["CurrentStep"] < 6 && $arResult["POST"]["current_b"] == $arResult["PAY_CURRENT_ACCOUNT"] && isset($arResult["POST"]["current_b"]) && $arResult["PAY_CURRENT_ACCOUNT"] == 'Y' && $arResult['POST']['ORDER_PRICE'] == $arResult['POST']['CURRENT_BUDGET']) {
		$_REQUEST["CurrentStep"] = $arResult["POST"]["CurrentStep"] = $arResult["CurrentStep"] = $arResult["CurrentStepTmp"] = 6;
		$arResult["PAY_SYSTEM_ID"] = 1;

	} elseif ($_REQUEST["CurrentStep"] < 6 && isset($arResult['POST']['summ_to_pay']) && $arResult['POST']['summ_to_pay'] == 0) {
		$_REQUEST["CurrentStep"] = $arResult["POST"]["CurrentStep"] = $arResult["CurrentStep"] = $arResult["CurrentStepTmp"] = 6;
		$arResult["PAY_SYSTEM_ID"] = 1;
	}
}
if ($_REQUEST["CurrentStep"] == 7 || ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid()))
{
	if ( strlen($_REQUEST["ORDER_ID"])>0)
		$ID = urldecode(urldecode($_REQUEST["ORDER_ID"]));
	if ( IntVal($_REQUEST["CurrentStep"])>0)
		$arResult["CurrentStep"] = IntVal($_REQUEST["CurrentStep"]);
	if ( IntVal($_REQUEST["CurrentStep"])>0)
		$CurrentStepTmp = IntVal($_REQUEST["CurrentStep"]);
	elseif ( IntVal($arResult["POST"]["CurrentStep"])>0)
		$CurrentStepTmp = IntVal($arResult["POST"]["CurrentStep"]);
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && $arResult["BACK"] == 'Y' && check_bitrix_sessid())
{
	if ( $arResult["POST"]["CurrentStep"] == 6 )
		$arResult["CurrentStepTmp"] = 3;
	else
		$arResult["CurrentStepTmp"] = $CurrentStepTmp;

	if ( IntVal($arResult["CurrentStepTmp"])>0)
		$arResult["CurrentStep"] = $arResult["CurrentStepTmp"];
	else
		$arResult["CurrentStep"] = $arResult["CurrentStep"] - 2;
	$arResult["BACK"] = "Y";
}

if ($arResult["CurrentStep"] <= 0 || !$arResult["CurrentStep"]  ){
	$arResult["CurrentStep"] = 1;
	//unset($_SESSION['BH_SAVE_DATE_TIME']);
}
$arResult["ERROR_MESSAGE"] = "";

if ( $arResult["CurrentStep"]>1 && $arResult["POST"]["USER_LOGOUT"] == "Y" && $USER->IsAuthorized() ){
	$FUSER_ID = bhBasket::update($FUSER_ID, true);
}elseif ( $arResult["CurrentStep"]>1 && $arResult["POST"]["USER_LOGOUT"] == "Y" ){
	$arResult["POST"]["USER_PASSWORD"] = '';
}

/*******************************************************************************/
/*****************  ACTION  ****************************************************/
/*******************************************************************************/
if (!$USER->IsAuthorized())
{
	$arResult["USER_LOGIN"] = ((strlen($arResult["POST"]["USER_LOGIN"]) > 0) ? $arResult["POST"]["USER_LOGIN"] : htmlspecialcharsbx(${COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"}));

	$arResult["USER_PASSWORD"] = ((strlen($arResult["POST"]["USER_PASSWORD"]) > 0) ? $arResult["POST"]["USER_PASSWORD"] : '');
	$arResult["AUTH"]["new_user_registration"] = ((COption::GetOptionString("main", "new_user_registration", "Y") == "Y") ? "Y" : "N");

	if ( $_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid())
	{
		if ( $arResult["POST"]["do_authorize"] == "on" ){
			if (strlen($arResult["POST"]["USER_LOGIN"]) <= 0 ){
				$arResult["ERROR_MESSAGE"] .= GetMessage("STOF_ERROR_AUTH_LOGIN").".<br />";
				$arResult["USER_LOGIN_ERROR"] = true;
			}
		}
	}
}else{
	$arResult["USER_LOGIN"] = $USER->getLogin();
	$arResult["SHOW_EMAIL"] = true;
	$_SESSION['NO_SMS_CONFIRM'] = 'Y';
	$_SESSION['NO_SMS_CONFIRM_2'] = 'Y';
}

if ( $_SESSION["BH_SALE_BASKET_MESSAGE"])
	$arResult["BH_SALE_BASKET_MESSAGE"] = $_SESSION["BH_SALE_BASKET_MESSAGE"];
//if ( $arResult["USER_LOGIN"] != 'admin' ){

if ( $arResult['CurrentStep'] > 2 && $arResult['CurrentStep'] < 7 && !$_REQUEST["ORDER_ID"] ){
	$rsUser = CUser::GetByLogin($arResult["USER_LOGIN"]);
	//if ( $arResult["POST"]["AUTH"]=='Y' ){
	if (strlen($arResult["USER_LOGIN"]) <= 0) {
		if ($arResult["POST"]["AUTH"] == 'Y')
			$arResult["ERROR_MESSAGE"] .= GetMessage("STOF_ERROR_REG_EMAIL") . ".<br />";
		$arResult["ERRORS"]["USER_LOGIN"] = $arResult["ERROR_MESSAGE"];
		$arResult["USER_LOGIN_ERROR"] = true;
	} elseif (!check_email($arResult["USER_LOGIN"])) {
		$arResult["ERROR_MESSAGE"] .= GetMessage("STOF_ERROR_REG_BAD_EMAIL") . ".<br />";
		$arResult["ERRORS"]["USER_LOGIN"] = $arResult["ERROR_MESSAGE"];
		$arResult["USER_LOGIN_ERROR"] = true;
	} elseif ($arUser = $rsUser->Fetch() && !$USER->IsAuthorized()) {
		if ($arResult["POST"]["do_authorize"] == "on") {
			$arResult["ERROR_MESSAGE"] = 'Неверный логин или пароль';
		} else {
			$arResult["ERROR_MESSAGE"] = 'Такой email уже зарегистрирован';
            $arResult["ERROR_CODE"] = 'email';
		}
		$arResult["ERRORS"]["USER_LOGIN"] = $arResult["ERROR_MESSAGE"];
		$arResult["USER_LOGIN_ERROR"] = true;
	}
	if (!$USER->IsAuthorized() && ($arResult["POST"]["AUTH"] == 'Y' || strlen($arResult["USER_PASSWORD"]) <= 0)) {
		$arPolicy = CUSER::GetGroupPolicy(array());
		$passwordErrors = Cuser::CheckPasswordAgainstPolicy($arResult["USER_PASSWORD"], $arPolicy);
		if (!empty($passwordErrors)) {
			$arResult["ERROR_MESSAGE"] = implode("<br>", $passwordErrors);
			$arResult["ERRORS"]["USER_PASSWORD"] = implode("<br>", $passwordErrors);
			$arResult["USER_LOGIN_ERROR"] = true;
		}
	}
}
if ($arResult["CurrentStep"] == 1)
{
	if ( strlen($arResult['POST']['ORDER_PROP_DATE']) > 0 && strlen($arResult['POST']['ORDER_PROP_TIME']) > 0 && $arResult['BACK'] != 'Y' && isset($_SESSION['BH_SAVE_DATE_TIME']) && $_SESSION['BH_SAVE_DATE_TIME'] == true ){
		$arResult["CurrentStep"] = 2;
		unset($_SESSION['BH_SAVE_DATE_TIME']);
	}
};

if ( !$USER->getId())
	$USER_ID = 6;
else
	$USER_ID = $USER->getId();
$rsUser = CUser::GetByID($USER_ID);
$arUser = $rsUser->Fetch();

if ( $arResult['CurrentStep'] > 1 && $arResult['CurrentStep'] < 7 && !$_REQUEST["ORDER_ID"]) {
	if ( $USER->IsAuthorized() && $arResult['CurrentStep'] == 2 && $arResult["BACK"] != "Y" && $arUser['PERSONAL_CITY'] == $_SESSION['CITY_ID'] ){
		$arResult['CurrentStep'] = 3;
	}elseif ( $arResult["USER_LOGIN_ERROR"] && !$USER->IsAuthorized() ){
		$arResult['CurrentStep'] = 2;
	}

	if ( $arResult["USER_LOGIN_ERROR"] ){
		$arResult['POST']['AUTH'] = 'Y';
	}elseif ( strlen($arResult["ERROR_MESSAGE"])>0 ){
		$arResult['CurrentStep'] = 1;
	}
}

if ( true)
{
	$arResult["BASE_LANG_CURRENCY"] = CSaleLang::GetLangCurrency(SITE_ID);
	$arResult['HIDE_COUPONS'] = false;
	if ( $arResult["CurrentStep"] == 3 && strlen($_REQUEST["COUPON"]) <= 0 && strlen($_SESSION['SALE_COUPON_UTM'])>0 ){
		$arResult["POST"]["COUPON"] = $_SESSION['SALE_COUPON_UTM'];
		$arResult['HIDE_COUPONS'] = 'Y';
	}

	if ($arResult["CurrentStep"] > 0 && $arResult["CurrentStep"] <= 6)
	{
		if (strlen($arResult["POST"]["COUPON"]) > 0 ){
			$done_promos = file($_SERVER["DOCUMENT_ROOT"].'/logs/coupons_promos.txt');
			/*$oldPromo = false;
			foreach($done_promos as $line ){
				$arPromos = preg_split('[;]', $line);
				$user = $arPromos[0];
				if ( $user == $arResult['USER_LOGIN'] ){
					$promo = trim($arPromos[1]);
					if ( strtolower($promo)==strtolower(trim($arResult["POST"]["COUPON"])))
						$oldPromo = true;
				}
			}*/

			CCatalogDiscountCoupon::ClearCoupon();
			//if ( !$oldPromo) {
			$arResult["PERSON_TYPE"] = 1;

			$arResult["VALID_COUPON"] = CCatalogDiscountCoupon::SetCoupon($arResult["POST"]["COUPON"]);
			$discount_id = 0;
			if ( isset($arResult["VALID_COUPON"]) && $arResult["VALID_COUPON"] ) {
				$db = CCatalogDiscountCoupon::getList(array(), array('COUPON' => trim($arResult["POST"]["COUPON"])));
				if ($ar = $db->Fetch()) {
					if ( $ar['ONE_TIME'] == 'N' ){
						$discount_id = $ar['DISCOUNT_ID'];
					} else {
						$oldPromo = false;
						foreach($done_promos as $line ){
							$arPromos = preg_split('[;]', $line);
							$user = $arPromos[0];
							if ( $ar['ONE_TIME'] == 'Y' || $user == $arResult['USER_LOGIN'] ){
								$promo = trim($arPromos[1]);
								if ( strtolower($promo)==strtolower(trim($arResult["POST"]["COUPON"])))
									$oldPromo = true;
							}
						}
						if ( !$oldPromo ){
							$discount_id = $ar['DISCOUNT_ID'];
						}
					}

				}
				if ( $discount_id > 0 ){
					$discount = CCatalogDiscount::getByID($discount_id);
					if( CSalePersonType::GetByID($discount['SORT']))
						$arResult["PERSON_TYPE"] = $discount['SORT'];
					$_SESSION["VALID_COUPON_BH"] = $arResult["POST"]["COUPON"];
				} else {
					unset($arResult["VALID_COUPON"]);
				}
			}
			//}
			if ( $arResult["CurrentStep"] == 5)
				$arResult["CurrentStep"] = $arResult["CurrentStep"] -1;
		}

		if (!isset($arResult["VALID_COUPON"]) || (isset($arResult["VALID_COUPON"]) && $arResult["VALID_COUPON"] === false))
		{
			CCatalogDiscountCoupon::ClearCoupon();
			$arResult["PERSON_TYPE"] = 1;
			unset($_SESSION["VALID_COUPON_BH"]);
		}

		if ($arResult["PAY_CURRENT_ACCOUNT"] == "Y" && $arParams["ALLOW_PAY_FROM_ACCOUNT"] == "Y")
			$arResult["PAY_CURRENT_ACCOUNT"] = "Y";

		// <***************** BEFORE 1 STEP
		$bProductsInBasket = false;
		$arResult['BASKET_PRICE'] = 0;
		$arProductsInBasket = bhBasket::getRealBasket($FUSER_ID);

		$ProdIDs = array();
		$cnt = array();
		if ( count($arProductsInBasket) > 0 ){
			$bProductsInBasket = true;
			foreach ($arProductsInBasket as $basket){
				$arResult['BASKET_PRICE'] += $basket['PRICE'] * $basket['QUANTITY'];
				$ProdIDs[] = $basket['PRODUCT_ID'];
				$cnt[$basket['PRODUCT_ID']] = $basket['QUANTITY'];
			}
		}

		$arBasketProps = bhBasket::getBasketProps($ProdIDs);
		$totalTime = bhBasket::getDuration($ProdIDs, $cnt);

		$arOrder = array('BASKET_ITEMS'=>$arProductsInBasket, "USER_ID"=>$USER_ID, "SITE_ID"=>'s1', "PERSON_TYPE_ID" => $arResult["PERSON_TYPE"]);

		CSaleDiscount::DoProcessOrder($arOrder, $arOptions, $arErrors);
		$DISCOUNT_PRICE_ALL = floor($arResult['BASKET_PRICE'] - $arOrder['ORDER_PRICE']);
		$arResult['BASKET_ITEMS'] = $arOrder['BASKET_ITEMS'];

		$totalTime = $totalTime/60;
		$totalTime = round($totalTime, 1);
		$arResult["TOTAL_TIME"] = $totalTime;
		$arResult["TOTAL_TIME_FORMATED"] = $totalTime.' '.bhTools::words($totalTime, $hours);

		if ( $totalTime > 16-bhSettings::$SaveConst ){
			$arResult["ERROR_MESSAGE"] = "На уборку Вашей квартиры требуется более ".(16-bhSettings::$SaveConst)." часов! Напишите, пожалуйста, нам через <a href='/obratnaya-svyaz/#clee'>форму обратной связи</a> и мы обязательно свяжемся с Вами для обсуждения индивидуальных условий";
			$bProductsInBasket = false;
		}

		if (!$bProductsInBasket)
		{
			LocalRedirect($arParams["PATH_TO_BASKET"]);
		}

		if ( $arResult['CurrentStep'] > 1 && (strlen($arResult['POST']['ORDER_PROP_DATE']) <= 0 || strlen($arResult['POST']['ORDER_PROP_TIME']) <= 0 ) ){
			$arResult["ERROR_MESSAGE"] .= 'Выберите время уборки' . "<br />";
			$arResult["ERROR_CODE"] .= 'time';
			$arResult["CurrentStep"] = 1;
		}

		//
		if (strlen($arResult["ERROR_MESSAGE"]) <= 0 && $arResult["CurrentStep"] > 1)
		{
			// <***************** AFTER 1 STEP
			if ($arResult["PERSON_TYPE"] <= 0)
				$arResult["ERROR_MESSAGE"] .= GetMessage("SALE_NO_PERS_TYPE")."<br />";

			if (($arResult["PERSON_TYPE"] > 0) && !($arPersType = CSalePersonType::GetByID($arResult["PERSON_TYPE"])))
				$arResult["ERROR_MESSAGE"] .= GetMessage("SALE_PERS_TYPE_NOT_FOUND")."<br />";

			if (strlen($arResult["ERROR_MESSAGE"]) > 0)
				$arResult["CurrentStep"] = 1;
		}
		if (strlen($arResult["ERROR_MESSAGE"]) <= 0 && $arResult["CurrentStep"] > 2)
		{
			// <***************** AFTER 2 STEP
			$arFilter = array("PERSON_TYPE_ID" => $arResult["PERSON_TYPE"], "ACTIVE" => "Y", "UTIL" => "N");
			if ( !empty($arParams["PROP_".$arResult["PERSON_TYPE"]]))
				$arFilter["!ID"] = $arParams["PROP_".$arResult["PERSON_TYPE"]];

			$dbOrderProps = CSaleOrderProps::GetList(
				array("SORT" => "ASC"),
				$arFilter,
				false,
				false,
				array("ID", "NAME", "TYPE", "IS_LOCATION", "CODE", "IS_LOCATION4TAX", "IS_PROFILE_NAME", "IS_PAYER", "IS_EMAIL", "IS_ZIP", "REQUIED", "SORT")
			);
			$phone = false;

			while ($arOrderProps = $dbOrderProps->GetNext())
			{
                $bErrorField = False;
				$curVal = $arResult["POST"]["~ORDER_PROP_".$arOrderProps["CODE"]];
				if ( $arOrderProps["CODE"] == 'PERSONAL_PHONE' ){
					$phone = $curVal;
				}

				if ( $USER_ID != 6 && isset($arUser[$arOrderProps["CODE"]]) ){
					$curVal = strlen($arUser[$arOrderProps["CODE"]])<=0?$curVal:$arUser[$arOrderProps["CODE"]];
					if ( $arOrderProps["CODE"] == 'PERSONAL_PHONE' ){
						if ( strlen($phone)<=0 && strlen($curVal)>0 ){
							if ( bhTools::setConfirm($curVal, false, false) ){
								$phone = $curVal;
							}
						} elseif ( $phone==$curVal ){
							bhTools::setConfirm($curVal, false, false);
						} elseif ( strlen($phone)>0 && $phone != $curVal ){
							// sendConfirmCode($phone);
						}
					}
				}elseif ( !$phone && $_SESSION['PHONE_CONFIRM_NUMBER'] && $arOrderProps["CODE"] == 'PERSONAL_PHONE' ){
					$phone = $_SESSION['PHONE_CONFIRM_NUMBER'];
					$curVal = $phone;
				};

				if ($arOrderProps["TYPE"]=="LOCATION")
				{
					if ( isset($arResult["POST"]["NEW_LOCATION_".$arOrderProps["CODE"]]) && intval($arResult["POST"]["NEW_LOCATION_".$arOrderProps["CODE"]]) > 0 )
					{
						$curVal = intval($arResult["POST"]["NEW_LOCATION_".$arOrderProps["CODE"]]);
						$arResult["POST"]["ORDER_PROP_".$arOrderProps["CODE"]] = $curVal;
					}
				}
				if ($arOrderProps["TYPE"]=="LOCATION" && ($arOrderProps["IS_LOCATION"]=="Y" || $arOrderProps["IS_LOCATION4TAX"]=="Y"))
				{
					if ($arOrderProps["IS_LOCATION"]=="Y")
						$arResult["DELIVERY_LOCATION"] = IntVal($curVal);
					if ($arOrderProps["IS_LOCATION4TAX"]=="Y")
						$arResult["TAX_LOCATION"] = IntVal($curVal);

					if (IntVal($curVal)<=0) $bErrorField = True;
				}
				elseif ($arOrderProps["IS_PROFILE_NAME"]=="Y" || $arOrderProps["IS_PAYER"]=="Y" || $arOrderProps["IS_EMAIL"]=="Y" || $arOrderProps["IS_ZIP"]=="Y")
				{
					if ($arOrderProps["IS_PROFILE_NAME"]=="Y")
					{
						$arResult["PROFILE_NAME"] = Trim($curVal);
						if (strlen($arResult["PROFILE_NAME"])<=0)
							$bErrorField = True;
					}
					if ($arOrderProps["IS_PAYER"]=="Y")
					{
						$arResult["PAYER_NAME"] = Trim($curVal);
						if (strlen($arResult["PAYER_NAME"])<=0)
							$bErrorField = True;
					}
                    if ($arOrderProps["IS_EMAIL"]=="Y")
					{
						$arResult["USER_EMAIL"] = Trim($curVal);
						if (strlen($arResult["USER_EMAIL"])<=0 || !check_email($arResult["USER_EMAIL"]))
							$bErrorField = True;
					}
					if ( $arOrderProps["IS_ZIP"]=="Y")
					{
						$arResult["DELIVERY_LOCATION_ZIP"] = $curVal;
						if (strlen($arResult["DELIVERY_LOCATION_ZIP"])<=0)
							$bErrorField = True;
					}
				}
				elseif ($arOrderProps["REQUIED"]=="Y")
				{
					if ($arOrderProps["TYPE"]=="TEXT" || $arOrderProps["TYPE"]=="TEXTAREA" || $arOrderProps["TYPE"]=="RADIO" || $arOrderProps["TYPE"]=="SELECT" || $arOrderProps["TYPE"] == "CHECKBOX")
					{
						if (strlen($curVal)<=0)
							$bErrorField = True;
					}
					elseif ($arOrderProps["TYPE"]=="LOCATION")
					{
						if (IntVal($curVal)<=0)
							$bErrorField = True;
					}
					elseif ($arOrderProps["TYPE"]=="MULTISELECT")
					{
						if (!is_array($curVal) || count($curVal)<=0)
							$bErrorField = True;
					}
				}
				if ($bErrorField ){
					$arResult["ERROR_MESSAGE"] .= GetMessage("SALE_EMPTY_FIELD")." \"".$arOrderProps["NAME"]."\".<br />";
					$arResult["ERRORS"][$arOrderProps["CODE"]] = GetMessage("SALE_EMPTY_FIELD")." \"".$arOrderProps["NAME"] . '"';
				}
                //Костыль, поскольку поля не являются битриксовыми, их нельзя проверить нормальным способом.
                if (!$USER->IsAuthorized() && ($arResult['CurrentStep'] == 3 || $arResult["CurrentStep"] == 2.5)) {
                    if ($arOrderProps['CODE'] == 'PERSONAL_STREET') {
                        $reqFields = array('street' => 'Улица', 'house' => 'Дом', "flat" => "Квартира");
                        foreach ($reqFields as $fieldCode => $fieldName) {
                            if (!$_REQUEST[$fieldCode]) {
                                $arResult["ERROR_MESSAGE"] .= GetMessage("SALE_EMPTY_FIELD")." \"" . $fieldName . "\".<br />";
                                $arResult["ERRORS"][$fieldCode] = GetMessage("SALE_EMPTY_FIELD")." \"" . $fieldName . '"';
                            }
                            $bErrorField = true;

                        }

                        if (!$_REQUEST['street'] || !$_REQUEST['house'] || !$_REQUEST['flat']) {
                        }
                    }
                }
			}
			if ( strlen($arResult["ERROR_MESSAGE"]) > 0)
				$arResult["CurrentStep"] = 2;

			if ( $arResult["CurrentStep"] > 2 ){
				if ( $_SESSION['NO_SMS_CONFIRM'] == 'Y' ){
					bhTools::setConfirm($phone, false, false);
				}

				if ( $arResult['POST']['RESEND'] == 'on' ){
					bhTools::cancelConfirm();
					bhTools::sendConfirmCode($phone);
					$_SESSION['CONFIRM_CODE_RESEND'] = $_SESSION['PHONE_CONFIRM_CODE'];
					$arResult["CurrentStep"] = 2.5;
				//}elseif ( strlen($arResult['POST']['confirm_code'])>0 && $phone ){
				}elseif ( strlen($arResult['POST']['sms-pass'])>0 && $phone ){
					if ( bhTools::checkConfirm($phone, $arResult['POST']['sms-pass']) ){
						bhTools::setConfirm($phone, true, $arResult['POST']['sms-pass']);
						$arResult["CurrentStep"] = 4;
					}else{
						$arResult["ERROR_MESSAGE"] .= 'Неверный код подтверждения';
						$arResult["ERROR_MESSAGE"]["confirm_code"] = 'Неверный код подтверждения';
						$arResult["CurrentStep"] = 2.5;
					}

				}elseif ( strlen($phone)>0 && !$_SESSION['PHONE_CONFIRM_CODE_CONFIRMED'] && $_SESSION['PHONE_CONFIRM_CODE_SENT'] != "Y" ){
					bhTools::sendConfirmCode($phone);
					$arResult["CurrentStep"] = 2.5;
				}elseif ( strlen($phone)>0 &&  $_SESSION['PHONE_CONFIRM_NUMBER'] != $phone ){
					bhTools::sendConfirmCode($phone);
					$arResult["CurrentStep"] = 2.5;
				}
			}

		}

		if ( $arResult["CurrentStep"] > 2 && $_SESSION['PHONE_CONFIRM_CODE_SENT'] == 'Y' && !$_SESSION['PHONE_CONFIRM_CODE_CONFIRMED'] ){

			if ( $_SESSION['NO_SMS_CONFIRM'] == 'Y' ){
				bhTools::setConfirm($phone, false, false);
			} else {
				$arResult["CurrentStep"] = '2.5';
			}
		}

        if (strlen($arResult["ERROR_MESSAGE"]) <= 0 && $arResult["CurrentStep"] > 3)
		{
			// <***************** AFTER 3 STEP
			$arResult["TaxExempt"] = array();
			$arUserGroups = $USER->GetUserGroupArray();

			if ( $arResult["bUsingVat"] != "Y")
			{
				$dbTaxExemptList = CSaleTax::GetExemptList(array("GROUP_ID" => $arUserGroups));
				while ($TaxExemptList = $dbTaxExemptList->Fetch())
				{
					if (!in_array(IntVal($TaxExemptList["TAX_ID"]), $arResult["TaxExempt"]))
					{
						$arResult["TaxExempt"][] = IntVal($TaxExemptList["TAX_ID"]);
					}
				}
			}

			// DELIVERY

			$arResult["DELIVERY_PRICE"] = 0;
			$arResult["ORDER_WEIGHT"] = $orderWeight;
			if (is_array($arResult["DELIVERY_ID"]))
			{
				$arOrder = array(
					"PRICE" => $arResult["ORDER_PRICE"],
					"WEIGHT" => $arResult["ORDER_WEIGHT"],
					"LOCATION_FROM" => COption::GetOptionInt('sale', 'location'),
					"LOCATION_TO" => $arResult["DELIVERY_LOCATION"],
					"LOCATION_ZIP" => $arResult["DELIVERY_LOCATION_ZIP"],
				);

				$arDeliveryPrice = CSaleDeliveryHandler::CalculateFull($arResult["DELIVERY_ID"][0], $arResult["DELIVERY_ID"][1], $arOrder, $arResult["BASE_LANG_CURRENCY"]);

				if ($arDeliveryPrice["RESULT"] == "ERROR")
					$arResult["ERROR_MESSAGE"] = $arDeliveryPrice["TEXT"];
				else
					$arResult["DELIVERY_PRICE"] = roundEx($arDeliveryPrice["VALUE"], SALE_VALUE_PRECISION);
			}
			else
			{
				if (($arResult["DELIVERY_ID"] > 0) && !($arDeliv = CSaleDelivery::GetByID($arResult["DELIVERY_ID"])))
					$arResult["ERROR_MESSAGE"] .= GetMessage("SALE_DELIVERY_NOT_FOUND")."<br />";
				elseif (($arResult["DELIVERY_ID"] > 0) && $arDeliv)
					$arResult["DELIVERY_PRICE"] = roundEx(CCurrencyRates::ConvertCurrency($arDeliv["PRICE"], $arDeliv["CURRENCY"], $arResult["BASE_LANG_CURRENCY"]), SALE_VALUE_PRECISION);
			}

			if (strlen($arResult["ERROR_MESSAGE"]) > 0)
				$arResult["CurrentStep"] = 3;
		}

		if (strlen($arResult["ERROR_MESSAGE"]) <= 0 && $arResult["CurrentStep"] >= 5)
		{
			// <***************** AFTER 4 STEP
			// PAY_SYSTEM
			if ( $arResult["CurrentStep"] > 5)
			{
				$arResult["PAY_SYSTEM_ID"] = $arResult["PAY_SYSTEM_ID"]>0?$arResult["PAY_SYSTEM_ID"]:IntVal($_REQUEST["PAY_SYSTEM_ID"]);
				if ($arResult["PAY_SYSTEM_ID"] <= 0)
					$arResult["ERROR_MESSAGE"] .= GetMessage("SALE_NO_PAY_SYS")."<br />";
				if (($arResult["PAY_SYSTEM_ID"] > 0) && !($arPaySys = CSalePaySystem::GetByID($arResult["PAY_SYSTEM_ID"], $arResult["PERSON_TYPE"])))
					$arResult["ERROR_MESSAGE"] .= GetMessage("SALE_PAY_SYS_NOT_FOUND")."<br />";
			}
			if ($arResult["PAY_CURRENT_ACCOUNT"] != "Y")
				$arResult["PAY_CURRENT_ACCOUNT"] = "N";
			if (strlen($arResult["ERROR_MESSAGE"]) > 0)
				$arResult["CurrentStep"] = 5;
		}
		if (strlen($arResult["ERROR_MESSAGE"]) <= 0 && $arResult["CurrentStep"] == 6)
		{

			if (strlen($arResult["ERROR_MESSAGE"]) > 0)
				$arResult["CurrentStep"] = 5;
			//NEW USER
			if ( !$USER->IsAuthorized() ){
				if (strlen($arResult["POST"]["USER_LOGIN"]) <= 0 ){
					$arResult["ERRORS"]['USER_LOGIN'] = GetMessage("STOF_ERROR_REG_EMAIL");
					$arResult["ERROR_MESSAGE"] .= GetMessage("STOF_ERROR_REG_EMAIL").".<br />";
				}elseif (!check_email($arResult["POST"]["USER_LOGIN"]) ){
					$arResult["ERROR_MESSAGE"] .= GetMessage("STOF_ERROR_REG_BAD_EMAIL").".<br />";
					$arResult["ERRORS"]['USER_LOGIN'] = GetMessage("STOF_ERROR_REG_BAD_EMAIL");
				}

				if (strlen($arResult["POST"]["USER_PASSWORD"]) <= 0 ){
					$arResult["ERROR_MESSAGE"] .= GetMessage("STOF_ERROR_REG_FLAG1").".<br />";
					$arResult["ERRORS"]['USER_PASSWORD'] = GetMessage("STOF_ERROR_REG_FLAG1");
				}

				if (strlen($arResult["ERROR_MESSAGE"]) <= 0)
				{
					$arAuthResult = $USER->Register($arResult["POST"]["~USER_LOGIN"], '', '', $arResult["POST"]["~USER_PASSWORD"], $arResult["POST"]["~USER_PASSWORD"], $arResult["POST"]["~USER_LOGIN"], LANG);
					if ($arAuthResult != False && $arAuthResult["TYPE"] == "ERROR" ){
						$arResult["ERROR_MESSAGE"] .= GetMessage("STOF_ERROR_REG").((strlen($arAuthResult["MESSAGE"]) > 0) ? ": ".$arAuthResult["MESSAGE"] : ".<br />" );
					}else {
						if ($USER->IsAuthorized()){
							$USER_ID = $USER->getId();
						} else {
							$arResult["ERROR_MESSAGE"] .= GetMessage("STOF_ERROR_REG_CONFIRM")."<br />";
						}
					}
				}
			}

			//Check available time for order
			$cleanerIDChoose = true;
            $arDates = bhCalendar::getDates($arResult['POST']['ORDER_PROP_DURATION']);
            if ( !isset($arDates[$arResult['POST']['ORDER_PROP_DATE']]) ){
				$cleanerIDChoose = false;
			}

			if ( !$cleanerIDChoose ){
				$arResult["ERROR_MESSAGE"] = 'Выбранное время уже занято.';
				$arResult["ERROR_TIME"] = 'Y';
			}

			if (strlen($arResult["ERROR_MESSAGE"]) <= 0)
			{
				$totalOrderPrice = $arResult["BASKET_PRICE"] + $arResult["DELIVERY_PRICE"] + $arResult["TAX_PRICE"];

				$arFields = array(
					"LID" => SITE_ID,
					"PERSON_TYPE_ID" => $arResult["PERSON_TYPE"],
					"PAYED" => "N",
					"CANCELED" => "N",
					"STATUS_ID" => "N",
					"PRICE" => $totalOrderPrice-$DISCOUNT_PRICE_ALL,
					"CURRENCY" => $arResult["BASE_LANG_CURRENCY"],
					"USER_ID" => IntVal($USER_ID),
					"PAY_SYSTEM_ID" => $arResult["PAY_SYSTEM_ID"],
					"PRICE_DELIVERY" => $arResult["DELIVERY_PRICE"],
					"DELIVERY_ID" => is_array($arResult["DELIVERY_ID"]) ? implode(":", $arResult["DELIVERY_ID"]) : ($arResult["DELIVERY_ID"] > 0 ? $arResult["DELIVERY_ID"] : false),
					"DISCOUNT_VALUE" => $DISCOUNT_PRICE_ALL,
					"TAX_VALUE" => $arResult["bUsingVat"] == "Y" ? $arResult["vatSum"] : $arResult["TAX_PRICE"],
					"USER_DESCRIPTION" => $arResult["ORDER_DESCRIPTION"]
				);

				// add Guest ID
				if (CModule::IncludeModule("statistic"))
					$arFields["STAT_GID"] = CStatistic::GetEventParam();

                if (isset($_SESSION['ORDER_COMMENT']) && $_SESSION['ORDER_COMMENT']) {
                    $arFields['COMMENTS'] = $_SESSION['ORDER_COMMENT'];
                }

				$arResult["ORDER_ID"] = CSaleOrder::Add($arFields);



				//Отправление смс после совершения заказа
				$dateArray= explode("-", $arResult["POST"]["ORDER_PROP_DATE"]);
				$dateCurrent=$dateArray[2].".".$dateArray[1];

				$sms="Спасибо, мы приняли ваш заказ #" . $arResult["ORDER_ID"] . " по адресу: "
                    . $arResult["POST"]["ORDER_PROP_PERSONAL_STREET"] . " на " . $dateCurrent . " " . $arResult["POST"]["ORDER_PROP_TIME"] . ", " . $arResult["BASKET_PRICE"] . " р. "
                    ."Подробнее 88002228330. С чистой душой, Ваш MaxClean.";
				$phone = str_replace(array("+", "-", "(", ")", " "), "", $phone);
				if ($phone[0]=="7")
					$phone[0]="8";
                sendsms($phone, $sms);


				$sms1="Новый заказ #".$arResult["ORDER_ID"].", " .$arResult["BASKET_PRICE"] . "р., ".$dateCurrent." ".$arResult["POST"]["ORDER_PROP_TIME"].", необходимо назначить клинера.";
				if ($arResult["POST"]["ORDER_PROP_PERSONAL_CITY"]==618){
					sendsms(MANAGER_PHONE_MSK, $sms1);
					sendsms(MANAGER_PHONE_MSK_TEST, $sms1);
				}
				elseif ($arResult["POST"]["ORDER_PROP_PERSONAL_CITY"]==617){
					sendsms(MANAGER_PHONE, $sms1);
					sendsms(MANAGER_PHONE_SPB_TEST, $sms1);
				}

				//Костыль на добавление телефона
				//$arFields = array(
				//		"ORDER_ID" => $arResult["ORDER_ID"],
				//		"ORDER_PROPS_ID" => 3,
				//		"NAME" => "Телефон",
				//		"CODE" => "PERSONAL_PHONE",
				//		"VALUE" => $phone
				//);
				//CSaleOrderPropsValue::Add($arFields);

                //if ($USER && $USER->NAME) {
                    //Костыль на добавление телефона
                //    $arFields = array(
                //       "ORDER_ID" => $arResult["ORDER_ID"],
                //       "ORDER_PROPS_ID" => 4,
				//       "NAME" => "Ваше имя",
                //       "CODE" => "NAME",
                //       "VALUE" => $phone
                //    );
                //    CSaleOrderPropsValue::Add($arFields);
                //}

				//Отправка письма пользователю
				$durationArray = explode(".", $arResult["POST"]["ORDER_PROP_DURATION"]);
				if ($durationArray[1])
					$duration2=":30";
				else
					$duration2=":00";
				$duration=$durationArray[0].$duration2;
				$secs = strtotime($duration) - strtotime("00:00:00");
				$base = strtotime($arResult["POST"]["ORDER_PROP_TIME"]);
				$timeFinish=date("H:i", $base + $secs);
				foreach ($arResult["BASKET_ITEMS"] as $arItemBasket){
					if ($arItemBasket["PRODUCT_ID"]==3745 || $arItemBasket["PRODUCT_ID"]==3746 || $arItemBasket["PRODUCT_ID"]==3747 || $arItemBasket["PRODUCT_ID"]==3748 || $arItemBasket["PRODUCT_ID"]==3749 || $arItemBasket["PRODUCT_ID"]==3750)
						$ploshad=$arItemBasket["NAME"];
					else{
						if ($arItemBasket["NAME"]=="окна")
							$uslugiLine.="<p>Помыть ".$arItemBasket["NAME"]." (".intval($arItemBasket["QUANTITY"]).")</p>";
						elseif ($arItemBasket["NAME"]=="пылесос")
							$uslugiLine.="<p>Пылесос</p>";
						else
							$uslugiLine.="<p>Помыть ".$arItemBasket["NAME"]."</p>";
					}
				}

				$arEventFields = array(
						"ORDER_ID"  	=>  $arResult["ORDER_ID"],
						"EMAIL"  		=>  $arResult["USER_LOGIN"],
						"NAME"			=>	$arResult["POST"]["ORDER_PROP_NAME"],
						"DATE"			=>	$dateCurrent,
						"PLOSHAD"		=>	$ploshad,
						"USLUGI_LINE"	=>	$uslugiLine,
						"ADDRESS"		=>	$arResult["POST"]["ORDER_PROP_PERSONAL_STREET"],
						"PHONE"			=>	$phone,
						"PRICE"			=>	$arResult["BASKET_PRICE"],
						"TOTAL_TIME"	=>	$arResult["TOTAL_TIME_FORMATED"],
						"TIME"			=>	$arResult["POST"]["ORDER_PROP_TIME"]." - ".$timeFinish." (~".$arResult["TOTAL_TIME_FORMATED"].")",

				);

				if ($arResult["POST"]["ORDER_PROP_PERSONAL_CITY"]==618)
					$arEventFields["EMAIL_MANAGER"]="v.sazhnev@naibecar.com, juliya8905@gmail.com, ju.kazachenko@naibecar.com, r.blonov@naibecar.com";

				elseif ($arResult["POST"]["ORDER_PROP_PERSONAL_CITY"]==617)
					$arEventFields["EMAIL_MANAGER"]="a.artemov@naibecar.com, juliya.v.k@mail.ru, ju.kazachenko@naibecar.com, r.blonov@naibecar.com";


				CEvent::Send("NEW_ORDER", "s1", $arEventFields, "N", 50);
				CEvent::Send("NEW_ORDER", "s1", $arEventFields, "N", 51);



				unset($_SESSION['ORDER'][$FUSER_ID]);
				unset($_SESSION['ORDER'][CSaleBasket::GetBasketUserID()]);
				$arResult["ORDER_ID"] = IntVal($arResult["ORDER_ID"]);
				bhTools::cancelConfirm();

				if ($arResult["ORDER_ID"] <= 0) {
					if ( $ex = $APPLICATION->GetException())
						$arResult["ERROR_MESSAGE"] .= $ex->GetString();
					else
						$arResult["ERROR_MESSAGE"] .= GetMessage("SALE_ERROR_ADD_ORDER")."<br />";
				} else {
					$arOrder = CSaleOrder::GetByID($arResult["ORDER_ID"]);
					$ar2add = array();

					/*FOR NEW ORDER*/
					$ar2add['PAYTURE_NEW'] = 'Y';
					$ar2add['PAYTURE_LOGIN'] = $arResult['USER_LOGIN'];
					/*END*/

					// GIVE BONUS
					if ( strlen($_SESSION["SALE_COUPON_UTM"]) > 0 || strlen($_SESSION["VALID_COUPON_BH"]) > 0){
						if ( strlen($_SESSION["VALID_COUPON_BH"]) > 0 ) {
							$coupon = $_SESSION["VALID_COUPON_BH"];
							unset($_SESSION["VALID_COUPON_BH"]);
						}
						else {
							$coupon = $_SESSION["SALE_COUPON_UTM"];
						}
						$ar2add['SALE_COUPON_UTM'] = $coupon;

					}

					if ( strlen($_SESSION["SALE_SOURCE_UTM"]) > 0 ) {
						$ar2add['SALE_SOURCE_UTM'] = $_SESSION["SALE_SOURCE_UTM"];
					}
					if (  strlen($coupon) > 0) {

						file_put_contents($_SERVER["DOCUMENT_ROOT"].'/logs/coupons_promos.txt', $arResult['USER_LOGIN'].';'.$coupon."\n", FILE_APPEND);
						unset($_SESSION["VALID_COUPON_BH"]);
						unset($_SESSION["SALE_COUPON_UTM"]);
						unset($_SESSION["SALE_SOURCE_UTM"]);
						unset($_SESSION["SALE_BASKET_MESSAGE"]);
					}

					if ( !empty($ar2add)  ){
						bhOrder::addProps($arResult['PERSON_TYPE'], $arResult["ORDER_ID"], $ar2add);
					}
				}
			}

			if (strlen($arResult["ERROR_MESSAGE"]) <= 0)
			{
				CSaleBasket::OrderBasket($arResult["ORDER_ID"], $FUSER_ID, SITE_ID, false);
			}

			if (strlen($arResult["ERROR_MESSAGE"]) <= 0)
			{
				$arFilter = array("PERSON_TYPE_ID" => $arResult["PERSON_TYPE"], "ACTIVE" => "Y", "UTIL" => "N");
				if ( !empty($arParams["PROP_".$arResult["PERSON_TYPE"]]))
					$arFilter["!ID"] = $arParams["PROP_".$arResult["PERSON_TYPE"]];
				$arPropsCode = array();
				$dbOrderProperties = CSaleOrderProps::GetList(
					array("SORT" => "ASC"),
					$arFilter,
					false,
					false,
					array("ID", "TYPE", "NAME", "CODE", "USER_PROPS", "SORT")
				);

				while ($arOrderProperties = $dbOrderProperties->Fetch())
				{
					$curVal = $arResult["POST"]["~ORDER_PROP_".$arOrderProperties["CODE"]];

					if ($arOrderProperties["TYPE"] == "MULTISELECT")
					{
						$curVal = "";
						$countResProp = count($arResult["POST"]["~ORDER_PROP_".$arOrderProperties["CODE"]]);
						for ($i = 0; $i < $countResProp; $i++)
						{
							if ($i > 0)
								$curVal .= ",";
							$curVal .= $arResult["POST"]["~ORDER_PROP_".$arOrderProperties["CODE"]][$i];
						}
					}

					if ($arOrderProperties["TYPE"] == "CHECKBOX" && strlen($curVal) <= 0 && $arOrderProperties["REQUIED"] != "Y")
					{
						$curVal = "N";
					}

					switch ($arOrderProperties["CODE"]) {
						case 'wish_cleaner':
							if ( isset($_SESSION['WISH_CLEANER']) && $_SESSION['WISH_CLEANER'] > 0 ){
								$curVal = $_SESSION['WISH_CLEANER'];
								$wish_cleaner = $_SESSION['WISH_CLEANER'];
								unset($_SESSION['WISH_CLEANER']);
							};
							break;
						case 'DATE':
							$curVal = bhTools::dateFormat($curVal, 'date');
							break;
					}

					if (strlen($curVal) > 0)
					{
						bhOrder::setProp($arResult["ORDER_ID"], $arOrderProperties["CODE"], $curVal, $arResult['PERSON_TYPE']);

						$arPropsCode[$arOrderProperties["CODE"]] = $curVal;

						if ( $arOrderProperties["CODE"] == "PERSONAL_STREET" ){
							$flatAddress = $curVal;
						}

						/*if ( $arOrderProperties["USER_PROPS"] == "Y" && IntVal($arResult["PROFILE_ID"]) <= 0 && IntVal($arResult["USER_PROPS_ID"])<=0)
						{
							if (strlen($arResult["PROFILE_NAME"]) <= 0)
								$arResult["PROFILE_NAME"] = GetMessage("SALE_PROFILE_NAME")." ".Date("Y-m-d");

							$arFields = array(
								"NAME" => $arResult["PROFILE_NAME"],
								"USER_ID" => IntVal($USER_ID),
								"PERSON_TYPE_ID" => $arResult["PERSON_TYPE"]
							);
							$arResult["USER_PROPS_ID"] = CSaleOrderUserProps::Add($arFields);
							$arResult["USER_PROPS_ID"] = IntVal($arResult["USER_PROPS_ID"]);
						}*/
					}
				}

				bhOrder::setProp($arResult["ORDER_ID"], "HOUR_PRICE", $_SESSION['HOUR_PRICE'], $arResult['PERSON_TYPE']);
				if ( count($arPropsCode) > 0 && $USER_ID != 6  ){
					$user = new CUser;
					$user->Update($USER_ID, $arPropsCode);
				}

				// Add/Update apartment params
				$arBasket = bhBasket::getByOrderId($arResult["ORDER_ID"]);

				if ( count($arBasket[$arResult["ORDER_ID"]]) > 0 ) {
					bhApartment::setFlat($USER_ID, $arBasket[$arResult["ORDER_ID"]], $flatAddress, $wish_cleaner);
					bhCleaner::sendNotice($arResult["ORDER_ID"], $wish_cleaner);
				}

			}

			$withdrawSum = 0.0;
			if (strlen($arResult["ERROR_MESSAGE"]) <= 0)
			{
				if ($arResult["PAY_CURRENT_ACCOUNT"] == "Y" && $arParams["ALLOW_PAY_FROM_ACCOUNT"] == "Y")
				{
					$dbUserAccount = CSaleUserAccount::GetList(
						array(),
						array(
							"USER_ID" => $USER_ID,
							"CURRENCY" => $arResult["BASE_LANG_CURRENCY"],
						)
					);
					if ($arUserAccount = $dbUserAccount->GetNext())
					{
						if ($arUserAccount["CURRENT_BUDGET"] > 0)
						{
							if ( $arOrder["PRICE"]>$arUserAccount["CURRENT_BUDGET"])
								$withdrawSum = $arUserAccount["CURRENT_BUDGET"];
							else
								$withdrawSum = $arOrder["PRICE"];

							if ($withdrawSum > 0)
							{

								//echo  CSaleUserTransact::Add($arFields);
								CSaleUserAccount::UpdateAccount($USER_ID, '-'.$withdrawSum, "RUB", "PAYED by free hours", intVal($arResult["ORDER_ID"]));
								$arFields = array(
									"SUM_PAID" => $withdrawSum,
									"USER_ID" => $USER_ID
								);
								CSaleOrder::Update($arResult["ORDER_ID"], $arFields);
								/*if ($withdrawSum == $totalOrderPrice)
									CSaleOrder::PayOrder($arResult["ORDER_ID"], "Y", False, False);*/
							}

						}
					}
				}
			}
            // mail message
			if (strlen($arResult["ERROR_MESSAGE"]) <= 0)
			{
				$strOrderList = "";
				$totalTime = 0;
				$dbBasketItems = CSaleBasket::GetList(
					array("ID" => "ASC"),
					array("ORDER_ID" => $arResult["ORDER_ID"]),
					false,
					false,
					array("ID", "NAME", "QUANTITY")
				);
				while ($arBasketItems = $dbBasketItems->Fetch())
				{
					//$arBasketItems["PROPS"] = Array();
					$dbProp = CSaleBasket::GetPropsList(Array("SORT" => "ASC", "ID" => "ASC"), Array("BASKET_ID" => $arBasketItems["ID"],
						"CODE" => array("DURATION", "SERVICE")));
					while ($arProp = $dbProp -> GetNext() ){
						//$arBasketItems["PROPS"][] = $arProp;
						if ( $arProp["CODE"] == "DURATION" ){
							$totalTime += intVal($arProp["VALUE"]) * $arBasketItems["QUANTITY"];
						}
					}
				}
				$totalTime = round($totalTime/60, 1);

				$arResult["TOTAL_TIME"] = $totalTime;
				$arResult["TOTAL_TIME_FORMATED"] = $totalTime.' '.bhTools::words($totalTime, $hours);
				$arResult["TIME_PERIOD_FROM"] = $arResult['POST']['ORDER_PROP_TIME'];
				$period = $arResult['POST']['ORDER_PROP_TIME'] + $totalTime;
				$arResult["TIME_PERIOD_HALF"] = false;

				if ( floor($period) == $period ){
					if ( $period>=24)
						$period = $period  - 24;
					$arResult["TIME_PERIOD_TO"] = $period;
				}else{
					if ( floor($period)>=24)
						$period = $period  - 24;
					$arResult["TIME_PERIOD_TO"] = floor($period);
					$arResult["TIME_PERIOD_HALF"] = true;
				}

				$arFields = Array(
					"ORDER_ID" => $arOrder["ACCOUNT_NUMBER"],
					"ORDER_DATE" => Date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT", SITE_ID))),
					"ORDER_USER" => ( (strlen($arResult["PAYER_NAME"]) > 0) ? $arResult["PAYER_NAME"] : $USER->GetFormattedName(false) ),
					"CLEAR_PRICE" => $totalOrderPrice,
					"PRICE" => SaleFormatCurrency(round($totalOrderPrice, -1), $arResult["BASE_LANG_CURRENCY"]),
					"BCC" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
					"EMAIL" => $arResult["USER_LOGIN"],
					//"ORDER_LIST" => $strOrderList,
					"SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
				);

				$eventName = "SALE_NEW_ORDER";

				$bSend = true;
				foreach(GetModuleEvents("sale", "OnOrderNewSendEmail", true) as $arEvent)
					if (ExecuteModuleEventEx($arEvent, Array($arResult["ORDER_ID"], &$eventName, &$arFields))===false)
						$bSend = false;

				if ( $bSend)
				{
					$arFieldsLetter = $arFields;
					//$event = new CEvent;
					//$event->Send($eventName, SITE_ID, $arFields, "N");
				}

				CSaleMobileOrderPush::send("ORDER_CREATED", array("ORDER_ID" => $arFields["ORDER_ID"]));
			}

			if ( strlen($arResult["ERROR_MESSAGE"]) > 0 && $arResult["POST"]["current_b"] == $arResult["PAY_CURRENT_ACCOUNT"] && isset($arResult["POST"]["current_b"]) && $arResult["PAY_CURRENT_ACCOUNT"] == 'Y' ){
				$arResult["CurrentStep"] = 4;
			} elseif ( strlen($arResult["ERROR_MESSAGE"]) > 0)
				$arResult["CurrentStep"] = 5;
		}
	}
}

/*******************************************************************************/
/*****************  BODY  ******************************************************/
/*******************************************************************************/
//if ($USER->IsAuthorized())
if ($USER_ID > 0)
{
	if ($arResult["CurrentStep"] < 3)
	{
		if ( $arResult["SKIP_SECOND_STEP"] != "Y" && IntVal($arResult["PERSON_TYPE"]) > 0)
		{
			$arFilter = array("PERSON_TYPE_ID" => $arResult["PERSON_TYPE"], "ACTIVE" => "Y", "UTIL" => "N");
			if ( !empty($arParams["PROP_".$arResult["PERSON_TYPE"]]))
				$arFilter["!ID"] = $arParams["PROP_".$arResult["PERSON_TYPE"]];
		}

		if ( $USER_ID != 6 && isset($arUser[$arProperties["CODE"]]) ){
			$curVal = $arUser[$arProperties["CODE"]];
		}
		if ( $arResult["SKIP_SECOND_STEP"] == "Y" && $arResult["BACK"] == "Y")
		{
			$arResult["CurrentStep"] = 1;
		}
		elseif ( $arResult["SKIP_SECOND_STEP"] == "Y")
		{
			$arResult["CurrentStep"] = 3;
		}
	}

	if ($arResult["CurrentStep"] == 3)
	{
		if (IntVal($arResult["DELIVERY_LOCATION"]) > 0)
		{
			// if your custom handler needs something else, ex. cart content, you may put it here or get it from your handler using API
			$arFilter = array(
				"COMPABILITY" => array(
					"WEIGHT" => $arResult["ORDER_WEIGHT"],
					"PRICE" => $arResult["ORDER_PRICE"],
					"LOCATION_FROM" => COption::GetOptionString('sale', 'location', false, SITE_ID),
					"LOCATION_TO" => $arResult["DELIVERY_LOCATION"],
					"LOCATION_ZIP" => $arResult["DELIVERY_LOCATION_ZIP"],
				)
			);

			$rsDeliveryServicesList = CSaleDeliveryHandler::GetList(array("SORT" => "ASC"), $arFilter);
			$arDeliveryServicesList = array();
			while ($arDeliveryService = $rsDeliveryServicesList->Fetch())
			{
				$arDeliveryServicesList[] = $arDeliveryService;
			}

			//$numDelivery = count($arDeliveryServicesList);

			$curOneDelivery = false;

			$numDelivery = 0;
			foreach ($arDeliveryServicesList as $key => $arDelivery)
			{
				foreach ($arDelivery['PROFILES'] as $pkey => $arProfile)
				{
					if ($arProfile['ACTIVE'] != 'Y')
					{
						unset($arDeliveryServicesList[$key]['PROFILES'][$pkey]);
					}
				}

				$cnt = count($arDeliveryServicesList[$key]["PROFILES"]);
				if ($cnt <= 0)
					unset($arDeliveryServicesList[$key]);
				else
				{
					$numDelivery += $cnt;
					if ( $cnt == 1 && empty($curOneDelivery))
					{
						foreach ($arDeliveryServicesList[$key]["PROFILES"] as $pkey => $arProfile)
							$curOneDelivery = array($arDeliveryServicesList[$key]['SID'], $pkey);
					}
				}
			}

			$dbDelivery = CSaleDelivery::GetList(
				array(),
				array(
					"LID" => SITE_ID,
					"+<=WEIGHT_FROM" => $arResult["ORDER_WEIGHT"],
					"+>=WEIGHT_TO" => $arResult["ORDER_WEIGHT"],
					"+<=ORDER_PRICE_FROM" => $arResult["ORDER_PRICE"],
					"+>=ORDER_PRICE_TO" => $arResult["ORDER_PRICE"],
					"ACTIVE" => "Y",
					"LOCATION" => $arResult["DELIVERY_LOCATION"],
				)
			);
			while ($arDelivery = $dbDelivery->Fetch())
			{
				$arDeliveryDescription = CSaleDelivery::GetByID($arDelivery["ID"]);
				$arDelivery["DESCRIPTION"] = $arDeliveryDescription["DESCRIPTION"];

				$numDelivery++;
				if ($numDelivery >= 2)
					break;

				if (!is_array($curOneDelivery) || count($curOneDelivery) <= 0 || $curOneDelivery <= 0)
				{
					$curOneDelivery = $arDelivery["ID"];
				}
			}

			if ( $numDelivery < 2 )
			{
				$arResult["SKIP_THIRD_STEP"] = "Y";
				$arResult["CurrentStep"] = 4;
				$arResult["DELIVERY_ID"] = $curOneDelivery;
			}
		}
		else
		{

			$arResult["SKIP_THIRD_STEP"] = "Y";
			$arResult["CurrentStep"] = 4;
		}
	}

	if ($arResult["CurrentStep"] == 4)
	{
		$numPaySys = 0;
		$curOnePaySys = 0;
		$arFilter = array(
			//"LID" => SITE_ID,
			//"CURRENCY" => $arResult["BASE_LANG_CURRENCY"],
			"ACTIVE" => "Y",
			"PERSON_TYPE_ID" => $arResult["PERSON_TYPE"],
			"PSA_HAVE_PAYMENT" => "Y"
		);
		$deliv = $arResult["DELIVERY_ID"];
		if ( is_array($arResult["DELIVERY_ID"]))
			$deliv = $arResult["DELIVERY_ID"][0].":".$arResult["DELIVERY_ID"][1];
		if ( !empty($arParams["DELIVERY2PAY_SYSTEM"]))
		{
			foreach($arParams["DELIVERY2PAY_SYSTEM"] as $val)
			{
				if ( is_array($val[$deliv]))
				{
					foreach($val[$deliv] as $v)
						$arFilter["ID"][] = $v;
				}
				elseif ( IntVal($val[$deliv]) > 0)
					$arFilter["ID"][] = $val[$deliv];
			}
		}

		$dbPaySystem = CSalePaySystem::GetList(
			array("SORT" => "ASC", "PSA_NAME" => "ASC"),
			$arFilter
		);
		while ($arPaySystem = $dbPaySystem->Fetch())
		{
			$numPaySys++;
			if ($numPaySys >= 2)
				break;

			if ($curOnePaySys <= 0)
				$curOnePaySys = $arPaySystem["ID"];
		}


		if ($numPaySys < 2 && $numPaySys > 0)
		{
			if ( $arParams["ALLOW_PAY_FROM_ACCOUNT"] == "Y")
			{
				$dbUserAccount = CSaleUserAccount::GetList(
					array(),
					array(
						"USER_ID" => $USER_ID,
						"CURRENCY" => $arResult["BASE_LANG_CURRENCY"],
					)
				);
				if ($arUserAccount = $dbUserAccount->GetNext())
				{
					if ($arUserAccount["CURRENT_BUDGET"] <= 0)
					{
						$arParams["ALLOW_PAY_FROM_ACCOUNT"] = "N";
					}
					else
					{
						if ( $arParams["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y")
						{
							if ( DoubleVal($arUserAccount["CURRENT_BUDGET"]) >= DoubleVal($arResult["ORDER_PRICE"]))
							{
								$arParams["ALLOW_PAY_FROM_ACCOUNT"] = "Y";
							}
							else
								$arParams["ALLOW_PAY_FROM_ACCOUNT"] = "N";
						}
						else
						{
							$arParams["ALLOW_PAY_FROM_ACCOUNT"] = "Y";
						}
					}

				}
			}


			if ( $arParams["ALLOW_PAY_FROM_ACCOUNT"] == "N")
			{
				$arResult["SKIP_FORTH_STEP"] = "Y";
				$arResult["CurrentStep"] = 4;
				$arResult["PAY_SYSTEM_ID"] = $curOnePaySys;
			}
		}
	}

	if ( $arResult["CurrentStep"] != 2  ){
		$hidden_contacts = true;
		$arFilter = array("PERSON_TYPE_ID" => $arResult["PERSON_TYPE"], "ACTIVE" => "Y", "UTIL" => "N");
		$arFilter = array("PERSON_TYPE_ID" => 1, "ACTIVE" => "Y", "UTIL" => "N");
		$arFilter['!CODE'] = array('DATE', 'TIME', 'DURATION');
		$dbProperties = CSaleOrderProps::GetList(
			array("SORT" => "ASC"),
			$arFilter,
			false,
			false,
			array("ID", "NAME", "CODE", "IS_LOCATION", "TYPE", "SORT")
		);
		while ($arProperties = $dbProperties->GetNext())
		{
			unset($curVal);
			if ( isset($arResult["POST"]["ORDER_PROP_".$arProperties["CODE"]]))
				$curVal = $arResult["POST"]["ORDER_PROP_".$arProperties["CODE"]];

			if ( $arProperties['CODE'] == 'wish_cleaner' && isset($_SESSION['WISH_CLEANER']) ){
				$dbU = CUser::getByID($_SESSION['WISH_CLEANER']);
				if ( $arCleaner = $dbU->fetch() ){
					$curVal = $arCleaner['NAME'];
				}
			}

			if ( $USER_ID != 6 && isset($arUser[$arProperties["CODE"]]) && strlen($curVal)<=0 ){
				$curVal = $arUser[$arProperties["CODE"]];
			}

			if ($arProperties["IS_LOCATION"]=="Y")
				$arResult["DELIVERY_LOCATION"] = IntVal($curVal);

			if ( strlen($curVal) <= 0 && $arProperties['CODE'] != 'wish_cleaner') {
				$hidden_contacts = false;
				continue;
			}
			$arResult["POST"]['ORDER_PROP_'.$arProperties["CODE"]] = $curVal;
			$prop = array('ID'=>$arProperties['ID']);
			$prop['TYPE'] = $arProperties['TYPE'];
			$prop['SORT'] = $arProperties['SORT'];
			$prop['VALUE'] = $curVal;
			$prop['NAME'] = $arProperties['NAME'];
			$prop['CODE'] = $arProperties['CODE'];
			$arResult["hidden_props"][$arProperties["CODE"]] =  $prop;
		}
        if (!isset($arResult['POST'])) {

        }

		if ( !$hidden_contacts) {
			unset($arResult["hidden_props"]);
		} /*elseif ( $arResult['POST']['BACK'] != 'Y' && $_SESSION['PHONE_CONFIRM_CODE_CONFIRMED'] == 'Y' && $arResult["CurrentStep"] < 4 && $arResult["CurrentStep"] > 1 ) {
			$arResult["CurrentStep"] = 4;
		}*/

	}

	//------------------ STEP 1 ----------------------------------------------
	if ($arResult["CurrentStep"] == 1) {
		if (!$arResult['PERSON_TYPE']) {
			$dbPersonType = CSalePersonType::GetList(
				array("SORT" => "ASC", "NAME" => "ASC"),
				array("LID" => SITE_ID, "ACTIVE" => "Y")
			);
			//$bFirst = True;
			if ($arPersonType = $dbPersonType->GetNext()) {
				$arResult["PERSON_TYPE"] = IntVal($arPersonType["ID"]);
			}
		}
		if ( CModule::IncludeModule("statistic"))
		{
			$event1 = "eStore";
			$event2 = "Step4_1";
			$event3 = "";

			if (is_array($arResult['BASKET_ITEMS']))
			{
				foreach($arResult['BASKET_ITEMS'] as $ar_prod)
				{
					$event3 .= $ar_prod["PRODUCT_ID"].", ";
				}
			}
			$e = $event1."/".$event2."/".$event3;

			if ( !is_array($_SESSION["ORDER_EVENTS"]) || (is_array($_SESSION["ORDER_EVENTS"]) && !in_array($e, $_SESSION["ORDER_EVENTS"]))) // check for event in session
			{
				CStatistic::Set_Event($event1, $event2, $event3);
				$_SESSION["ORDER_EVENTS"][] = $e;
			}
		}

	//------------------ STEP 2 ----------------------------------------------
    }
    elseif
      ($arResult["CurrentStep"] == 2) {
		$arResult["USER_PROFILES"] = Array();
		$bFillProfileFields = False;
		$bFirstProfile = True;

		$dbUserProfiles = CSaleOrderUserProps::GetList(
			array("DATE_UPDATE" => "DESC"),
			array(
				"PERSON_TYPE_ID" => $arResult["PERSON_TYPE"],
				"USER_ID" => IntVal($USER_ID)
			)
		);
		if ($arUserProfiles = $dbUserProfiles->GetNext())
		{
			$bFillProfileFields = True;
			do
			{
				if (IntVal($arResult["PROFILE_ID"])==IntVal($arUserProfiles["ID"]) || !isset($arResult["PROFILE_ID"]) && $bFirstProfile)
					$arUserProfiles["CHECKED"] = "Y";
				$bFirstProfile = False;
				$arUserProfiles["USER_PROPS_VALUES"] = Array();
				$dbUserPropsValues = CSaleOrderUserPropsValue::GetList(
					array("SORT" => "ASC"),
					array("USER_PROPS_ID" => $arUserProfiles["ID"]),
					false,
					false,
					array("VALUE", "PROP_TYPE", "VARIANT_NAME", "SORT", "ORDER_PROPS_ID")
				);
				while ($arUserPropsValues = $dbUserPropsValues->GetNext())
				{
					if ( isset($arUser[$arUserPropsValues["CODE"]]) ){
						$arUserPropsValues["VALUE"] = $arUser[$arUserPropsValues["CODE"]];
					}
					$valueTmp = "";
					if ($arUserPropsValues["PROP_TYPE"] == "SELECT"
						|| $arUserPropsValues["PROP_TYPE"] == "MULTISELECT"
						|| $arUserPropsValues["PROP_TYPE"] == "RADIO")
					{
						$arUserPropsValues["VALUE_FORMATED"] = $arUserPropsValues["VARIANT_NAME"];
					}
					elseif ($arUserPropsValues["PROP_TYPE"] == "LOCATION")
					{
						if ($arLocation = CSaleLocation::GetByID($arUserPropsValues["VALUE"], LANGUAGE_ID))
						{
							$arUserPropsValues["VALUE_FORMATED"] = $arLocation["CITY_NAME"];
						}
					}
					else
						$arUserPropsValues["VALUE_FORMATED"] = $arUserPropsValues["VALUE"];
					$arUserProfiles["USER_PROPS_VALUES"][] = $arUserPropsValues;
				}
				$arResult["USER_PROFILES"][] = $arUserProfiles;
			}
			while ($arUserProfiles = $dbUserProfiles->GetNext());

			if (isset($arResult["PROFILE_ID"]) && IntVal($arResult["PROFILE_ID"]) > 0 && $bFirstProfile)
				$arResult["USER_PROFILES_0"] = "Y";

		}

		if ($bFillProfileFields)
		{
			$arResult["USER_PROFILES_TO_FILL"] = "Y";
			if ( isset($arResult["PROFILE_ID"]) && IntVal($arResult["PROFILE_ID"]) > 0 && $bFirstProfile)
				$arResult["USER_PROFILES_TO_FILL_VALUE"] = "Y";
		}

		//for function PrintPropsForm
		$propertyGroupID = 0;
		$propertyUSER_PROPS = "";

		$arFilter = array("PERSON_TYPE_ID" => $arResult["PERSON_TYPE"], "ACTIVE" => "Y", "UTIL" => "N");

		$dbProperties = CSaleOrderProps::GetList(
			array("SORT" => "ASC"),
			$arFilter,
			false,
			false,
			array("ID", "NAME", "TYPE", "REQUIED", "DEFAULT_VALUE", "IS_LOCATION", "PROPS_GROUP_ID", "SIZE1", "SIZE2", "DESCRIPTION", "IS_EMAIL", "IS_PROFILE_NAME", "IS_PAYER", "IS_LOCATION4TAX", "CODE", "GROUP_NAME", "GROUP_SORT", "SORT", "USER_PROPS", "IS_ZIP")
		);
		while ($arProperties = $dbProperties->GetNext())
		{
			unset($curVal);

			if ( isset($arResult["POST"]["ORDER_PROP_".$arProperties["CODE"]]))
				$curVal = $arResult["POST"]["ORDER_PROP_".$arProperties["CODE"]];

			if ( $USER_ID != 6 && isset($arUser[$arProperties["CODE"]]) ){
				if ( $arProperties["CODE"] == 'PERSONAL_CITY' && $arUser[$arProperties["CODE"]] != $_SESSION['CITY_ID'] && $curVal != $_SESSION['CITY_ID'] ){
					$curVal = $_SESSION['CITY_ID'];
					$clear_address = true;
				}
				if ( !$curVal && !$clear_address)
					$curVal = $arUser[$arProperties["CODE"]];
			}elseif ( $arProperties["CODE"] == 'PERSONAL_CITY' && !$curVal ){
				$curVal = $_SESSION['CITY_ID'];
			}

			$arProperties["FIELD_NAME"] = "ORDER_PROP_".$arProperties["CODE"];
			if (IntVal($arProperties["PROPS_GROUP_ID"]) != $propertyGroupID || $propertyUSER_PROPS != $arProperties["USER_PROPS"])
				$arProperties["SHOW_GROUP_NAME"] = "Y";
			$propertyGroupID = $arProperties["PROPS_GROUP_ID"];
			$propertyUSER_PROPS = $arProperties["USER_PROPS"];

			if ($arProperties["REQUIED"]=="Y" || $arProperties["IS_EMAIL"]=="Y" || $arProperties["IS_PROFILE_NAME"]=="Y" || $arProperties["IS_LOCATION"]=="Y" || $arProperties["IS_LOCATION4TAX"]=="Y" || $arProperties["IS_PAYER"]=="Y" || $arProperties["IS_ZIP"]=="Y")
				$arProperties["REQUIED_FORMATED"]="Y";

			if ($arProperties["TYPE"] == "CHECKBOX")
			{
				if ($curVal=="Y" || !isset($curVal) && $arProperties["DEFAULT_VALUE"]=="Y")
					$arProperties["CHECKED"] = "Y";
				$arProperties["SIZE1"] = ((IntVal($arProperties["SIZE1"]) > 0) ? $arProperties["SIZE1"] : 30);
			}
			elseif ($arProperties["TYPE"] == "TEXT")
			{
				if (strlen($curVal) <= 0)
				{
					if ( strlen($arProperties["DEFAULT_VALUE"])>0 && !isset($curVal))
						$arProperties["VALUE"] = $arProperties["DEFAULT_VALUE"];
					elseif ($arProperties["IS_EMAIL"] == "Y")
						$arProperties["VALUE"] = $USER->GetEmail();
					elseif ($arProperties["IS_PAYER"] == "Y")
					{
						//$arProperties["VALUE"] = $USER->GetFullName();
						$rsUser = CUser::GetByID($USER_ID);
						$fio = "";
						if ($arUser = $rsUser->Fetch())
						{
							if (strlen($arUser["LAST_NAME"]) > 0)
								$fio .= $arUser["LAST_NAME"];
							if (strlen($arUser["NAME"]) > 0)
								$fio .= " ".$arUser["NAME"];
							if (strlen($arUser["SECOND_NAME"]) > 0 AND strlen($arUser["NAME"]) > 0)
								$fio .= " ".$arUser["SECOND_NAME"];
						}
						$arProperties["VALUE"] = $fio;
					}
				}
				else
					$arProperties["VALUE"] = $curVal;

			} elseif ($arProperties["TYPE"] == "SELECT") {
				$arProperties["SIZE1"] = ((IntVal($arProperties["SIZE1"]) > 0) ? $arProperties["SIZE1"] : 1);
				$dbVariants = CSaleOrderPropsVariant::GetList(
					array("SORT" => "ASC"),
					array("ORDER_PROPS_ID" => $arProperties["ID"]),
					false,
					false,
					array("*")
				);
				while ($arVariants = $dbVariants->GetNext())
				{
					if ($arVariants["VALUE"] == $curVal || !isset($curVal) && $arVariants["VALUE"] == $arProperties["DEFAULT_VALUE"])
						$arVariants["SELECTED"] = "Y";
					$arProperties["VARIANTS"][] = $arVariants;
				}
			} elseif ($arProperties["TYPE"] == "MULTISELECT") {
				$arProperties["FIELD_NAME"] = "ORDER_PROP_".$arProperties["CODE"].'[]';
				$arProperties["SIZE1"] = ((IntVal($arProperties["SIZE1"]) > 0) ? $arProperties["SIZE1"] : 5);
				$arDefVal = explode(",", $arProperties["DEFAULT_VALUE"]);
				$countDefVal = count($arDefVal);
				for ($i = 0; $i < $countDefVal; $i++)
					$arDefVal[$i] = Trim($arDefVal[$i]);

				$dbVariants = CSaleOrderPropsVariant::GetList(
					array("SORT" => "ASC"),
					array("ORDER_PROPS_ID" => $arProperties["ID"]),
					false,
					false,
					array("*")
				);
				while ($arVariants = $dbVariants->GetNext())
				{
					if ((is_array($curVal) && in_array($arVariants["VALUE"], $curVal)) || (!isset($curVal) && in_array($arVariants["VALUE"], $arDefVal)))
						$arVariants["SELECTED"] = "Y";
					$arProperties["VARIANTS"][] = $arVariants;
				}
			} elseif ($arProperties["TYPE"] == "TEXTAREA") {
				$arProperties["SIZE2"] = ((IntVal($arProperties["SIZE2"]) > 0) ? $arProperties["SIZE2"] : 4);
				$arProperties["SIZE1"] = ((IntVal($arProperties["SIZE1"]) > 0) ? $arProperties["SIZE1"] : 40);
				$arProperties["VALUE"] = ((isset($curVal)) ? $curVal : $arProperties["DEFAULT_VALUE"]);
			} elseif ($arProperties["TYPE"] == "LOCATION") {
				$arProperties["SIZE1"] = ((IntVal($arProperties["SIZE1"]) > 0) ? $arProperties["SIZE1"] : 1);
				$dbVariants = CSaleLocation::GetList(
					array("SORT" => "ASC", "COUNTRY_NAME_LANG" => "ASC", "CITY_NAME_LANG" => "ASC"),
					array("LID" => LANGUAGE_ID),
					false,
					false,
					array("ID", "COUNTRY_NAME", "CITY_NAME", "SORT", "COUNTRY_NAME_LANG", "CITY_NAME_LANG")
				);
				while ($arVariants = $dbVariants->GetNext())
				{
					if (IntVal($arVariants["ID"]) == IntVal($curVal) || !isset($curVal) && IntVal($arVariants["ID"]) == IntVal($arProperties["DEFAULT_VALUE"]))
						$arVariants["SELECTED"] = "Y";
					$arVariants["NAME"] = $arVariants["CITY_NAME"];
					$arProperties["VARIANTS"][] = $arVariants;
				}
			} elseif ($arProperties["TYPE"] == "RADIO") {
				$dbVariants = CSaleOrderPropsVariant::GetList(
					array("SORT" => "ASC"),
					array("ORDER_PROPS_ID" => $arProperties["ID"]),
					false,
					false,
					array("*")
				);
				while ($arVariants = $dbVariants->GetNext())
				{
					if ($arVariants["VALUE"] == $curVal || (strlen($curVal)<=0 && $arVariants["VALUE"] == $arProperties["DEFAULT_VALUE"]))
						$arVariants["CHECKED"]="Y";

					$arProperties["VARIANTS"][] = $arVariants;
				}
			}
			if ( $arProperties["USER_PROPS"]=="Y")
				$arResult["PRINT_PROPS_FORM"]["USER_PROPS_Y"][$arProperties["CODE"]] = $arProperties;
			else
				$arResult["PRINT_PROPS_FORM"]["USER_PROPS_N"][$arProperties["CODE"]] = $arProperties;
		}
		if ( empty($arResult["PRINT_PROPS_FORM"]["USER_PROPS_Y"])) {
			$arResult["USER_PROFILES"] = Array();
			$arResult["USER_PROFILES_TO_FILL_VALUE"] = "N";
			$arResult["USER_PROFILES_TO_FILL"] = "N";

		}

		if ( CModule::IncludeModule("statistic")) {
			$event1 = "eStore";
			$event2 = "Step4_2";
			$event3 = "";

			foreach($arResult['BASKET_ITEMS'] as $ar_prod)
			{
				$event3 .= $ar_prod["PRODUCT_ID"].", ";
			}
			$e = $event1."/".$event2."/".$event3;

			if ( !is_array($_SESSION["ORDER_EVENTS"]) || (is_array($_SESSION["ORDER_EVENTS"]) && !in_array($e, $_SESSION["ORDER_EVENTS"]))) // check for event in session
			{
				CStatistic::Set_Event($event1, $event2, $event3);
				$_SESSION["ORDER_EVENTS"][] = $e;
			}
		}
	}
	//------------------ STEP 3 ----------------------------------------------
	elseif ($arResult["CurrentStep"] == 3)
	{
		$arResult["DELIVERY"] = Array();

		$deliv = $arResult["DELIVERY_ID"];
		if ( is_array($arResult["DELIVERY_ID"]))
			$deliv = $arResult["DELIVERY_ID"][0].":".$arResult["DELIVERY_ID"][1];

		$dbDelivery = CSaleDelivery::GetList(
			array("SORT"=>"ASC", "NAME"=>"ASC"),
			array(
				"LID" => SITE_ID,
				"+<=WEIGHT_FROM" => $arResult["ORDER_WEIGHT"],
				"+>=WEIGHT_TO" => $arResult["ORDER_WEIGHT"],
				"+<=ORDER_PRICE_FROM" => $arResult["ORDER_PRICE"],
				"+>=ORDER_PRICE_TO" => $arResult["ORDER_PRICE"],
				"ACTIVE" => "Y",
				"LOCATION" => $arResult["DELIVERY_LOCATION"]
			)
		);

		$bFirst = True;
		while ($arDelivery = $dbDelivery->GetNext())
		{
			$arDelivery["FIELD_NAME"] = "DELIVERY_ID";
			if (IntVal($arResult["DELIVERY_ID"]) == IntVal($arDelivery["ID"])
				|| IntVal($arResult["DELIVERY_ID"]) <= 0 && $bFirst)
				$arDelivery["CHECKED"] = "Y";
			if (IntVal($arDelivery["PERIOD_FROM"]) > 0 || IntVal($arDelivery["PERIOD_TO"]) > 0)
			{
				$arDelivery["PERIOD_TEXT"] = GetMessage("SALE_DELIV_PERIOD");
				if (IntVal($arDelivery["PERIOD_FROM"]) > 0)
					$arDelivery["PERIOD_TEXT"] .= " ".GetMessage("SALE_FROM")." ".IntVal($arDelivery["PERIOD_FROM"]);
				if (IntVal($arDelivery["PERIOD_TO"]) > 0)
					$arDelivery["PERIOD_TEXT"] .= " ".GetMessage("SALE_TO")." ".IntVal($arDelivery["PERIOD_TO"]);
				if ($arDelivery["PERIOD_TYPE"] == "H")
					$arDelivery["PERIOD_TEXT"] .= " ".GetMessage("SALE_PER_HOUR")." ";
				elseif ($arDelivery["PERIOD_TYPE"]=="M")
					$arDelivery["PERIOD_TEXT"] .= " ".GetMessage("SALE_PER_MONTH")." ";
				else
					$arDelivery["PERIOD_TEXT"] .= " ".GetMessage("SALE_PER_DAY")." ";
			}
			$arDelivery["PRICE_FORMATED"] = SaleFormatCurrency($arDelivery["PRICE"], $arDelivery["CURRENCY"]);
			$arResult["DELIVERY"][] = $arDelivery;
			$bFirst = false;
		}

		if (is_array($arDeliveryServicesList))
		{
			$bFirst = true;
			foreach ($arDeliveryServicesList as $arDeliveryInfo)
			{
				$delivery_id = $arDeliveryInfo["SID"];

				if (!is_array($arDeliveryInfo) || !is_array($arDeliveryInfo["PROFILES"])) continue;

				foreach ($arDeliveryInfo["PROFILES"] as $profile_id => $arDeliveryProfile)
				{
					$arProfile = array(
						"SID" => $profile_id,
						"TITLE" => $arDeliveryProfile["TITLE"],
						"DESCRIPTION" => $arDeliveryProfile["DESCRIPTION"],
						//"CHECKED" => $bFirst ? "Y" : "N",
						"FIELD_NAME" => "DELIVERY_ID",
					);

					if ($arResult['DELIVERY_ID'])
						if ( strpos($deliv, ":") !== false &&
							$deliv == $delivery_id.":".$profile_id
							|| empty($arResult["DELIVERY_ID"]) && $bFirst
						)
							$arProfile["CHECKED"] = "Y";

					if (!is_array($arResult["DELIVERY"][$delivery_id]))
					{
						$arResult["DELIVERY"][$delivery_id] = array(
							"SID" => $delivery_id,
							"TITLE" => $arDeliveryInfo["NAME"],
							"DESCRIPTION" => $arDeliveryInfo["DESCRIPTION"],
							"PROFILES" => array(),
						);
					}

					$arResult["DELIVERY"][$delivery_id]["PROFILES"][$profile_id] = $arProfile;

					$bFirst = false;
				}
			}
		}


		if ( CModule::IncludeModule("statistic"))
		{
			$event1 = "eStore";
			$event2 = "Step4_3";
			$event3 = "";

			foreach($arResult['BASKET_ITEMS'] as $ar_prod)
			{
				$event3 .= $ar_prod["PRODUCT_ID"].", ";
			}
			$e = $event1."/".$event2."/".$event3;

			if ( !is_array($_SESSION["ORDER_EVENTS"]) || (is_array($_SESSION["ORDER_EVENTS"]) && !in_array($e, $_SESSION["ORDER_EVENTS"]))) // check for event in session
			{
				CStatistic::Set_Event($event1, $event2, $event3);
				$_SESSION["ORDER_EVENTS"][] = $e;
			}
		}
	}
	//------------------ STEP 4 ----------------------------------------------
	elseif ($arResult["CurrentStep"] == 5)
	{

		$arResult["PAY_SYSTEM"] = Array();
		$arFilter = array(
			"ACTIVE" => "Y",
			"PERSON_TYPE_ID" => $arResult["PERSON_TYPE"],
			"PSA_HAVE_PAYMENT" => "Y"
		);
		$deliv = $arResult["DELIVERY_ID"];
		if ( is_array($arResult["DELIVERY_ID"]))
			$deliv = $arResult["DELIVERY_ID"][0].":".$arResult["DELIVERY_ID"][1];
		if ( !empty($arParams["DELIVERY2PAY_SYSTEM"]))
		{
			foreach($arParams["DELIVERY2PAY_SYSTEM"] as $val)
			{
				if ( is_array($val[$deliv]))
				{
					foreach($val[$deliv] as $v)
						$arFilter["ID"][] = $v;
				}
				elseif ( IntVal($val[$deliv]) > 0)
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

		$bHaveTaxExempts = False;
		if (is_array($arResult["TaxExempt"]) && count($arResult["TaxExempt"])>0)
		{
			$dbTaxRateList = CSaleTaxRate::GetList(
				array("APPLY_ORDER" => "ASC"),
				array(
					"LID" => SITE_ID,
					"PERSON_TYPE_ID" => $PERSON_TYPE,
					"IS_IN_PRICE" => "N",
					"ACTIVE" => "Y",
					"LOCATION" => IntVal($TAX_LOCATION)
				)
			);
			while ($arTaxRateList = $dbTaxRateList->GetNext())
			{
				if (in_array(IntVal($arTaxRateList["TAX_ID"]), $arResult["TaxExempt"]))
				{
					$arResult["HaveTaxExempts"] = "Y";
					break;
				}
			}
		}

		if ( CModule::IncludeModule("statistic"))
		{
			$event1 = "eStore";
			$event2 = "Step4_5";
			$event3 = "";

			foreach($arResult['BASKET_ITEMS'] as $ar_prod)
			{
				$event3 .= $ar_prod["PRODUCT_ID"].", ";
			}
			$e = $event1."/".$event2."/".$event3;

			if ( !is_array($_SESSION["ORDER_EVENTS"]) || (is_array($_SESSION["ORDER_EVENTS"]) && !in_array($e, $_SESSION["ORDER_EVENTS"]))) // check for event in session
			{
				CStatistic::Set_Event($event1, $event2, $event3);
				$_SESSION["ORDER_EVENTS"][] = $e;
			}
		}
	}
	//------------------ STEP 5 ----------------------------------------------
	elseif ($arResult["CurrentStep"] == 4)
	{
		$arResult["ORDER_PROPS_PRINT"] = Array();
		$propertyGroupID = -1;

		$arFilter = array("PERSON_TYPE_ID" => $arResult["PERSON_TYPE"], "ACTIVE" => "Y", "UTIL" => "N");
		$arFilter['!CODE'] = array('DATE', 'TIME', 'DURATION');

		$dbProperties = CSaleOrderProps::GetList(
			array("SORT" => "ASC"),
			$arFilter,
			false,
			false,
			array("ID", "NAME", "CODE", "TYPE", "PROPS_GROUP_ID", "GROUP_NAME", "GROUP_SORT", "SORT")
		);
        while ($arProperties = $dbProperties->GetNext())
		{
			$curVal = $arResult["POST"]["ORDER_PROP_".$arProperties["CODE"]];

			if ( $arProperties["TYPE"] == "CHECKBOX" ){
				if ($curVal == "Y")
					$arProperties["VALUE_FORMATED"] = GetMessage("SALE_YES");
				else
					$arProperties["VALUE_FORMATED"] = GetMessage("SALE_NO");
			} elseif ( $arProperties["TYPE"] == "TEXT" || $arProperties["TYPE"] == "TEXTAREA" ){
				$arProperties["VALUE_FORMATED"] = $curVal;
			} elseif ( $arProperties["TYPE"] == "SELECT" || $arProperties["TYPE"] == "RADIO" ){
				$arVal = CSaleOrderPropsVariant::GetByValue($arProperties["ID"], $curVal);
				$arProperties["VALUE_FORMATED"] = htmlspecialcharsEx($arVal["NAME"]);
			} elseif ( $arProperties["TYPE"] == "MULTISELECT" ){
				$countCurVal = count($curVal);
				for ($i = 0; $i < $countCurVal; $i++)
				{
					$arVal = CSaleOrderPropsVariant::GetByValue($arProperties["ID"], $curVal[$i]);
					if ($i > 0)
						$arProperties["VALUE_FORMATED"] .= ", ";
					$arProperties["VALUE_FORMATED"] .= htmlspecialcharsEx($arVal["NAME"]);
				}
			} elseif ( $arProperties["TYPE"] == "LOCATION" ){
				$arVal = CSaleLocation::GetByID($curVal, LANGUAGE_ID);
				$arProperties["VALUE_FORMATED"] .= htmlspecialcharsEx($arVal["CITY_NAME"]);
			}
			if( $arProperties["VALUE_FORMATED"] )
				$arResult["ORDER_PROPS_PRINT"][] = $arProperties;
		}

        if ((IntVal($arResult["DELIVERY_ID"]) > 0) && ($arDeliv = CSaleDelivery::GetByID($arResult["DELIVERY_ID"])))
		{
			$arDeliv["NAME"] = htmlspecialcharsEx($arDeliv["NAME"]);
			$arResult["DELIVERY"] = $arDeliv;
			$arResult["DELIVERY_PRICE"] = roundEx(CCurrencyRates::ConvertCurrency($arDeliv["PRICE"], $arDeliv["CURRENCY"], $arResult["BASE_LANG_CURRENCY"]), SALE_VALUE_PRECISION);
		} elseif (IntVal($DELIVERY_ID)>0) {
			$arResult["DELIVERY"] = "ERROR";
		}

		if ((IntVal($arResult["PAY_SYSTEM_ID"]) > 0) && ($arPaySys = CSalePaySystem::GetByID($arResult["PAY_SYSTEM_ID"], $arResult["PERSON_TYPE"])))
		{
			$arResult["PAY_SYSTEM"] = $arPaySys;
			$arResult["PAY_SYSTEM"]["PSA_NAME"] = htmlspecialcharsEx($arResult["PAY_SYSTEM"]["PSA_NAME"]);
			$arResult["PAY_SYSTEM"]["~PSA_NAME"] = $arResult["PAY_SYSTEM"]["PSA_NAME"];
		} elseif (IntVal($arResult["PAY_SYSTEM_ID"]) > 0) {
			$arResult["PAY_SYSTEM"] = "ERROR";
		}

		$arResult["TIME_PERIOD_FROM"] = $arResult['POST']['ORDER_PROP_TIME'];
		$period = $arResult['POST']['ORDER_PROP_TIME'] + $totalTime;
		$arResult["TIME_PERIOD_HALF"] = false;

		if ( floor($period) == $period ){
			if ( $period >= 24)
				$period = $period - 24;
			$arResult["TIME_PERIOD_TO"] = $period;
		}else{
			if ( floor($period) >= 24 )
				$period = $period  - 24;
			$arResult["TIME_PERIOD_TO"] = floor($period);
			$arResult["TIME_PERIOD_HALF"] = true;
		}

		$arResult["ORDER_PRICE_FORMATED"] = SaleFormatCurrency(round($arResult["ORDER_PRICE"], -1), $arResult["BASE_LANG_CURRENCY"]);
		$arResult["DISCOUNT_PRICE_FORMATED"] = SaleFormatCurrency(round($arResult["DISCOUNT_PRICE"], -1), $arResult["BASE_LANG_CURRENCY"]);

		if ($arParams["ALLOW_PAY_FROM_ACCOUNT"] == "Y")
		{
			if ( $USER_ID != 6 ){
				$dbUserAccount = CSaleUserAccount::GetList(
					array(),
					array(
						"USER_ID" => $USER_ID,
						"CURRENCY" => $arResult["BASE_LANG_CURRENCY"]
					)
				);
				if ($arUserAccount = $dbUserAccount->Fetch())
				{
					if ( $arUserAccount["CURRENT_BUDGET"] >= $orderTotalSum)
					{
						$arResult["PAYED_FROM_ACCOUNT"] = "Y";
					}
				}
			}
		}
		if ($arParams["ALLOW_PAY_FROM_ACCOUNT"] == "Y")
		{
			if ( $USER_ID != 6 ){
				$dbUserAccount = CSaleUserAccount::GetList(
					array(),
					array(
						"USER_ID" => $USER_ID,
						"CURRENCY" => $arResult["BASE_LANG_CURRENCY"],
					)
				);
				if ($arUserAccount = $dbUserAccount->GetNext())
				{

					if ($arUserAccount["CURRENT_BUDGET"] > 0)
					{
						if ( $arParams["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y")
						{
							if ( DoubleVal($arUserAccount["CURRENT_BUDGET"]) >= DoubleVal($arResult["ORDER_PRICE"]))
							{
								$arResult["PAY_FROM_ACCOUNT"] = "Y";
							}
							else
								$arResult["PAY_FROM_ACCOUNT"] = "N";
						}
						else
						{
							$arResult["PAY_FROM_ACCOUNT"] = "Y";
						}
					}
				}
			}
			$arResult["USER_ACCOUNT"] = $arUserAccount;
			//$arResult["CURRENT_BUDGET"] = $arUserAccount["CURRENT_BUDGET"];
		}

		if ( IntVal($arResult["DELIVERY_PRICE"])>0)
			$arResult["DELIVERY_PRICE_FORMATED"] = SaleFormatCurrency($arResult["DELIVERY_PRICE"], $arResult["BASE_LANG_CURRENCY"]);
		$orderTotalSum = $arResult["BASKET_PRICE"] + $arResult["DELIVERY_PRICE"] + $arResult["TAX_PRICE"] - $arResult["DISCOUNT_PRICE"] - $DISCOUNT_PRICE_ALL;

		if ( $arResult["PAY_FROM_ACCOUNT"] == "Y" ){
			if ( $arUserAccount["~CURRENT_BUDGET"]>$orderTotalSum ){
				$to_pay_hours = $orderTotalSum;///$_SESSION['HOUR_PRICE'];
			}elseif ( $arUserAccount["~CURRENT_BUDGET"]<=$orderTotalSum ){
				$to_pay_hours = $arUserAccount["CURRENT_BUDGET"];
			}/*else{
                $to_pay_hours = $arResult['TOTAL_TIME'];
            }*/
			$to_pay_hours = SaleFormatCurrency(round($to_pay_hours, -1), $arResult["BASE_LANG_CURRENCY"]);

			$arResult["CURRENT_BUDGET_FORMATED"] = "Использовать (".$to_pay_hours." из ".SaleFormatCurrency(round($arUserAccount["CURRENT_BUDGET"], -1),$arResult["BASE_LANG_CURRENCY"])." <span class='rouble'>Р</span>)";
		}
		if ( $arResult["PAY_CURRENT_ACCOUNT"] == "Y" ){
			$arResult["PAYED_FROM_ACCOUNT_FORMATED"] = SaleFormatCurrency(round((($arUserAccount["~CURRENT_BUDGET"] >= $orderTotalSum) ? $orderTotalSum : $arUserAccount["~CURRENT_BUDGET"]), -1),	$arResult["BASE_LANG_CURRENCY"]);

			$arResult["PAYED_FROM_ACCOUNT_HOURS_FORMATED"] = SaleFormatCurrency(round($arUserAccount["~CURRENT_BUDGET"] >= $orderTotalSum ? $orderTotalSum : $arUserAccount["~CURRENT_BUDGET"], -1),$arResult["BASE_LANG_CURRENCY"]);

			if ( $arUserAccount["~CURRENT_BUDGET"]<=$orderTotalSum ){
				$orderTotalSum = $orderTotalSum - $arUserAccount["~CURRENT_BUDGET"];
			}else{
				$orderTotalSum = 0;
			}
		}

		$arResult["ORDER_TOTAL_PRICE"] = $orderTotalSum;
		$arResult["ORDER_TOTAL_PRICE_FORMATED"] = SaleFormatCurrency(round($orderTotalSum, -1), $arResult["BASE_LANG_CURRENCY"]);

		if ( CModule::IncludeModule("statistic"))
		{
			$event1 = "eStore";
			$event2 = "Step5_4";
			$event3 = "";

			foreach($arProductsInBasket as $ar_prod)
			{
				$event3 .= $ar_prod["PRODUCT_ID"].", ";
			}
			$e = $event1."/".$event2."/".$event3;

			if ( !is_array($_SESSION["ORDER_EVENTS"]) || (is_array($_SESSION["ORDER_EVENTS"]) && !in_array($e, $_SESSION["ORDER_EVENTS"]))) // check for event in session
			{
				CStatistic::Set_Event($event1, $event2, $event3);
				$_SESSION["ORDER_EVENTS"][] = $e;
			}
		}
	}
	//------------------ STEP 6 ----------------------------------------------
	elseif ($arResult["CurrentStep"] == 7)
	{
		$arOrder = false;
		$arResult["ORDER_ID"] = $ID;

		if (!$arOrder)
		{
			$arOrder = CSaleOrder::GetByID($arResult["ORDER_ID"]);
		}

		if ($arOrder)
		{
			if (IntVal($arOrder["PAY_SYSTEM_ID"]) > 0)
			{
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
				if ($arPaySysAction = $dbPaySysAction->Fetch())
				{
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
            if (isset($_SESSION['LAZYLINK'])) unset($_SESSION['LAZYLINK']);
			foreach(GetModuleEvents("sale", "OnSaleComponentOrderComplete", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, Array($arOrder["ID"], $arOrder));

		}else{
			LocalRedirect('/');
		}
	}

	/*if ( empty($arResult['BASKET_ITEMS']) && !empty($arProductsInBasket) ){
		$arResult['BASKET_ITEMS'] = $arProductsInBasket;
	}*/
	//------------------------------------------------------------------------
}

$arResult["DISCOUNT_PRICE_ALL"] = $DISCOUNT_PRICE_ALL;
$arResult["DISCOUNT_PRICE_ALL_FORMATED"] = SaleFormatCurrency(round($DISCOUNT_PRICE_ALL, -1), $allCurrency);

$arResult['BASKET_PRICE_FORMATED'] = SaleFormatCurrency(round($arResult['BASKET_PRICE'], -1), $allCurrency);;

foreach ( $arResult['BASKET_ITEMS'] as &$basket ){
	if ( isset($arBasketProps[$basket['PRODUCT_ID']]) ){
		$basket['PROPERTIES'] = $arBasketProps[$basket['PRODUCT_ID']];
	}
}

$arMain = $arAdit = $arHidden = array();
$arResult["BASKET_ITEMS"] = bhBasket::getBasketFormated($FUSER_ID, $_SESSION['CATALOG_PRICE_TYPE'], false, $arResult['BASKET_ITEMS']);

$arResult['FUSER_ID'] = $FUSER_ID;
$arResult['USER_AUTHORIZED'] = $USER->IsAuthorized();

if ($arResult['ERRORS']) {
    if ($arResult['ERRORS']['NAME']) {
        $arResult['FIRSTKEYERROR'] = 'ORDER_PROP_NAME';
    } else if (isset($arResult['ERRORS']['PERSONAL_PHONE'])) {
        $arResult['FIRSTKEYERROR'] = 'ORDER_PROP_PERSONAL_PHONE';
    } else {
        $arResult['FIRSTKEYERROR'] = key($arResult['ERRORS']);
    }
}
$this->IncludeComponentTemplate();

//send EMAIL
if ( $bSend)
{
	$i = 0;
	foreach($arResult['BASKET_ITEMS']['MAIN'] as $arBasketItem ){
		if ( $arBasketItem['QUANTITY'] > 0 ){
			$mail_line .= $arBasketItem["NAME"].'м&#178;';
		}
	};
	$additional_line = bhTools::makeAddLine($arResult['BASKET_ITEMS']['ADDITIONAL']);
	$date = new DateTime($_POST['ORDER_PROP_DATE']);
	//$date->format("d.m.y");
	$month = bhTools::months(true);
	$date_line = $date->format("d ");
	$date_line .= $month[(int)$date->format("m")];
	$date_line .= $date->format(" Y");

	$prop_line = '<div style="display: block;"><span style="color: #6e7677;">Логин:</span> '.$arResult['USER_LOGIN'].'</div>';
	foreach($arResult["hidden_props"] as $prop ){
		if ( $prop['CODE'] == 'PERSONAL_CITY' ){
			$city = CSaleLocation::GetByID($prop['VALUE'], 'ru');
			$prop['VALUE'] = $city['CITY_NAME'];
		}
		if ( $prop['CODE'] == 'PERSONAL_STREET' || $prop['CODE'] == 'PERSONAL_CITY' || $prop['CODE'] == 'PERSONAL_PHONE' || $prop['CODE'] == 'NAME' ){
			$prop_line .= '<div style="display: block;"><span style="color: #6e7677;">'.$prop['NAME'].':</span> '.$prop['VALUE'].'</div>';
		}

	}
	$pay_line = '';
	if ( $arResult["PAY_SYSTEM_ID"]==1 ){
		$pay_line = ' (оплата наличными)';
	}elseif ( $arResult["PAY_SYSTEM_ID"]==2 ){
		$pay_line = ' (оплата картой)';
	}

	$arResult['URL_TO_CANCEL'] = '/user/history/?ID='.$arResult['ID'].'&CANCEL=Y';
	$res = CSaleUserTransact::GetList(Array("ID" => "DESC"), array("ORDER_ID" => $arOrder["ID"], 'DEBIT'=>'N', 'DESCRIPTION'=>'PAYED by free hours'));
	$SUM_PAID = 0;
	while ($r = $res->Fetch() ){
		if ( $r['ORDER_ID']>0)
			$SUM_PAID += $r['AMOUNT'];
	}
	$sum_paid_line = '';
	if ( $SUM_PAID > 0 ){
		$SUM_PAID = round($SUM_PAID);
		$SUM_PAID_FORMATED = SaleFormatCurrency(round($SUM_PAID, -1), $allCurrency);//($SUM_PAID/$_SESSION['HOUR_PRICE'])." ".words(($SUM_PAID/$_SESSION['HOUR_PRICE']), array('час', 'часа', 'часов'));
		$sum_paid_line = '<div style="display: block;">Использовано бонусов: '.$SUM_PAID_FORMATED.' Р </div>';
	}
	if ( $arOrder['DISCOUNT_VALUE']>0 ){
		$DISCOUNT = round(floor($arOrder['DISCOUNT_VALUE']), -1);
		$discount_line = '<div style="display: block;">Промокод: -'.$DISCOUNT.' Р </div>';
	}
	$Pay2_line = '';
	if ( intVAl($DISCOUNT)>0 || intVal($SUM_PAID)>0 ){
		$Pay2_line = '<div style="display: block;">Итого: '.(round($arFieldsLetter['CLEAR_PRICE']-intVal($DISCOUNT)-intVal($SUM_PAID), -1)).' Р ';

		$Pay2_line .='<span class="grey" style="text-transform: lowercase;"> (';
		if ( $arOrder["PAY_SYSTEM_ID"]==2)
			$Pay2_line .= 'списывается';
		else
			$Pay2_line .= 'оплата';

		$Pay2_line .=' после выполнения заказа)</span>';
		$Pay2_line .='</div>';
	}

	$text = '<tr><td width="100%" style="vertical-align: top; padding: 0 30px 20px 30px;">
              <table style="font-family: Arial, Helvetica, sans-serif; width: 540px; border-collapse:collapse; border:none; table-layout:fixed; background: #eff1f1; font-size: 20px; line-height: 35px;">
                <tbody><tr>
                  <td width="100%" style="vertical-align: top; padding: 20px 40px 30px 40px;">
                    <span style="display: block; font-size: 15px; line-height: 30px; color: #6e7677; margin: 0; padding: 0 0 15px 0;">
                      <img src="http://gettidy.ru/layout/emails/images/icon-date.png" width="24" height="30" alt="" style="vertical-align: middle; display: inline-block; margin: 0 10px 0 0;">Дата и время
                    </span>
                    <div style="display: block;  margin: 0;">'.$date_line.'</div>
                    <div style="display: block;  margin: 0;">с '.$arResult['TIME_PERIOD_FROM'].':00 до '.$arResult['TIME_PERIOD_TO'].':'.($arResult['TIME_PERIOD_HALF']?'3':'0').'0 <span style="color: #6e7677;">(&#126;'.$arResult['TOTAL_TIME_FORMATED'].')</span></div>
                  </td>
                </tr>
              </tbody></table>
            </td>
          </tr>
          <tr>
            <td width="100%" style="vertical-align: top; padding: 0 30px 20px 30px;">
              <table style="font-family: Arial, Helvetica, sans-serif; width: 540px; border-collapse:collapse; border:none; table-layout:fixed; background: #eff1f1; font-size: 20px; line-height: 35px;">
                <tbody><tr>
                  <td width="100%" style="vertical-align: top; padding: 20px 40px 30px 40px;">
                    <span style="display: block; font-size: 15px; line-height: 2; color: #6e7677; padding: 0 0 15px 0;">
                      <img src="http://gettidy.ru/layout/emails/images/icon-params.png" width="24" height="30" alt="" style="vertical-align: middle; display: inline-block; margin: 0 10px 0 0;">Параметры квартиры
                    </span>
                    <div style="display: block;">'.$mail_line.'</div>'.(strlen($additional_line)>0?'<div style="display: block;"><span style="color: #6e7677;">Дополнительно:</span> '.$additional_line.'</div>':'').'

                  </td>
                </tr>
              </tbody></table>
            </td>
          </tr>
          <tr>
            <td width="100%" style="padding: 0 30px 20px 30px;">
              <table style="font-family: Arial, Helvetica, sans-serif; width: 540px; border-collapse:collapse; border:none; table-layout:fixed; background: #eff1f1; font-size: 20px; line-height: 35px;">
                <tbody><tr>
                  <td width="100%" style="padding: 20px 40px 30px 40px;">
                    <span style="display: block; font-size: 15px; line-height: 2; color: #6e7677; padding: 0 0 15px 0;">
                      <img src="http://gettidy.ru/layout/emails/images/icon-contacts.png" width="16" height="30" alt="" style="vertical-align: middle; display: inline-block; margin: 0 10px 0 0;">Контактные данные
                    </span>
                    '.$prop_line.'
                  </td>
                </tr>
              </tbody></table>
            </td>
          </tr>
          <tr>
            <td width="100%" style="padding: 0 30px 20px 30px;">
              <table style="font-family: Arial, Helvetica, sans-serif; width: 540px; border-collapse:collapse; border:none; table-layout:fixed; background: #eff1f1; font-size: 20px; line-height: 35px;">
                <tbody><tr>
                  <td width="100%" style=" padding: 20px 40px 30px 40px;">
                    <span style="display: block; font-size: 15px; line-height: 2; color: #6e7677; padding: 0 0 15px 0;">
                      <img src="http://gettidy.ru/layout/emails/images/icon-pay.png" width="22" height="30" alt="" style="vertical-align: middle; display: inline-block; margin: 0 10px 0 0;">Стоимость
                    </span>
                    <div style="display: block;">'.$arFieldsLetter['PRICE'].' Р за '.$arResult['TOTAL_TIME_FORMATED'].'<span style="color: #6e7677;">'.$pay_line.'</span></div>'.$discount_line.$sum_paid_line.$Pay2_line.'
                  </td>
                </tr>
              </tbody></table>
            </td>
          </tr>
          <tr>
            <td width="100%" style="padding: 5px 30px 35px 30px;">
              <span style="display: block;">Отменить заказ можно в <a href="http://gettidy.ru/user/history/?ID='.$arOrder["ACCOUNT_NUMBER"].'" target="_blank" style="border: none; outline: 0; text-decoration: none; color: #07b19a !important; font-weight: bold;"><span style="color: #07b19a">личном кабинете</span></a></span>
            </td>
          </tr>
 ';
	$arFieldsLetter['TEXT'] = $text;
	$event = new CEvent;
	$event->Send($eventName, SITE_ID, $arFieldsLetter, "N");

	$token = bhSettings::$mandrillKey;
	//$mandrill = new Mandrill($token);

	/*$mandrill->messages->sendTemplate(
		'new-order',
		array(
			array('name'=>'TEXT', 'content'=>$text),
		),
		array(
			//'subject'=>$_SERVER['SERVER_NAME'].': Новый заказ N'.$arOrder['ID'],
			'to'=>array(
				array(
					'email' => $arFieldsLetter['EMAIL'],
					'name' => $arFieldsLetter['ORDER_USER'],
					'type' => 'to'
				)
			),
			'global_merge_vars'=>array(
				array('name'=>'SERVER_NAME', 'content'=>$_SERVER['SERVER_NAME']),
				array('name'=>'ORDER_ID', 'content'=>$arOrder['ID']),
				array('name'=>'DEFAULT_EMAIL_FROM', 'content'=>'hello@'.$_SERVER['SERVER_NAME']),
				array('name'=>'SITE_NAME', 'content'=>$_SERVER['SERVER_NAME'])),
			'merge'=>'Y')
	);
	*/
	$stringSms = 'Спасибо, скоро будет назначен ваш клинер';
	//не отправлять по завершению заказа
	//bhTools::sendSms($phone, $stringSms);


	file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/logs/order_sms_log.txt', $arOrder['ID'].' '.$phone.' '.$stringSms."\n", FILE_APPEND);
	if ( strlen($arResult["ERROR_MESSAGE"]) > 0 ){
		file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/logs/order_errors_log.txt', $arOrder['ID'].': '.$arResult["ERROR_MESSAGE"] . "\n".'sms: '.$phone.' '.$stringSms."\n", FILE_APPEND);
	}

    LocalRedirect($arParams["PATH_TO_ORDER"]."?CurrentStep=7&ORDER_ID=".urlencode(urlencode($arOrder['ID'])));
}
?>