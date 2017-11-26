<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("iblock")){
    ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALL"));
    return;
}

if (!CModule::IncludeModule("catalog")){
    ShowError(GetMessage("CATALOG_MODULE_NOT_INSTALL"));
    return;
}

if (!CModule::IncludeModule("sale")){
    ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
    return;
}

if ($_REQUEST["AJAX_CALL"] == "Y"){
    $APPLICATION->RestartBuffer();
}

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

$action = $arParams["ACTION_VARIABLE"];
$arSort = array('SORT'=>'ASC');

$PRICE_TYPE = bhTools::getPriceType();
$FUSER_ID = $_REQUEST['FUSER_ID'] ? $_REQUEST['FUSER_ID'] : CSaleBasket::GetBasketUserID();

$arParams["SECTION_ID"] = intval($arParams["~SECTION_ID"]);
$timeName = $arParams['PROPERTY_DURATION'];

if ($_REQUEST['utm_Advert'] && !$USER->isAuthorized()){

    if (strlen($_REQUEST['utm_Advert']) > 0){
        $db = CCatalogDiscountCoupon::GetList(array(), array('COUPON'=>$_REQUEST['utm_Advert'], 'ACTIVE'=>'Y'));
        if ($arCoupon = $db->fetch()){
            $arRes["VALID_COUPON"] = CCatalogDiscountCoupon::SetCoupon($arCoupon['COUPON']);
            if (!isset($arRes["VALID_COUPON"]) || (isset($arRes["VALID_COUPON"]) && $arRes["VALID_COUPON"] === false)){
                CCatalogDiscountCoupon::ClearCoupon();
                // unset($_SESSION["SALE_BASKET_MESSAGE"]);
                unset($arRes["VALID_COUPON"]);
            }else{
                $_SESSION["SALE_BASKET_MESSAGE"] = $arCoupon['DESCRIPTION'];
                $_SESSION["SALE_COUPON_UTM"] = $arCoupon['COUPON'];
                if (isset($_REQUEST['utm_user_id'])) {
                    $_SESSION["SALE_SOURCE_UTM"] = $_REQUEST['utm_user_id'];
                }
            }
        }
    }
}
$realBasket = bhBasket::getRealBasket($FUSER_ID);
$arFlat = bhApartment::getFlat();


$arBasket = bhBasket::getBasket($FUSER_ID, $PRICE_TYPE, $arFlat, false);
if ($USER->getID() ==571){
    xmp($realBasket);
    xmp($arBasket);
};
//get
$mustbe = false;
foreach ($arBasket as $item){
    if ( !$mustbe && $item['QUANTITY'] > 0 && strlen($item['PROPERTIES']['MUSTBE']['VALUE']) > 0 ){
        $mustbe = $item['ID'];
    }
}

if (($_REQUEST["submit"]) > 0 || strlen($_REQUEST[$action]) > 0 ){
    $_SESSION['BH_SAVE_DATE_TIME'] = false;

    // if action is performed
    if (strlen($_REQUEST[$action]) > 0) {
        //check the list of ids from request
        //if the item already in basket skip any action with it
        //if it's not in a basket yet add it to basket
        //if the item not in a list but int basket - delete it from there
        $empty = true;
        $toAdd = array();
        $toUpdate = array();
        $arrFields = array();
        if (count($_REQUEST['PRODUCT'])>0){
            foreach ($_REQUEST['PRODUCT'] as $id){
                $arFields = array();
                $basket_id = false;
                $qnt = 1;
                if ( isset($_REQUEST['QUANTITY_'.$id]) && strlen($_REQUEST['QUANTITY_'.$id]) > 0 ){
                    $qnt = IntVal(trim($_REQUEST['QUANTITY_'.$id]));
                }

                if (isset($realBasket[$id])){
                    $basket_id = $realBasket[$id]['ID'];
                }
                $arFields = $arBasket[$id]['PROPERTIES'];
                $arFields['QUANTITY'] = $qnt ;

                if (strlen($arFields['MUSTBE']['VALUE']) > 0){
                    $empty = false;
                }

                if ( !$basket_id ) {
                    $toAdd[] = $id;
                    $arrFields[$id] = $arFields;
                } else {
                    $toUpdate[$basket_id] = $qnt;
                    unset($realBasket[$id]);
                }
            }
        }

        if ( $empty && $mustbe > 0 ){
            $arFields = $arBasket[$mustbe]['PROPERTIES'];
            $arFields['QUANTITY'] = 1;
            $toAdd[] = $mustbe;
            $arrFields[$mustbe] = $arFields;
        }
        if ( !empty($toAdd) ){
            bhBasket::add2basket($toAdd, $PRICE_TYPE, $arrFields);
        }
        if ( !empty($toUpdate) ){
            foreach($toUpdate as $id=>$qnt){
                CSaleBasket::Update($id, array('QUANTITY' => $qnt));
            }
        }

        foreach ($realBasket as $bskId => $fields){
            CSaleBasket::Delete($fields['ID']);
        }

        //get fresh data
        $realBasket = bhBasket::getRealBasket($FUSER_ID);
        $arBasket = bhBasket::getBasket($FUSER_ID, $PRICE_TYPE, $arFlat, $realBasket);

        //save in session chosen wish_cleaner id
        if ( isset($_REQUEST['WISH_CLEANER']) ){
            unset($_SESSION['WISH_CLEANER']);
            $cleaner_id = intval($_REQUEST['WISH_CLEANER']);
            if ( $cleaner_id > 0 ){
                $_SESSION['WISH_CLEANER'] = $cleaner_id;
            }
        }
    }
}

$DISCOUNT_PRICE = 0;
$BASKET_PRICE = 0;

$ids = array();
$cnt = array();
if ( !empty($realBasket) ){
    $arOrder = array(
        'BASKET_ITEMS' => $realBasket,
        "SITE_ID" => 's1',
    );
    //check and set discounts
    if ( strlen($_SESSION["SALE_COUPON_UTM"]) > 0 ) {
        $db = CCatalogDiscountCoupon::GetList(array(),
            array(
                'COUPON' => $_SESSION["SALE_COUPON_UTM"],
                'ACTIVE' => 'Y'
            )
        );
        if ( $arCoupon = $db->fetch() ) {
            $arDiscount = CCatalogDiscount::getById($arCoupon['DISCOUNT_ID']);
            $arOrder["PERSON_TYPE_ID"] = $arDiscount["SORT"];
        }
    }

    $arErrors = array();
    CSaleDiscount::DoProcessOrder($arOrder, array(), $arErrors);

    foreach ( $arOrder['BASKET_ITEMS'] as $item ) {
        if ( $item['DISCOUNT_PRICE'] > 0 ) $DISCOUNT_PRICE += $item['DISCOUNT_PRICE'];
        $BASKET_PRICE += $item['PRICE'] * $item['QUANTITY'];
        $ids[] = $item['PRODUCT_ID'];
        $cnt[$item['PRODUCT_ID']] = $item['QUANTITY'];
    }
} else{
    foreach ($arBasket as $item){
        $BASKET_PRICE += $item['PRICE'] * $item['QUANTITY'];
        if ( $item['QUANTITY'] > 0 ){
            $ids[] = $item['ID'];
            $cnt[$item['ID']] = $item['QUANTITY'];
        }
    }
}
$arResult['DISCOUNT_PRICE'] = floor($DISCOUNT_PRICE);
$arResult['BASKET_PRICE'] = floor($BASKET_PRICE) + $arResult['DISCOUNT_PRICE'];


//get ITEMS
$arResult["ITEMS"] = bhBasket::getBasketFormated($FUSER_ID, $PRICE_TYPE, $arFlat, $realBasket, false);

$arResult['WISH_CLEANER'] = false;

if ( !empty($arFlat['PROPS']['cleaner']['VALUE']) ){
    $wish_cleaner = 0;
    if ( !empty($arFlat['PROPS']['wish_cleaner']['VALUE']) ){
        $wish_cleaner = intVal($arFlat['PROPS']['wish_cleaner']['VALUE']);
    }
    krsort($arFlat['PROPS']['cleaner']['VALUE']);
    $arCleaners = bhTools::formatUser($arFlat['PROPS']['cleaner']['VALUE'], true);
    $arResult['chosen_wish_cleaner'] = 0;
    $arResult['WISH_CLEANER'] = array( array('sort'=>'0', 'id'=>'0', 'name'=>'Не важно', 'img'=>''));
    $j = 0;
    foreach ($arCleaners as $cleaner){
        $j++;
        $fields = array('sort'=>$j,'id'=>$cleaner['ID'], 'name'=>$cleaner['NAME'], 'img'=>$cleaner['PERSONAL_PHOTO']);
        if ( $cleaner['ID'] == $wish_cleaner ){
            $fields['selected'] = 'Y';
            $arResult['chosen_wish_cleaner'] = $wish_cleaner;
        }
        $arResult['WISH_CLEANER'][] = $fields;
    }
}
//
//xmp($arResult['WISH_CLEANER']);
$arResult['HIDDEN'] = array();
$arResult['HIDDEN'][] = array('NAME'=>$arParams['ACTION_VARIABLE'], 'VALUE' => $arParams['ACTION_NAME']);
$arResult['HIDDEN'][] = array('NAME'=>'submit', 'VALUE' => 'Y');
$arResult['HIDDEN'][] = array('NAME'=>'FUSER_ID', 'VALUE' => $FUSER_ID);

if (count($arResult["ITEMS"]['SERVICES'])>0){

}

foreach ($realBasket as $item){
    $arResult['HIDDEN'][] = array('NAME'=>'PRODUCT_IN_BASKET['.$item['PRODUCT_ID'].']', 'VALUE' => $item['ID']);
};

if ( empty($realBasket) ){
    $arResult['EMPTY_BASKET'] = true;

    foreach($arBasket as $item){
        if ( $item['QUANTITY'] > 0 &&  strlen($item['PROPERTIES'][bhSettings::$catalog_mustBe]['VALUE']) <= 0 ){
            $arResult['HIDDEN'][] = array('NAME'=>'PRODUCT[]', 'VALUE' => $item['PRODUCT_ID']);
            if ( strlen($item['PROPERTIES']['SET_QUANTITY']['VALUE']) > 0 ){
                $arResult['HIDDEN'][] = array('NAME'=>'QUANTITY_'.$item['PRODUCT_ID'], 'VALUE' => (int)$item['QUANTITY']);
            }
        }
    }

}

$time = 0;
$mins = bhBasket::getDuration($ids, $cnt);
if ($mins > 0){
    $time = intVal($mins)/60;
    $arResult[$timeName]['COUNT'] = round($time, 1);
    $arResult[$timeName]['COUNT_FORMATED'] = $time.' '.bhTools::words($time, array('час', 'часа', 'часов'));
    $arResult[$timeName]['SUMM'] = round($time, 1);
    $arResult[$timeName]['SUMM_FORMATED'] = $time.' '.bhTools::words($time, array('час', 'часа', 'часов'));
    $arResult['HIDDEN'][] = array('NAME'=>'TOTAL_'.$timeName, 'VALUE' => $time);
    $arResult['HIDDEN'][] = array('NAME'=>'AJAX_CALL', 'VALUE' => '');
}


if ( $arResult['DISCOUNT_PRICE'] > 0 ){
    $arResult['ORDER_PRICE'] = $arResult["BASKET_PRICE"] - $arResult['DISCOUNT_PRICE'];
    $arResult['DISCOUNT_PRICE_FORMATED'] = SaleFormatCurrency(round($arResult['DISCOUNT_PRICE'], -1), 'RUB');
    $arResult["ORDER_PRICE_FORMATED"] = SaleFormatCurrency(round($arResult["ORDER_PRICE"], -1), 'RUB');
}
$arResult["BASKET_PRICE_FORMATED"] = SaleFormatCurrency(round($arResult["BASKET_PRICE"], -1), 'RUB');
//xmp($arResult);
if ( $time > 16 - bhSettings::$SaveConst ){
    $arResult["ERROR_MESSAGE"] = "На уборку Вашей квартиры требуется более ".(16-bhSettings::$SaveConst)." часов! Напишите,
    пожалуйста, нам через <a href='/help/?#form'>форму обратной связи</a> и мы обязательно свяжемся с Вами для обсуждения
    индивидуальных условий";
}

if ( isset($_SESSION["SALE_BASKET_MESSAGE"]) ){
    $arResult["SALE_BASKET_MESSAGE"] = $_SESSION["SALE_BASKET_MESSAGE"];
}


if ( ($_REQUEST["submit"]) > 0 || strlen($_REQUEST[$action]) > 0 ){
    if ($_REQUEST["AJAX_CALL"] != "Y" && $time <= (16-bhSettings::$SaveConst)){
        LocalRedirect($arParams['BASKET_URL']);
    }
}
/*if (isset($_SESSION["SALE_BASKET_MESSAGE"]))
    unset($_SESSION["SALE_BASKET_MESSAGE"]);*/
$this->IncludeComponentTemplate();

if ($_REQUEST["AJAX_CALL"] == "Y") die();
