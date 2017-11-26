<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$pageId = "user_forum";
include("util_menu.php");
include("util_profile.php");
?>
<?$arInfo = $APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.forum.topic.new",
	"",
	array(
		"FID"	=>	$arParams["FORUM_ID"],
		"MID" => $arResult["VARIABLES"]["message_id"],
		"MESSAGE_TYPE" => $arResult["VARIABLES"]["action"],
		
		"SOCNET_GROUP_ID" => 0, 
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		
		"URL_TEMPLATES_TOPIC_LIST" =>  $arResult["~PATH_TO_USER_FORUM"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["~PATH_TO_USER_FORUM_MESSAGE"],
		"URL_TEMPLATES_PROFILE_VIEW" =>  $arResult["~PATH_TO_USER"],
		
		"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"],
		"PATH_TO_SMILE" => $arParams["PATH_TO_FORUM_SMILE"],
		"PATH_TO_ICON"	=>	$arParams["PATH_TO_FORUM_ICON"],
		"AJAX_TYPE" => $arParams["AJAX_TYPE"],
		
		"SET_TITLE" => $arResult["SET_TITLE"],
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"CACHE_TYPE" => $arResult["CACHE_TYPE"]
	),
	$component,
	array("HIDE_ICONS" => "Y"));
?><?
if (!empty($arInfo) && $arInfo["PERMISSION"] > "I"):
?><?$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.forum.post_form", 
	"", 
	Array(
		"FID"	=>	$arParams["FORUM_ID"],
		"TID"	=>	$arResult["VARIABLES"]["topic_id"],
		"MID"	=>	$arResult["VARIABLES"]["message_id"],
		"PAGE_NAME"	=>	"user_forum_topic_edit",
		"MESSAGE_TYPE"	=>	$_REQUEST["MESSAGE_TYPE"],
		"bVarsFromForm" => $arInfo["bVarsFromForm"],
		
		"SOCNET_GROUP_ID" => 0, 
		"USER_ID" => $arResult["VARIABLES"]["user_id"], 
		
		"URL_TEMPLATES_TOPIC_LIST" =>  $arResult["~PATH_TO_USER_FORUM_TOPIC"],
		"URL_TEMPLATES_MESSAGE" => $arResult["~PATH_TO_USER_FORUM_MESSAGE"],
		
		"MESSAGE" => $arInfo["MESSAGE"],
		"ERROR_MESSAGE" => $arInfo["ERROR_MESSAGE"],
		
		"PATH_TO_SMILE"	=>	$arParams["PATH_TO_FORUM_SMILE"],
		"PATH_TO_ICON"	=>	$arParams["PATH_TO_FORUM_ICON"],
		"SMILE_TABLE_COLS" => $arParams["SMILE_TABLE_COLS"],
		"AJAX_TYPE" => $arParams["AJAX_TYPE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
		"SHOW_TAGS" => $arParams["SHOW_TAGS"]),
	$component,
	array("HIDE_ICONS" => "Y"));
?><?
endif;
?>