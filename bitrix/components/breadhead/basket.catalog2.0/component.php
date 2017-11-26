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


if (!function_exists('numberof')) {
    function numberof($numberof, $value, $suffix){
        $numberof = abs($numberof);
        $keys = array(2, 0, 1, 1, 1, 2);
        $mod = $numberof % 100;
        $suffix_key = $mod > 4 && $mod < 20 ? 2 : $keys[min($mod%10, 5)];
        return $value . $suffix[$suffix_key];
    }
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

if ($_REQUEST['ref_inviter'] && !$USER->isAuthorized()){
    $_SESSION['NEW_REF_USER'] = true;
    $_SESSION['REF_INVITER'] = $_REQUEST['ref_inviter'];
    $_SESSION['REF_DISCOUNT'] = DISCOUNT_FOR_NEW_REF_USER;
}
if ($USER->isAuthorized()) {
    $oUser = new CUser;
    $userData = $oUser->GetByID($USER->GetID())->fetch();
    if ($userData['UF_BONUS'] > 0) {
        $_SESSION['REF_DISCOUNT'] = DISCOUNT_FOR_REF_INVITER;
    }

    if ($userData['UF_MNOGORU'] > 0) {
        $_SESSION['MNOGORU'] = $userData['UF_MNOGORU'];
    }

}

unset($couponRequest);
if (isset($_REQUEST['coupon']) && $_REQUEST['coupon'])
    $couponRequest=$_REQUEST['coupon'];
elseif (isset($_REQUEST['coupon_mob']) && $_REQUEST['coupon_mob'])
    $couponRequest=$_REQUEST['coupon_mob'];


unset($mnogoruRequest);
if (isset($_REQUEST['mnogoru']) && $_REQUEST['mnogoru'])
    $mnogoruRequest=$_REQUEST['mnogoru'];
elseif (isset($_REQUEST['mnogoru_mob']) && $_REQUEST['mnogoru_mob'])
    $mnogoruRequest=$_REQUEST['mnogoru_mob'];
elseif ($_SESSION["MNOGORU"])
    $mnogoruRequest=$_SESSION["MNOGORU"];
if ($mnogoruRequest)
    $mnogoruRequest=str_replace(" ", "", $mnogoruRequest);


if (isset($mnogoruRequest)){
    $arResult["MNOGORU"]=$mnogoruRequest;
    if (strlen($mnogoruRequest)!=8){
        $arResult["MNOGORU_VAL"]="<p style='color:red;'>Неверный формат карты</p>";
        $_SESSION["MNOGORU"]="";
    }
    elseif (!ctype_digit($mnogoruRequest)){
        $arResult["MNOGORU_VAL"]="<p style='color:red;'>Неверный формат карты</p>";
        $_SESSION["MNOGORU"]="";
    }
    else{
        $arResult["MNOGORU_VAL"]="<p style='color:green;'>Карта применена</p>";
        $_SESSION["MNOGORU"]=$mnogoruRequest;
    }
}

if ($couponRequest) {
    CCatalogDiscountCoupon::ClearCoupon();
    $arResult['VALIDATION_COUPON_RESULT'] = CCatalogDiscountCoupon::SetCoupon($couponRequest);
    $arResult['COUPON'] = $couponRequest;
    if ($arResult['VALIDATION_COUPON_RESULT']) {
        //$arCoupon = CCatalogDiscountCoupon::GetList(array(), array('COUPON'=>$_REQUEST['coupon'], 'ACTIVE'=>'Y'))->fetch();
        $arCoupon=\Bitrix\Sale\Internals\DiscountCouponTable::getList(array('select' => array('ID', 'COUPON', 'DESCRIPTION'), 'filter' => array('COUPON'=>$_REQUEST['coupon'], 'ACTIVE'=>'Y')))->fetch();
        $_SESSION["SALE_BASKET_MESSAGE"] = $arCoupon['DESCRIPTION'];
        $_SESSION["SALE_COUPON_UTM"] = $arCoupon['COUPON'];
    }
}elseif ($_SESSION["SALE_COUPON_UTM"]){
    //$arCoupon = CCatalogDiscountCoupon::GetList(array(), array('COUPON'=>$_SESSION["SALE_COUPON_UTM"], 'ACTIVE'=>'Y'))->fetch();
    $arCoupon=\Bitrix\Sale\Internals\DiscountCouponTable::getList(array('select' => array('ID', 'COUPON', 'DESCRIPTION'), 'filter' => array('COUPON'=>$_SESSION["SALE_COUPON_UTM"], 'ACTIVE'=>'Y')))->fetch();
    if($arCoupon){
        $arResult['COUPON'] = $_SESSION["SALE_COUPON_UTM"];
        $arResult['VALIDATION_COUPON_RESULT'] = true;
    }else{
        unset($_SESSION["SALE_COUPON_UTM"]);
        unset($_SESSION["SALE_BASKET_MESSAGE"]);
    }
}



$arFlat = bhApartment::getFlat();
$arBasket = bhBasket::getBasket($FUSER_ID, $PRICE_TYPE, $arFlat, false);
$realBasket = bhBasket::getRealBasket($FUSER_ID);

if (!$realBasket){
    $arFields = array();
    $id=3745; //до 45 кв. м.
    $arFields = $arBasket[$id]['PROPERTIES'];
    $arFields['QUANTITY']=1;
    $arFieldsArray[$id]=$arFields;
    bhBasket::add2basket(array($id), $PRICE_TYPE, $arFieldsArray);
    $realBasket = bhBasket::getRealBasket($FUSER_ID);
    $arBasket = bhBasket::getBasket($FUSER_ID, $PRICE_TYPE, $arFlat, $realBasket);
}


//get
$mustbe = false;
foreach ($arBasket as $item){
    if ( !$mustbe && $item['QUANTITY'] > 0 && strlen($item['PROPERTIES']['MUSTBE']['VALUE']) > 0 ){
        $mustbe = $item['ID'];
    }
}

if (($_REQUEST["submit"]) > 0 || strlen($_REQUEST[$action]) > 0 ){
    $_SESSION['BH_SAVE_DATE_TIME'] = false;
    if (strlen($_REQUEST[$action]) > 0){
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
        $toAdd = array_filter($toAdd);
        if ( $empty && $mustbe > 0 ){
            $arFields = $arBasket[$mustbe]['PROPERTIES'];
            $arFields['QUANTITY'] = 1;
            $toAdd[] = $mustbe;
            $arrFields[$mustbe] = $arFields;
        }
        if (!empty($toAdd)){
            bhBasket::add2basket($toAdd, $PRICE_TYPE, $arrFields);
        }

        if (!empty($toUpdate)){
            foreach($toUpdate as $id=>$qnt){
                CSaleBasket::Update($id, array('QUANTITY' => $qnt));
            }
        }
        foreach ($realBasket as $bskId => $fields){
            CSaleBasket::Delete($fields['ID']);
        }
        $realBasket = bhBasket::getRealBasket($FUSER_ID);
        $arBasket = bhBasket::getBasket($FUSER_ID, $PRICE_TYPE, $arFlat, $realBasket);
    }
}




$dbBasketItems = CSaleBasket::GetList(
    array("ID" => "ASC"),
    array('FUSER_ID' => CSaleBasket::GetBasketUserID(), 'LID' => SITE_ID, 'ORDER_ID' => 'NULL'),
    false,
    false,
    array('ID', 'PRODUCT_ID', 'QUANTITY', 'PRICE', 'DISCOUNT_PRICE', 'WEIGHT')
);

$allSum = 0;
$allWeight = 0;
$arItems = array();

while ($arBasketItems = $dbBasketItems->Fetch()){
    $allSum += ($arBasketItems["PRICE"] * $arBasketItems["QUANTITY"]);
    $allWeight += ($arBasketItems["WEIGHT"] * $arBasketItems["QUANTITY"]);
    $arItems[] = $arBasketItems;
}

$arOrder=array(
    'SITE_ID' => SITE_ID,
    'USER_ID' => $USER->GetID(),
    'ORDER_PRICE' => $allSum,
    'ORDER_WEIGHT' => $allWeight,
    'BASKET_ITEMS' => $arItems
);
$arOptions=array('COUNT_DISCOUNT_4_ALL_QUANTITY' => 'Y');
$arErrors=array();

CSaleDiscount::DoProcessOrder($arOrder, $arOptions, $arErrors);

$PRICE_ALL = 0;
$DISCOUNT_PRICE_ALL = 0;
$QUANTITY_ALL = 0;
$ids = array();
$cnt = array();
foreach ($arOrder["BASKET_ITEMS"] as $arOneItem){
    $PRICE_ALL += $arOneItem["PRICE"] * $arOneItem["QUANTITY"];
    $DISCOUNT_PRICE_ALL += $arOneItem["DISCOUNT_PRICE"] * $arOneItem["QUANTITY"];
    $QUANTITY_ALL += $arOneItem['QUANTITY'];
    $ids[] = $arOneItem['PRODUCT_ID'];
    $cnt[$arOneItem['PRODUCT_ID']] = $arOneItem['QUANTITY'];
}

$arResult['DISCOUNT_PRICE'] = round($DISCOUNT_PRICE_ALL);
$arResult['BASKET_PRICE'] = round($PRICE_ALL+$DISCOUNT_PRICE_ALL);
$arResult['TOTAL_PRICE']=round($PRICE_ALL);


$arResult["ITEMS"] = bhBasket::getBasketFormated($FUSER_ID, $PRICE_TYPE, $arFlat, $realBasket, false);
$arResult['WISH_CLEANER'] = false;

$arResult['HIDDEN'] = array();
$arResult['HIDDEN'][] = array('NAME'=>$arParams['ACTION_VARIABLE'], 'VALUE' => $arParams['ACTION_NAME']);
$arResult['HIDDEN'][] = array('NAME'=>'submit', 'VALUE' => 'Y');
$arResult['HIDDEN'][] = array('NAME'=>'FUSER_ID', 'VALUE' => $FUSER_ID);




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
if (isset($_SESSION['REF_DISCOUNT'])) $arResult['DISCOUNT_PRICE'] += $_SESSION['REF_DISCOUNT'];


if ( $time > 16 - bhSettings::$SaveConst ){
    $arResult["ERROR_MESSAGE"] = "На уборку Вашей квартиры требуется более ".(16-bhSettings::$SaveConst)." часов! Напишите,
    пожалуйста, нам через <a href='/obratnaya-svyaz/#clee'>форму обратной связи</a> и мы обязательно свяжемся с Вами для обсуждения
    индивидуальных условий";
}

if ( isset($_SESSION["SALE_BASKET_MESSAGE"]) ){
    $arResult["SALE_BASKET_MESSAGE"] = $_SESSION["SALE_BASKET_MESSAGE"];
}

if (isset($_REQUEST['period'])){
    $_SESSION["period"]=$_REQUEST['period'];

    if ($_REQUEST["period"]=="once_per_week"){
        $arResult['periodDiscountPercent']=20;
        $arResult['periodName']="Раз в неделю";
    }elseif ($_REQUEST["period"]=="twice_per_week"){
        $arResult['periodDiscountPercent']=15;
        $arResult['periodName']="Раз в 2 недели";
    }elseif ($_REQUEST["period"]=="once_per_mounth"){
        $arResult['periodDiscountPercent']=10;
        $arResult['periodName']="Раз в месяц";
    }elseif ($_REQUEST["period"]=="once"){
        $arResult['periodDiscountPercent']=0;
        $arResult['periodName']="Один раз";
    }
}elseif ($_SESSION["period"]){
    if ($_SESSION["period"]=="once_per_week"){
        $arResult['periodDiscountPercent']=20;
        $arResult['periodName']="Раз в неделю";
    }elseif ($_SESSION["period"]=="twice_per_week"){
        $arResult['periodDiscountPercent']=15;
        $arResult['periodName']="Раз в 2 недели";
    }elseif ($_SESSION["period"]=="once_per_mounth"){
        $arResult['periodDiscountPercent']=10;
        $arResult['periodName']="Раз в месяц";
    }elseif ($_SESSION["period"]=="once"){
        $arResult['periodDiscountPercent']=0;
        $arResult['periodName']="Один раз";
    }
}else{
    $arResult['periodDiscountPercent']=0;
    $arResult['periodName']="Один раз";
}

if (!$arResult['DISCOUNT_PRICE']){
    $arResult['periodDiscount']=round(($arResult['BASKET_PRICE']*$arResult['periodDiscountPercent'])/100,0);
    $arResult['TOTAL_PRICE']=$arResult['BASKET_PRICE']-$arResult['periodDiscount'];
}


$_SESSION["periodDiscount"]=$arResult['periodDiscount'];
$_SESSION["periodTotalPrice"]=$arResult['TOTAL_PRICE'];
$_SESSION["periodName"]=$arResult['periodName'];
$_SESSION["DURATION"]=$arResult[$arParams['PROPERTY_DURATION']]['SUMM'];

$arResult['BASKET_PRICE_FORMATED'] = number_format($arResult['BASKET_PRICE'], 0, '.', '&nbsp;');
$arResult['DISCOUNT_PRICE_FORMATED'] = number_format($arResult['DISCOUNT_PRICE'], 0, '.', '&nbsp;');
$arResult['TOTAL_PRICE_FORMATED'] = number_format($arResult['TOTAL_PRICE'], 0, '.', '&nbsp;');


$arResult["MNOGORU_PRICE_FORMATED"]=number_format(round($arResult['TOTAL_PRICE']/7), 0, '.', '&nbsp;');
$arResult["MNOGORU_NAME_FORMATED"]=numberof(round($arResult['TOTAL_PRICE']/7), 'бонус', array('', 'а', 'ов'))." Много.ру";




if ( ($_REQUEST["submit"]) > 0 || strlen($_REQUEST[$action]) > 0 ){
    if ($_REQUEST["AJAX_CALL"] != "Y" && $time <= (16-bhSettings::$SaveConst)){
        LocalRedirect($arParams['BASKET_URL']);
    }
}


$this->IncludeComponentTemplate();

if ($_REQUEST["AJAX_CALL"] == "Y") die();
