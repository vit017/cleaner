<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!IsModuleInstalled("altasib.geoip"))
{
	ShowError(GetMessage("ALTASIB_GEOIP_MODULE_NOT_INSTALLED"));
	return;
}
/*
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;
if($arParams["CACHE_TYPE"] == "N" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "N"))
	$arParams["CACHE_TIME"] = 0;
*/
$db_vars = CSaleLocation::GetList(
	array(
		"SORT" => "ASC",
		"COUNTRY_NAME_LANG" => "ASC",
		"CITY_NAME_LANG" => "ASC"
	),
	array("LID" => LANGUAGE_ID),
	false,
	false,
	array()
);
while ($vars = $db_vars->Fetch()){
	$arResult['CITIES'][$vars['ID']] = $vars;
};

if(!CModule::IncludeModule("altasib.geoip"))
{
	$obCache->AbortDataCache();
	ShowError(GetMessage("ALTASIB_GEOIP_MODULE_NOT_INSTALLED"));
	return;
}
$set = false;

if ( $_REQUEST['change_city']=='Y' ){
	$arResult['CITY_ID'] = $_SESSION['CITY_ID'] = $_REQUEST['CITY_ID'];
    $set = true;
	$_SESSION['BH_CITY_CHANGED'] = true;
    unset($_REQUEST['change_city']);
} elseif ( $_REQUEST["save"] <> '' && isset($_REQUEST['PERSONAL_CITY'])&& check_bitrix_sessid() ){//change from user settings
    $arResult['CITY_ID'] = $_SESSION['CITY_ID'] = $_REQUEST['PERSONAL_CITY'];
    $set = true;
} elseif ( !isset($_SESSION['CITY_ID']) ){
	if ($USER->isAuthorized()) {
		$rsUser = CUser::GetByID($USER->getID());
		$arUser = $rsUser->Fetch();
		$location_id = $arUser['PERSONAL_CITY'];
		if (isset($arResult['CITIES'][$location_id])) {
			$arResult['CITY_ID'] = $_SESSION['CITY_ID'] = $arResult['CITIES'][$location_id]['ID'];
		}
	}
	if(!isset($arResult['CITY_ID'])){
		$tmpResult = ALX_GeoIP::GetAddr();
		//xmp($arResult);
		if ($tmpResult['district'] == 'Северо-Западный федеральный округ') {
			$arResult['CITY_ID'] = $_SESSION['CITY_ID'] = bhSettings::$city_id_spb;
		} else {
			$arResult['CITY_ID'] = $_SESSION['CITY_ID'] = bhSettings::$city_id_msc;
		}
	}
    $set = true;
} else {
    $arResult['CITY_ID'] = $_SESSION['CITY_ID'];
}

if ( isset($arResult['CITIES'][$arResult['CITY_ID']]) ){
	$arResult['CITIES'][$arResult['CITY_ID']]['SELECTED'] = 'Y';
	$arResult['CURRENT_CITY'] = $arResult['CITIES'][$arResult['CITY_ID']]['CITY_NAME'];
    switch($arResult['CITY_ID']){
        case bhSettings::$city_id_spb:
            $_SESSION['PHONE'] = bhSettings::$phone_spb;
            $_SESSION['ADDRESS'] = bhSettings::$address_spb;
            $_SESSION['CITY'] = $arResult['CURRENT_CITY'];
			$_SESSION['HOUR_PRICE'] = bhSettings::$hour_price_spb;
            break;
        default:
            $_SESSION['PHONE'] = bhSettings::$phone_msc;
            $_SESSION['ADDRESS'] = bhSettings::$address_msc;
            $_SESSION['CITY'] = $arResult['CURRENT_CITY'];
			$_SESSION['HOUR_PRICE'] = bhSettings::$hour_price_msc;
    }
	bhTools::setPriceType(true);
	if( $set ){
		bhTools::updatePrices();
	}

}
//XMP($arResult);
bhTools::setPriceType();
$this->IncludeComponentTemplate();
?>