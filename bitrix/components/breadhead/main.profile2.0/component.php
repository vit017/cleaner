<?
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @param array $arParams
 * @param CBitrixComponent $this
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
if (!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALL"));
	return;
}
if (!CModule::IncludeModule("catalog"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALL"));
	return;
}
global $USER_FIELD_MANAGER;

$arResult["ID"] = intval($USER->GetID());
$arResult["GROUP_POLICY"] = CUser::GetGroupPolicy($arResult["ID"]);

$arParams['SEND_INFO'] = $arParams['SEND_INFO'] == 'Y' ? 'Y' : 'N';
$arParams['CHECK_RIGHTS'] = $arParams['CHECK_RIGHTS'] == 'Y' ? 'Y' : 'N';

if(!($arParams['CHECK_RIGHTS'] == 'N' || $USER->CanDoOperation('edit_own_profile')) || $arResult["ID"]<=0)
{
	$APPLICATION->ShowAuthForm("");
	return;
}

$strError = '';

if( $_SERVER["REQUEST_METHOD"] == "POST" && ($_REQUEST["save"] <> '' || $_REQUEST["RESEND"] <> '') && check_bitrix_sessid() )
{
	if(COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y')
	{
		//possible encrypted user password
		$sec = new CRsaSecurity();
		if(($arKeys = $sec->LoadKeys()))
		{
			$sec->SetKeys($arKeys);
			$errno = $sec->AcceptFromForm(array('NEW_PASSWORD', 'NEW_PASSWORD_CONFIRM'));
			if($errno == CRsaSecurity::ERROR_SESS_CHECK)
				$strError .= GetMessage("main_profile_sess_expired").'<br />';
			elseif($errno < 0)
				$strError .= GetMessage("main_profile_decode_err", array("#ERRCODE#"=>$errno)).'<br />';
		}
	}

	if($strError == '')
	{
		$bOk = false;
		$obUser = new CUser;

		$rsUser = CUser::GetByID($arResult["ID"]);
		$arUser = $rsUser->Fetch();
		if($arUser)
		{
			$oldPhone = $arUser['PERSONAL_PHONE'];
			$oldAddress = $arUser['PERSONAL_STREET'];
		}

		if($_REQUEST["EMAIL"] != $arUser["EMAIL"] || $_REQUEST["EMAIL"] != $arUser["LOGIN"]){
			if(!check_email($_REQUEST["EMAIL"])){
				$arResult["ERROR_MESSAGE"] = 'Неверно введен email';
			}
			$oldUser = CUSER::getByLogin($_REQUEST["EMAIL"]);
			$oldUser = $oldUser->Fetch();
			if(!empty($oldUser) && $oldUser['ID'] != $arResult["ID"]){
				$arResult["ERROR_MESSAGE"] = 'Такой email уже зарегестрирован';
			}
		}
		if ( strlen($arResult["ERROR_MESSAGE"]) <= 0 ){

			if ( $_REQUEST['FLAT'] ){
				$arBasket = array();

				if(count($_REQUEST['FLAT'])>0){
					$arBasket['MAIN'] = array(array('PRODUCT_ID' => $_REQUEST['FLAT_SIZE']));
					$arBasket['ADDITIONAL'] = array();

					foreach($_REQUEST['SERVICES'] as $id=>$qnt){
						$fields = array('PRODUCT_ID' => $id);
						if ( isset($_REQUEST['QUANTITY_'.$id]) ){
							$fields['QUANTITY'] = intVal($_REQUEST['QUANTITY_'.$id]);
						} else{
							$fields['QUANTITY'] = 1;
						}
						$arBasket['ADDITIONAL'][] = $fields;
					}

					if(empty($arBasket['ADDITIONAL'])){
						$arBasket['ADDITIONAL'] = false;
					}
					if ( isset($_REQUEST['WISH_CLEANER']) ){
						$wish_cleaner = intval($_REQUEST['WISH_CLEANER']);
						if ( $wish_cleaner == 0 ){
							$wish_cleaner = false;
						}
					}
				}
				bhApartment::setFlat($arUser['ID'], $arBasket, $oldAddress, $wish_cleaner);
			} elseif( isset($_REQUEST["PERSONAL_STREET"]) && $_REQUEST["PERSONAL_STREET"] !=  $oldAddress ){
				$newAddress = trim($_REQUEST["PERSONAL_STREET"]);
				CIBlockElement::SetPropertyValuesEx($_REQUEST['FLAT_ID'], false, array('address' => $newAddress));
				$el = new CIBlockElement;
				$el->Update($_REQUEST['FLAT_ID'], array('NAME'=>$newAddress));
			}


			$arFields = array(
				"NAME" => $_REQUEST["NAME"],
				"LAST_NAME" => $_REQUEST["LAST_NAME"],
				"SECOND_NAME" => $_REQUEST["SECOND_NAME"],
				"EMAIL" => $_REQUEST["EMAIL"],
				"LOGIN" => $_REQUEST["EMAIL"],
				//"PERSONAL_PHONE" => $_REQUEST["PERSONAL_PHONE"],
				"PERSONAL_CITY" => $_REQUEST["PERSONAL_CITY"],
				"PERSONAL_STREET" => $_REQUEST["PERSONAL_STREET"]!=$oldAddress?$_REQUEST["PERSONAL_STREET"]:$oldAddress,
			);

			// CHECK PHONE
			if ( $_REQUEST['BACK_PHONE'] == 'Y' ){
				bhTools::cancelConfirm();
			} elseif($oldPhone != $_REQUEST["PERSONAL_PHONE"]){

				if($_REQUEST['confirm_code'] == $_SESSION['PHONE_CONFIRM_CODE']){
					unset($_SESSION['PHONE_CONFIRM_CODE_SENT']);
					$_SESSION['PHONE_CONFIRM_CODE_CONFIRMED'] = 'Y';
					$arFields["PERSONAL_PHONE"]  = $_SESSION['PHONE_CONFIRM_NUMBER'];
				}else{
					unset($arFields["PERSONAL_PHONE"]);
				}

				if($_REQUEST["RESEND"] <> ''){

					$smscode=rand(1, 10000);
					$_SESSION['PHONE_CONFIRM_CODE'] = $smscode;
					$curlInit = curl_init('https://intra.becar.ru/f8/spservice/request.php?xml=&dima-phone=89817180789&messagebody='.$smscode);
					curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
					curl_setopt($curlInit,CURLOPT_HEADER,true);
					curl_setopt($curlInit,CURLOPT_NOBODY,true);
					curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);
					//Получаем ответ
					$response = curl_exec($curlInit);
					curl_close($curlInit);

					bhTools::sendConfirmCode($_REQUEST["PERSONAL_PHONE"]);
					$_SESSION['CONFIRM_CODE_RESEND'] = $_SESSION['PHONE_CONFIRM_CODE'];
				}
				elseif(!$_SESSION['PHONE_CONFIRM_CODE_SENT']  && $_SESSION['PHONE_CONFIRM_NUMBER'] != $_REQUEST["PERSONAL_PHONE"]){
					bhTools::sendConfirmCode($_REQUEST["PERSONAL_PHONE"]);
				}elseif(!$_SESSION['PHONE_CONFIRM_CODE_CONFIRMED'] && $_REQUEST['confirm_code']){
					$arResult["ERROR_MESSAGE"] .= 'Неверный код подтверждения';
				}
			}

			if($_REQUEST["NEW_PASSWORD"] <> '' && $arUser['EXTERNAL_AUTH_ID'] == '')
			{
				$arFields["PASSWORD"] = $_REQUEST["NEW_PASSWORD"];
				$arFields["CONFIRM_PASSWORD"] = $_REQUEST["NEW_PASSWORD_CONFIRM"];
			}


			$USER_FIELD_MANAGER->EditFormAddFields("USER", $arFields);

			if(!$obUser->Update($arResult["ID"], $arFields, true)){
				$strError .= $obUser->LAST_ERROR.'<br />';
			}
		}
	}
} elseif ( $_SESSION['PHONE_CONFIRM_CODE_SENT'] ){
	bhTools::cancelConfirm();
}

$rsUser = CUser::GetByID($arResult["ID"]);
if(!$arResult["arUser"] = $rsUser->GetNext(false))
{
	$arResult["ID"] = 0;
}

if($strError <> '' || $arResult['ERROR_MESSAGE'])
{
	static $skip = array("PERSONAL_PHOTO"=>1, "WORK_LOGO"=>1, "forum_AVATAR"=>1, "blog_AVATAR"=>1);
	foreach($_POST as $k => $val)
	{
		if(!isset($skip[$k]))
		{
			if(!is_array($val))
			{
				$val = htmlspecialcharsex($val);
			}

			$arResult["arUser"][$k] = $val;

		}
	}
}

$arResult["FORM_TARGET"] = $APPLICATION->GetCurPage();

$arResult["strProfileError"] = $strError;
$arResult["BX_SESSION_CHECK"] = bitrix_sessid_post();

$arResult["DATE_FORMAT"] = CLang::GetDateFormat("SHORT");

$arResult["COOKIE_PREFIX"] = COption::GetOptionString("main", "cookie_name", "BITRIX_SM");
if (strlen($arResult["COOKIE_PREFIX"]) <= 0)
	$arResult["COOKIE_PREFIX"] = "BX";

// ********************* User properties ***************************************************
$arResult["USER_PROPERTIES"] = array("SHOW" => "N");
if (!empty($arParams["USER_PROPERTY"]))
{
	$arUserFields = $USER_FIELD_MANAGER->GetUserFields("USER", $arResult["ID"], LANGUAGE_ID);
	if (count($arParams["USER_PROPERTY"]) > 0)
	{
		foreach ($arUserFields as $FIELD_NAME => $arUserField)
		{
			if (!in_array($FIELD_NAME, $arParams["USER_PROPERTY"]))
				continue;
			$arUserField["EDIT_FORM_LABEL"] = strLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
			$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
			$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
			$arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
		}
	}
	if ( !empty($arResult["USER_PROPERTIES"]["DATA"]) )
		$arResult["USER_PROPERTIES"]["SHOW"] = "Y";
	$arResult["bVarsFromForm"] = ($strError == ''? false : true);
}
// ******************** /User properties ***************************************************
// get flat to user

$arResult['FLAT'] = bhApartment::getFlatFormated();

$arResult['WISH_CLEANER'] = false;
if ( !empty($arResult['FLAT']['cleaner']['VALUE']) ){
	if ( !empty($arResult['FLAT']['wish_cleaner']['VALUE']) ){
		$wish_cleaner = intVal($arResult['FLAT']['wish_cleaner']['VALUE']);
	}
	krsort($arResult['FLAT']['cleaner']['VALUE']);
	$arCleaners = bhTools::formatUser($arResult['FLAT']['cleaner']['VALUE'], true);

	$arResult['WISH_CLEANER'] = array( array('sort'=>0, 'id'=>'0', 'name'=>'Не важно', 'img'=>''));
	$j=0;
	foreach ($arCleaners as $cleaner){
		$j++;
		$fields = array('sort'=>$j, 'id'=>$cleaner['ID'], 'name'=>$cleaner['NAME'], 'img'=>$cleaner['PERSONAL_PHOTO']);
		if ( $cleaner['ID'] == $wish_cleaner ){
			$fields['seleceted'] = 'Y';
			$arResult['chosen_wish_cleaner'] = $wish_cleaner;
		}
		$arResult['WISH_CLEANER'][] = $fields;
	}
}

//add flat settings to basket
if ( $_REQUEST['FLAT_ID'] > 0 && strlen($_REQUEST['save']) > 0 ){
	CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());
	foreach ($arResult['FLAT']['flat']['VARIANTS'] as $c => $item){
		if ( $item["VALUE"] > 0 ){
			Add2BasketByProductID($item['ID'], round($item["VALUE"]), false, $item["PROPERTIES"]);
		}
	}
	foreach ($arResult['FLAT']['services']['SERVICES'] as $item){
		if ( $item["VALUE"] > 0 ){
			Add2BasketByProductID($item['ID'], round($item["VALUE"]), false, $item["PROPERTIES"]);
		}
	}
}

if($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle(GetMessage("PROFILE_DEFAULT_TITLE"));

if($bOk)
	$arResult['DATA_SAVED'] = 'Y';
unset($_REQUEST["save"]);

if($_SESSION['CONFIRM_CODE_RESEND'] == $_SESSION['PHONE_CONFIRM_CODE']){
	$arResult['CHECK_NUMBER'] = 'N';
}else{
	$arResult['CHECK_NUMBER'] = 'Y';
}

if($_SESSION['PHONE_CONFIRM_CODE_SENT']){
	$tmpl = 'confirm';
}else
	$tmpl = '';
$this->IncludeComponentTemplate($tmpl);
