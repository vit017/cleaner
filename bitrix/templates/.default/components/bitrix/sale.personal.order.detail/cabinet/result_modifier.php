<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(isset($arResult['ERRORS']['FATAL'])){
    localRedirect($APPLICATION->GetCurPage());
}
include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/.default/components/bitrix/sale.personal.order.detail/.default/result_modifier.php");
?>