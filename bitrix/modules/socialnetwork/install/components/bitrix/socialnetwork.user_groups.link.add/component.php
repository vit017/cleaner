<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arResult["~filter_name"] = trim($_REQUEST["filter_name"]);
$arResult["filter_name"] = htmlspecialcharsbx($arResult["~filter_name"]);

if (array_key_exists("filter_my", $_REQUEST) && $_REQUEST["filter_my"] == "Y")
	$arResult["filter_my"] = $_REQUEST["filter_my"];

if (array_key_exists("filter_archive", $_REQUEST) && $_REQUEST["filter_archive"] == "Y")
	$arResult["filter_archive"] = $_REQUEST["filter_archive"];

if (array_key_exists("filter_extranet", $_REQUEST) && $_REQUEST["filter_extranet"] == "Y")
	$arResult["filter_extranet"] = $_REQUEST["filter_extranet"];

$arResult["LIST_NAV_ID"] = (!empty($arParams["LIST_NAV_ID"]) ? $arParams["LIST_NAV_ID"] : "sonet_user_groups");

$this->IncludeComponentTemplate();
?>