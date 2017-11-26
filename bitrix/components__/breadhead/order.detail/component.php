<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 17.04.15
 * Time: 14:49
 */
if (!CModule::IncludeModule('sale') ){
	return;
};
$arParams['ID'] = intVal($arParams['ID']);
if ( $arParams['ID'] <=0 || !$arParams['ID'])
	localRedirect($APPLICATION->GetCurPage());


$arResult = CSaleOrder::getByID($arParams['ID']);
$newVersion = new DateTime('18.02.2015 21:13');
$orderDate = new DateTime($arResult['DATE_INSERT']);

if ( $orderDate->format('Y-m-d H:j') < $newVersion->format('Y-m-d H:j') ){
	$arResult = workOld($arParams);
	$this->IncludeComponentTemplate('old');
} else {
//order props
	$raw_props = bhOrder::getProps($arResult['ID']);
	$arResult["ORDER_PROPS"] = bhOrder::formatProps($raw_props);
	$usersList = bhTools::formatUser($arResult['USER_ID']);
	$arResult["ORDER_PROPS"]['NAME'] = $usersList[$arResult['USER_ID']]['NAME'];

	$arResult['NEED_CARD'] = false;
	if ( $arResult["PAY_SYSTEM_ID"] == 2 && (!isset($raw_props['CardId']) || strlen($raw_props['CardId']['VALUE']) <= 0) ) {
		$arResult['NEED_CARD'] = true;
		$dbPaySysAction = CSalePaySystemAction::GetList(
			array(),
			array(
				"PAY_SYSTEM_ID" => $arResult["PAY_SYSTEM_ID"],
				"PERSON_TYPE_ID" => $arResult["PERSON_TYPE_ID"]
			),
			false,
			false,
			array("NAME", "ACTION_FILE", "NEW_WINDOW", "PARAMS", "ENCODING")
		);
		if ( $arPaySysAction = $dbPaySysAction->Fetch() ) {
			$arPaySysAction["NAME"] = htmlspecialcharsEx($arPaySysAction["NAME"]);
			if ( strlen($arPaySysAction["ACTION_FILE"]) > 0 ) {

				CSalePaySystemAction::InitParamArrays($arResult, $arResult['ID'], $arPaySysAction["PARAMS"]);
				$pathToAction = $_SERVER["DOCUMENT_ROOT"] . $arPaySysAction["ACTION_FILE"];

				$pathToAction = str_replace("\\", "/", $pathToAction);
				while ( substr($pathToAction, strlen($pathToAction) - 1, 1) == "/" )
					$pathToAction = substr($pathToAction, 0, strlen($pathToAction) - 1);

				if ( file_exists($pathToAction) ) {
					if ( is_dir($pathToAction) && file_exists($pathToAction . "/payment.php") )
						$pathToAction .= "/payment.php";

					$arPaySysAction["PATH_TO_ACTION"] = $pathToAction;
				}
			}
			$arResult["PAY_SYSTEM"] = $arPaySysAction;
		}
	};

//order basket
	$PRICE_TYPE = bhTools::getPriceType();
	$IDs = bhBasket::getItemsByOrderId($arResult['ID']);
	$arBasket = bhBasket::getBasket($arResult["FUSER_ID"], $PRICE_TYPE, false, $IDs, false);
	$arBasket = bhBasket::getBasketFormated($arResult["FUSER_ID"], $PRICE_TYPE, false, $arBasket);
	$arResult["BASKET"] = $arBasket;
	$arResult["SUMMARY"] = bhOrder::getSummary($arResult["ID"], $arBasket, $arResult["PAY_SYSTEM_ID"]);

//date of order
	$today = new DateTime();

	$date = new DateTime($arResult["ORDER_PROPS"]['DATE']['VALUE']);
	$interval = $today->diff($date);
	if ( $today->format('Y-m-d') != $date->format('Y-m-d') ) {
		$days = ceil(($interval->d * 24 + $interval->h) / 24);
	} else {
		$days = 0;
	}

	$arResult["WEEK_DAY"] = bhTools::convertDayNameLong($date->format('l'));
	if ( $interval->invert ) {
		$days = 0 - $days;
	};

	$arResult['DAYS_BEFORE'] = $days;
	if ( $days > 0 ) {
		$arResult["CAN_CHANGE"] = true;
		$arResult["CAN_CHANGE_URL"] = '/user/history/?ID=' . $arResult['ID'] . '&CHANGE=Y';

		if ( $days > 1 && ($arResult['STATUS_ID'] == 'A' || $arResult['STATUS_ID'] == 'N') ) {
			$arResult["CAN_CANCEL"] = true;
			$arResult['URL_TO_CANCEL'] = '/user/history/?ID=' . $arResult['ID'] . '&CANCEL=Y';
		}
	}

	if ( $arResult["ORDER_PROPS"]['Cleaner']['VALUE'] > 0 /*&& $arResult['DAYS_BEFORE'] <= 1 */) {
		$arCleaner = bhTools::formatUser($arResult["ORDER_PROPS"]['Cleaner']['VALUE']);
		$arResult["CLEANER"] = $arCleaner[$arResult["ORDER_PROPS"]['Cleaner']['VALUE']];
	}

	$db = CIBlockElement::GetList(array(), array('IBLOCK_ID' => bhSettings::$IBlock_comments, 'PROPERTY_ORDER' => $arResult['ID'], 'PROPERTY_AUTHOR' => $USER->getId()), false, false, array('PROPERTY_MARK'));
	if ( $review = $db->Fetch() ) {
		$arResult['REVIEW'] = $review;
	}

	$this->IncludeComponentTemplate();
}

function workOld($arParams){
	$arResult = CSaleOrder::getByID($arParams['ID']);
	$arDateTime = array();
	$db = CSaleBasket::getList(array(), array('ORDER_ID' => $arResult['ID']));
	while($item = $db->fetch()){
		$arResult['BASKET'][$item['PRODUCT_ID']] = $item;
	}

	$db = CIBlockElement::getList(array(), array('ID' => array_keys($arResult['BASKET'])));
	while( $element = $db ->getNextElement()){
		$fields = $element->getFields();
		$arResult['BASKET'][$fields['ID']]['NAME'] = $fields['NAME'];
		$arResult['BASKET'][$fields['ID']]['CODE'] = $fields['CODE'];
		$props = $element->getProperties();
		foreach($props as $code=>$vals){
			$arResult['BASKET'][$fields['ID']]['PROPS'][$code] = array(
				'NAME' => $vals['NAME'],
				'CODE' => $vals['CODE'],
				'VALUE' => $vals['VALUE'],
				'ID' => $vals['ID']
			);
		}
	}

	$dbProps = CSaleOrderPropsValue::GetOrderProps($arParams['ID']);
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
	$arVerbs = array();
	foreach($basket as $i=>$prod){
		$arIds[] = $prod['PRODUCT_XML_ID'];
	}
	$dbElems = CIBlockElement::getList(array(), array('IBLOCK_ID'=>bhSettings::$IBlock_catalog, 'ID'=>$arIds), false, false,array('ID', 'CODE', 'PROPERTY_NAME_FORMS', 'PROPERTY_VERB'));
	while($arEl = $dbElems->Fetch()){
		if(strlen($arEl['PROPERTY_NAME_FORMS_VALUE'])>0) {
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
				case 'NAME_FORMS':
					if(strlen($prop['VALUE'])>0)
						$form = $prod['QUANTITY'].' '.$prop['VALUE'];
					else
						$form = $prod["NAME"];
					break;
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
			$form = round($prod['QUANTITY']).' '.bhTools::words($prod['QUANTITY'], $arForms[$prod['PRODUCT_XML_ID']]);
		}
		if(isset($arVerbs[$prod['PRODUCT_XML_ID']])){
			$prod['NAME'] = $arVerbs[$prod['PRODUCT_XML_ID']].' '.$prod['NAME'];
		}
		$prod["NAME_FORMATED"] = $form;

		if($mustbe && $service){
			continue;
		}elseif($mustbe){
			$arResult["BASKET"]['MAIN'][] = $prod;
		}elseif($prod['CODE']!='ORDER' && $prod['CODE']!='DURATION' && $prod['CODE']!='add_30'){
			$arResult["BASKET"]['ADDITIONAL'][] = $prod;
		}

	}

	$today = new DateTime(date('d-m-Y'));
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

	$cleanerID = $arDateTime['Cleaner']['VALUE'];

	if ( $cleanerID ){
		$arCleaner = array();
		$arCleaner["ID"] = $cleanerID;
		$db = CUser::getById($cleanerID);
		if($userCleaner = $db->Fetch()){
			$arCleaner["PHOTO"] = CFile::GetPath($userCleaner["PERSONAL_PHOTO"]);
			$arCleaner["NAME"] = $userCleaner['NAME'];
		};
		$arResult["CLEANER"] = $arCleaner;
	}

//review mark
	$db = CIBlockElement::GetList(array(), array('IBLOCK_ID'=>bhSettings::$IBlock_comments, 'PROPERTY_ORDER'=>$arResult['ID'], 'PROPERTY_AUTHOR'=>$arResult['USER_ID']), false, false, array('PROPERTY_MARK'));
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

	$arResult['NEED_TO_PAY_FORMATED'] = CCurrencyLang::CurrencyFormat($arResult['NEED_TO_PAY'], 'RUB',false);

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

	return $arResult;
}
?>