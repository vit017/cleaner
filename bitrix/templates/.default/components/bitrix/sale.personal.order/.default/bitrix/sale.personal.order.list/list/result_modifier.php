<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
CModule::IncludeModule("iblock");
$arMarks = array();
$arReviews = array();
$db = CIBlockElement::GetList(array(), array("IBLOCK_ID"=>bhSettings::$IBlock_comments, "PROPERTY_AUTHOR" => $USER->getID()), false, false, array("PROPERTY_MARK", "PROPERTY_ORDER", "PREVIEW_TEXT"));
while($ar = $db->Fetch()){
    if($ar['PROPERTY_MARK_VALUE']>0)
        $arMarks[$ar['PROPERTY_ORDER_VALUE']] = $ar['PROPERTY_MARK_VALUE'];
    if(strlen($ar['PREVIEW_TEXT'])>0)
        $arReviews[$ar['PROPERTY_ORDER_VALUE']] = $ar['PREVIEW_TEXT'];
}
$arResult["ORDER_BY_STATUS"] = Array();
$ids = array();
foreach($arResult["ORDERS"] as $id=>$val) {
    if ( isset($arMarks[$val["ORDER"]["ID"]]) ) {
        $val["MARK"] = intVal($arMarks[$val["ORDER"]["ID"]]);
    }
    if ( isset($arReviews[$val["ORDER"]["ID"]]) ) {
        $val["REVIEW"] = 'Y';
    }
    $ids[$val["ORDER"]["ID"]] = $val["ORDER"];
}

$ordersByDate = bhOrder::sortByDate($ids, 'R');

unset($arResult["ORDERS"]);
$COMPLETE_ORDERS = array();
foreach ($ordersByDate as $order){
    foreach($order as $or){
        $date = new dateTime($or['PROPS']['DATE']['VALUE']);
        $or['DATE'] = $date->format('d.m.Y');
        $or['WEEK_DAY'] = bhTools::convertDayName($date->format('D'));
        if($or["CANCELED"] == 'Y'){
            $or["STATUS_ID"] = 'CANCELED';
        }
        $arResult["ORDER_BY_STATUS"][$or["STATUS_ID"]][] = $or;
        $arResult["ORDERS"][$or['DATE']][] = $or;
        if ($or["STATUS_ID"] == 'F') {
            $COMPLETE_ORDERS[$or['ID']] = $or;
        }
    }
}
$arResult['COMPLETE_ORDERS'] = $COMPLETE_ORDERS;
