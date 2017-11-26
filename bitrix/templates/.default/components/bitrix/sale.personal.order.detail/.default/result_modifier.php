<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(isset($arResult['ERRORS']['FATAL'])){
    localRedirect($APPLICATION->GetCurPage());
}
$arDateTime = array();

unset($arResult["ORDER_PROPS"]);
$dbProps = CSaleOrderPropsValue::GetOrderProps($arResult['ID']);
while($prop = $dbProps->fetch()){
    if($prop['CODE'] == 'PERSONAL_CITY'){
        $city = CSaleLocation::GetByID($prop['VALUE'], 'ru');
        $prop['VALUE'] = $city['CITY_NAME'];
    }
    $arDateTime[$prop['CODE']]['ID'] = $prop['ORDER_PROPS_ID'];
    $arDateTime[$prop['CODE']]['NAME'] = $prop['NAME'];
    $arDateTime[$prop['CODE']]['VALUE'] = $prop['VALUE'];
}
//xmp($arDateTime);
$arDateTime['TIME_TO']['VALUE'] = $arDateTime['TIME']['VALUE'] + $arDateTime['DURATION']['VALUE'];

$arDateTime['TIME']['PRINT_VALUE'] = $arDateTime['TIME']['VALUE'] != floor($arDateTime['TIME']['VALUE'])?floor($arDateTime['TIME']['VALUE']).':30':$arDateTime['TIME']['VALUE'].':00';
$arDateTime['TIME_TO']['PRINT_VALUE'] = $arDateTime['TIME_TO']['VALUE'] != floor($arDateTime['TIME_TO']['VALUE'])?floor($arDateTime['TIME_TO']['VALUE']).':30':$arDateTime['TIME_TO']['VALUE'].':00';

$arDateTime['DATE']['PRINT_VALUE'] = $arDateTime['DATE']['VALUE'];
//$arDate = preg_split('[ ]',$arDateTime['DATE']['VALUE']);
$arDate = preg_split('[\.]',trim($arDateTime['DATE']['VALUE']));
$months = bhTools::months(true);
$arDateTime['DATE']['PRINT_VALUE'] = $arDate[0].' '.$months[trim($arDate[1], '0')].' '.$arDate[2];
$arDateTime['DURATION']['PRINT_VALUE'] = $arDateTime['DURATION']['VALUE'].' '.bhTools::words(floor($arDateTime['DURATION']['VALUE']), array('час', 'часа', 'часов'));
$arResult['DATETIME'] = $arDateTime;


foreach($arResult["ORDER_PROPS"] as $i=>$prop){
    if(isset($arDateTime[$prop['CODE']]['PRINT_VALUE']))
        unset($arResult["ORDER_PROPS"][$i]);
}

$basket = $arResult["BASKET"];
unset($arResult["BASKET"]);
$arResult["BASKET"]['MAIN'] = array();
$arResult["BASKET"]['ADDITIONAL'] = array();
$newProps = array();
foreach($arResult['ORDER_PROPS'] as $arProp){
    $newProps[$arProp['CODE']] = $arProp;
}
//$arResult['ORDER_PROPS'] = $newProps;
$arIds = array();
$arCodes = array();
$arVerbs = array();
foreach($basket as $i=>$prod){
    $arIds[] = $prod['PRODUCT_XML_ID'];
}
$dbElems = CIBlockElement::getList(array(), array('IBLOCK_ID'=>bhSettings::$IBlock_catalog, 'ID'=>$arIds), false, false,array('ID', 'CODE', 'PROPERTY_NAME_FORMS', 'PROPERTY_VERB'));
while($arEl = $dbElems->Fetch()){
    if(strlen($arEl['PROPERTY_NAME_FORMS_VALUE'])>0) {
        $arCodes[$arEl['ID']] = $arEl['CODE'];
        if(strlen($arEl['PROPERTY_NAME_FORMS_VALUE'])>0)
            $arForms[$arEl['ID']][] = $arEl['PROPERTY_NAME_FORMS_VALUE'];
    }elseif(strlen($arEl['PROPERTY_VERB_VALUE'])>0){
        $arVerbs[$arEl['ID']] = trim($arEl['PROPERTY_VERB_VALUE']);
    }
}
$discount = 0;
foreach($basket as $i=>$prod){
    if($prod['DISCOUNT_PRICE']>0){
        $discount += $prod['DISCOUNT_PRICE'];
    }
    $mustbe = false;
    $service = false;
    foreach($prod['PROPS'] as $prop){
        switch($prop['CODE']){
            /*case 'NAME_FORMS':
				if(strlen($prop['VALUE'])>0)
					$form = $prod['QUANTITY'].' '.$prop['VALUE'];
				else
					$form = $prod["NAME"];
				break;*/
            case 'MUSTBE':
                if(strlen($prop['VALUE'])>0)
                    $mustbe = true;

                break;
            case 'SERVICE':
                if(strlen($prop['VALUE'])>0)
                    $service = true;
                break;
        }
    };
    if(!isset($arForms[$prod['PRODUCT_XML_ID']])){
        $form = $prod["NAME"];
        if($prod['MEASURE_CODE']==6){
            $form .= 'м2';
        }

    }else{
        $form = $prod['QUANTITY'].' '.bhTools::words($prod['QUANTITY'], $arForms[$prod['PRODUCT_XML_ID']]);
    }
    if(isset($arVerbs[$prod['PRODUCT_XML_ID']])){
        $prod['NAME'] = $arVerbs[$prod['PRODUCT_XML_ID']].' '.$prod['NAME'];
    }
    $prod["NAME_FORMATED"] = $form;

    if(isset($arCodes[$prod['PRODUCT_XML_ID']]))
        $prod['CODE'] = $arCodes[$prod['PRODUCT_XML_ID']];

    if($mustbe && $service){
        continue;
    }elseif($mustbe){
        $arResult["BASKET"]['MAIN'][] = $prod;
    }elseif($prod['CODE']!='ORDER' && $prod['CODE']!='DURATION'){
        $arResult["BASKET"]['ADDITIONAL'][] = $prod;
    }

}

$today = new DateTime(date('d-m-Y'));
//$today = new DateTime('17-07-2014');
$date = new DateTime($arDateTime['DATE']['VALUE']);
$interval = $today->diff($date);

$days = $interval->days;
$arResult["WEEK_DAY"] = bhTools::convertDayNameLong($date->format('l'));
if($interval->invert){
    $days = 0-$days;
};

$arResult['DAYS_BEFORE'] = $days;

$arResult["PRICE_TIME"] = $arDateTime['DURATION']['VALUE'];
$arResult["PRICE_TIME_FORMATED"] = $arDateTime['DURATION']['PRINT_VALUE'];
$cl_prop_id = $arDateTime['Cleaner']['ID'];

//xmp($cl_prop_id);
$db_vals = CSaleOrderPropsValue::GetList(
    array("SORT" => "ASC"),
    array(
        "ORDER_ID" => $arResult["ID"],
        "ORDER_PROPS_ID" => $cl_prop_id
    )
);
$cleanerID  = false;
if ($arVals = $db_vals->Fetch())
    $cleanerID = $arVals["VALUE"];
if($cleanerID){
    $arCleaner = array();
    $arCleaner["ID"] = $cleanerID;
    $arVal = CSaleOrderPropsVariant::GetByValue($cl_prop_id, $cleanerID);
    $arCleaner["NAME"] = $arVal["NAME"];
    $db = CUser::getById($cleanerID);
    if($userCleaner = $db->Fetch()){
        $arCleaner["PHOTO"] = CFile::GetFileArray($userCleaner["PERSONAL_PHOTO"]);
    };
    $arResult["CLEANER"] = $arCleaner;
}
if($arDateTime['DATE']["VALUE"]){
    $today = new DateTime();
    $today = $today->format('d.m.Y');
    if($today > $arDateTime['DATE']["VALUE"]){
        $arResult["CAN_CHANGE"] = true;
        $arResult["CAN_CHANGE_URL"] = '/user/history/?ID='.$arResult['ID'].'&CHANGE=Y';

    }
}
$arResult['URL_TO_CANCEL'] = '/user/history/?ID='.$arResult['ID'].'&CANCEL=Y';

//review mark
$db = CIBlockElement::GetList(array(), array('IBLOCK_ID'=>bhSettings::$IBlock_comments, 'PROPERTY_ORDER'=>$arResult['ID'], 'PROPERTY_AUTHOR'=>$USER->getId()), false, false, array('PROPERTY_MARK'));
if($review = $db->Fetch()){
    $arResult['REVIEW'] = $review;
}
$res = CSaleUserTransact::GetList(Array("ID" => "DESC"), array("ORDER_ID" => $arResult['ID'], 'DEBIT'=>'N', 'DESCRIPTION'=>'PAYED by free hours'));

$SUM_PAID = 0;
while($r = $res->Fetch()){
    $SUM_PAID += $r['AMOUNT'];
}
$arResult['PAID_BY_FREE_HOURS'] = $SUM_PAID;
if(!$arDateTime['HOUR_PRICE']['VALUE'] || $arDateTime['HOUR_PRICE']['VALUE']<=0){
    $hour_price = 700;
}else{
    $hour_price = $arDateTime['HOUR_PRICE']['VALUE'];
}
$arResult["SUM_PAID_TIME"] = $SUM_PAID/intval($hour_price);
$arResult["SUM_PAID_TIME_FORMATED"] = $arResult["SUM_PAID_TIME"].' '.bhTools::words($arResult["SUM_PAID_TIME"], array('час', 'часа', 'часов'));
$arResult['NEED_TO_PAY'] = $arResult['PRICE'] - $SUM_PAID;
if($arResult['SUM_PAID']>=$arResult['PRICE']){
    $arResult['NEED_TO_PAY'] = 0;
}
$arResult['PRICE'] = $arResult['PRICE']+$arResult['DISCOUNT_VALUE'];
$arResult['PRICE_FORMATED'] = CCurrencyLang::CurrencyFormat($arResult['PRICE'], 'RUB', false);
//$arResult['DISCOUNT_VALUE'] =  $arResult['DISCOUNT_VALUE'] + $discount;
$arResult['DISCOUNT_VALUE_FORMATED'] = CCurrencyLang::CurrencyFormat($arResult['DISCOUNT_VALUE'], 'RUB', false);
$db_vals = CSaleOrderPropsValue::GetList(
    array("SORT" => "ASC"),
    array(
        "ORDER_ID" => $arResult['ID'],
        'CODE' => 'CardNumber'
    )
);
if ($arVals = $db_vals->Fetch()){
    $arResult['NEED_CARD'] = $arVals['VALUE'];
}
if(!$arResult['NEED_CARD']){
    $db_vals = CSaleOrderPropsValue::GetList(
        array("SORT" => "ASC"),
        array(
            "ORDER_ID" => $arResult['ID'],
            'CODE' => 'CardId'
        )
    );
    if ($arVals = $db_vals->Fetch()){
        $arResult['NEED_CARD'] = $arVals['VALUE'];
    }
}
?>