<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 26.05.14
 * Time: 10:35
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle('Отзыв на уборку');?>
<?$APPLICATION->IncludeComponent(
	"bitrix:iblock.element.add.form",
	"",
	Array(
		"NAV_ON_PAGE" => "10",
		"USE_CAPTCHA" => "N",
		"USER_MESSAGE_ADD" => "",
		"USER_MESSAGE_EDIT" => "",
		"DEFAULT_INPUT_SIZE" => "30",
		"RESIZE_IMAGES" => "N",
		"IBLOCK_TYPE" => "comments",
		"IBLOCK_ID" => bhSettings::$IBlock_comments,
		"PROPERTY_CODES" => array("15", "16", "17", "18", "19", "NAME", "PREVIEW_TEXT"),
		"PROPERTY_CODES_REQUIRED" => array(),
		"GROUPS" => array("2"),
		"STATUS" => "ANY",
		"STATUS_NEW" => "NEW",
		"ALLOW_EDIT" => "N",
		"ALLOW_DELETE" => "N",
		"ELEMENT_ASSOC" => "CREATED_BY",
		"MAX_USER_ENTRIES" => "100000",
		"MAX_LEVELS" => "100000",
		"LEVEL_LAST" => "N",
		"MAX_FILE_SIZE" => "0",
		"PREVIEW_TEXT_USE_HTML_EDITOR" => "N",
		"DETAIL_TEXT_USE_HTML_EDITOR" => "N",
		"SEF_MODE" => "N",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"CUSTOM_TITLE_NAME" => "",
		"CUSTOM_TITLE_TAGS" => "",
		"CUSTOM_TITLE_DATE_ACTIVE_FROM" => "",
		"CUSTOM_TITLE_DATE_ACTIVE_TO" => "",
		"CUSTOM_TITLE_IBLOCK_SECTION" => "",
		"CUSTOM_TITLE_PREVIEW_TEXT" => "Ваш отзыв",
		"CUSTOM_TITLE_PREVIEW_PICTURE" => "",
		"CUSTOM_TITLE_DETAIL_TEXT" => "",
		"CUSTOM_TITLE_DETAIL_PICTURE" => "",
		"LIST_URL" => "/user/history/comments.php?ORDER=".$_REQUEST['ORDER']
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");