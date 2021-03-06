<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "group_requests";
include("util_group_menu.php");
include("util_group_profile.php");
?>
<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.group_requests", 
	"", 
	Array(
		"PATH_TO_USER" 					=> $arParams["PATH_TO_USER"],
		"PATH_TO_GROUP" 				=> $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_REQUESTS" 		=> $arResult["PATH_TO_GROUP_REQUESTS"],
		"PATH_TO_SMILE" 				=> $arResult["PATH_TO_SMILE"],
		"PAGE_VAR" 						=> $arResult["ALIASES"]["page"],
		"GROUP_VAR" 					=> $arResult["ALIASES"]["group_id"],
		"USER_VAR" 						=> $arResult["ALIASES"]["user_id"],
		"SET_NAV_CHAIN" 				=> $arResult["SET_NAV_CHAIN"],
		"SET_TITLE" 					=> $arResult["SET_TITLE"],
		"GROUP_ID" 						=> $arResult["VARIABLES"]["group_id"],
		"PATH_TO_GROUP_REQUEST_SEARCH" 	=> $arResult["PATH_TO_GROUP_REQUEST_SEARCH"],
		"ITEMS_COUNT" 					=> $arParams["ITEM_DETAIL_COUNT"],
		"PATH_TO_MESSAGES_CHAT" 		=> $arParams["PATH_TO_MESSAGES_CHAT"],
		"PATH_TO_VIDEO_CALL" 			=> $arParams["PATH_TO_VIDEO_CALL"],
		"DATE_TIME_FORMAT" 				=> $arResult["DATE_TIME_FORMAT"],
		"SHOW_YEAR" 					=> $arParams["SHOW_YEAR"],
		"NAME_TEMPLATE" 				=> $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" 					=> $arParams["SHOW_LOGIN"],
		"CACHE_TYPE" 					=> $arParams["CACHE_TYPE"],
		"CACHE_TIME" 					=> $arParams["CACHE_TIME"],
		"PATH_TO_CONPANY_DEPARTMENT" 	=> $arParams["PATH_TO_CONPANY_DEPARTMENT"],
		"USE_AUTOSUBSCRIBE" 			=> "N",
	),
	$component 
);
?>