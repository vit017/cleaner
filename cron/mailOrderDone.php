<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 29.07.14
 * Time: 14:43
 */

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("LANG", "s1");
foreach ($argv as $arg) {
    $e=explode("=",$arg);
    if (count($e)==2)
        $_GET[$e[0]]=$e[1];
    else
        $_GET[$e[0]]=0;
}
if ($_GET['SERVER_NAME']){
    $_SERVER['SERVER_NAME'] = $_GET['SERVER_NAME'];
}

if ( !isset($_SERVER['DOCUMENT_ROOT']) || $_SERVER['DOCUMENT_ROOT'] == '' ){
    $_SERVER['DOCUMENT_ROOT'] = '/home/u429586/'.$_SERVER['SERVER_NAME'].'/www/';
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if (! function_exists(
    'orderDone')) {
    function orderDone($ID)
    {
        CModule::IncludeModule('sale');

        $arOrder = CSaleOrder::getByID($ID);
        $rsUser = CUser::GetByID($arOrder['USER_ID']);
        $arUser = $rsUser->Fetch();

        $arItems = bhBasket::getItemsByOrderId($ID);

        $basketPrice = 0;
        $dbE = CIBlockElement::GetList(array(),array('IBLOCK_ID' => bhSettings::$IBlock_catalog,
            'ID'=>array_keys($arItems)), false,false,
            array('NAME', 'ID', 'PROPERTY_'.bhSettings::$catalog_mustBe, 'PROPERTY_'.bhSettings::$catalog_verb,
                'PROPERTY_'.bhSettings::$catalog_duration, 'PROPERTY_SET_QUANTITY', 'PROPERTY_WORD'));
        while($arElem = $dbE->fetch()){
            $props = array();
            if ( strlen($arElem['PROPERTY_'.bhSettings::$catalog_mustBe.'_VALUE']) > 0){
                $props[bhSettings::$catalog_mustBe] = $arElem['PROPERTY_'.bhSettings::$catalog_mustBe.'_VALUE'];
            }
            if ( strlen($arElem['PROPERTY_'.bhSettings::$catalog_verb.'_VALUE']) > 0){
                $props[bhSettings::$catalog_verb] = $arElem['PROPERTY_'.bhSettings::$catalog_verb.'_VALUE'];
            }
            if ( strlen($arElem['PROPERTY_'.bhSettings::$catalog_duration.'_VALUE']) > 0){
                $props[bhSettings::$catalog_duration] = $arElem['PROPERTY_'.bhSettings::$catalog_duration.'_VALUE'];
            }
            if ( strlen($arElem['PROPERTY_SET_QUANTITY_VALUE']) > 0){
                $props['SET_QUANTITY'] = $arElem['PROPERTY_SET_QUANTITY_VALUE'];
            }
            if ( strlen($arElem['PROPERTY_WORD_VALUE']) > 0){
                $props['WORD'] = $arElem['PROPERTY_WORD_VALUE'];
            }
            $arItems[$arElem['ID']]['PROPERTIES'] = $props;
            $basketPrice += ($arItems[$arElem['ID']]['PRICE']+$arItems[$arElem['ID']]['DISCOUNT_PRICE'])*$arItems[$arElem['ID']]['QUANTITY'];
        }

        $basketF = bhBasket::getBasketFormated($arOrder['FUSER_ID'], 1, false, $arItems, true);

        $order_line = '';
        foreach($basketF['MAIN'] as $arBasketItem ){
            if ( $arBasketItem['QUANTITY'] > 0 ){
                $order_line .= $arBasketItem["NAME"].'м&#178;';
            }
        };

        $additional_line = bhTools::makeAddLine($basketF['ADDITIONAL']);
        $arProps = bhOrder::getProps($ID);

        $order_line .= strlen($additional_line) > 0 ? ', дополнительно: ' . $additional_line : '';

        foreach ($arProps as $prop) {
            if ($prop['CODE'] == 'DATE')
                $date = new DateTime($prop['VALUE']);
            if ($prop['CODE'] == 'PERSONAL_CITY') {
                if ($prop['VALUE'] == bhSettings::$city_id_msc) {
                    $phone_line = bhSettings::$phone_msc;
                    $hour_price_line = bhSettings::$hour_price_msc;
                }
                else {
                    $phone_line = bhSettings::$phone_spb;
                    $hour_price_line = bhSettings::$hour_price_spb;
                }
                $city = CSaleLocation::GetByID($prop['VALUE'], 'ru');
                $prop['VALUE'] = $city['CITY_NAME'];
            }
            $arProps[$prop['CODE']] = $prop['VALUE'];
        }
        $address_line = $arProps['PERSONAL_CITY'] . ', ' . $arProps['PERSONAL_STREET'];

        $date_line = $date->format("d.m.Y");
        $payment_line = '';
        if ($arOrder["PAY_SYSTEM_ID"] == 1 && $arOrder['PRICE'] > 0) {
            $payment_line = 'наличными ';
        }
        elseif ($arOrder["PAY_SYSTEM_ID"] == 2) {
            $payment_line = 'картой ';
        }

        $res = CSaleUserTransact::GetList(Array("ID" => "DESC"), array("ORDER_ID" => $ID, 'DEBIT' => 'N'));
        $SUM_PAID = 0;
        while ($r = $res->Fetch()) {
            if ($r['ORDER_ID'] > 0)
                $SUM_PAID += $r['AMOUNT'];
        }
        $allCurrency = CSaleLang::GetLangCurrency('s1');
        if ($SUM_PAID > 0) {
            $SUM_PAID = round($SUM_PAID);
            $SUM_PAID_f = SaleFormatCurrency(round($SUM_PAID, -1), $allCurrency);
        }
        if ($arOrder['DISCOUNT_VALUE'] > 0) {
            $DISCOUNT = round($arOrder['DISCOUNT_VALUE']);
            $DISCOUNT_f = SaleFormatCurrency(round($arOrder['DISCOUNT_VALUE'], -1), $allCurrency);
        }
        $order_price_line = SaleFormatCurrency(round($arOrder['PRICE'] - $SUM_PAID,-1), 'RUB') . ' р. ';
        $payment_line .= $order_price_line;

        if ($DISCOUNT > 0 || $SUM_PAID > 0) {
            $i = 0;
            $payment_line .= '(';
            if ($DISCOUNT > 0) {
                $payment_line .= 'скидка ' . $DISCOUNT_f . ' р.';
                $i++;
            }
            if ($SUM_PAID > 0) {
                if ($i > 0)
                    $payment_line .= ', ';
                $payment_line .= 'использовано бонусов: ' . $SUM_PAID_f . ' р.';
            };
            $payment_line .= ')';
        }

        $hours = array('час', 'часа', 'часов');
        $duration_line = round($arProps['DURATION'], 1) . ' ' . bhTools::words($arProps['DURATION'], $hours);

        $cleaner_name_line = $cleaner_photo_line = '';
        $db = CUser::GetList($b = "ID", $o = "DESC", array("ID" => $arProps['Cleaner']),
            array('SELECT' => array('ID', 'UF_RATING', 'NAME', 'PERSONAL_PHOTO', 'EMAIL')));
        if ($arCleaner = $db->Fetch()) {
            $cleaner_name_line = $arCleaner['NAME'];
            if (strlen($arCleaner['PERSONAL_PHOTO']) > 0) {
                $photo = CFile::getFileArray($arCleaner['PERSONAL_PHOTO']);
                $photo = $photo['SRC'];
            }
            else {
                $photo = '/layout/assets/images/content/cleaner-unknown.png';
            }

          echo   $cleaner_photo_line = 'https://' . $_SERVER['HTTP_HOST'] . $photo;
        }

        $urlVk = 'https://' . $_SERVER['HTTP_HOST'] . '/?utm_Advert=free-30&utm_user_id=' . $arUser['ID'];
        $urlFb = 'https://' . $_SERVER['HTTP_HOST'] . '/fb/?utm_Advert=free-30&utm_user_id=' . $arUser['ID'];
        $urlVk = urlencode($urlVk);
        $urlFb = urlencode($urlFb);

        $arFields = Array(
            "SERVER_NAME" => '',
            "NAME" => $arUser["NAME"],
            "EMAIL" => $arUser["EMAIL"],
            "ORDER_PRICE" => $order_price_line,
            "DATE" => $date_line,
            "ADDRESS" => $address_line,
            "ORDER" => $order_line,
            "DURATION" => $duration_line,
            "PAYMENT" => $payment_line,
            "CLEANER_NAME" => $cleaner_name_line,
            "CLEANER_PHOTO" => $cleaner_photo_line,
            "ORDER_ID" => $ID,
            "GT_PHONE" => $phone_line,
            "HOUR_PRICE" => $hour_price_line / 2,
            "SERVER_NAME" => $_SERVER['SERVER_NAME'],
            "VK_LINK" => $urlVk,
            "FB_LINK" => $urlFb
        );
        $bSend = false;
        if (strlen($arFields['EMAIL']) > 0) {
            $bSend = true;
        }
        if ($bSend) {
            $arGlobals = array();
            foreach ($arFields as $code => $val) {
                $arGlobals[] = array('name' => $code, 'content' => $val);
            }
            if ($_GET['debug']) {
                echo $ID;
                xmp(array(
                    'to' => array(
                        array(
                            'email' => $arFields['EMAIL'],
                            'name' => $arFields['NAME'],
                            'type' => 'to'
                        )
                    ),
                    'global_merge_vars' => $arGlobals,
                    'merge' => 'Y'));
            }
            else {
                echo $arFields['EMAIL'];
//                $token = bhSettings::$mandrillKey;
//                $mandrill = new Mandrill($token);
//
//                $mandrill->messages->sendTemplate(
//                    'mandrill',
//                    array(),
//                    array(
//                        'to' => array(
//                            array(
//                                'email' => $arFields['EMAIL'],
//                                'name' => $arFields['NAME'],
//                                'type' => 'to'
//                            )
//                        ),
//                        'global_merge_vars' => $arGlobals,
//                        'merge' => 'Y')
//                );
                /*$mandrill->messages->sendTemplate(
                    'mandrill',
                    array(),
                    array(
                        'to' => array(
                            array(
                                'email' => $arCleaner['EMAIL'],
                                'name' => $arCleaner['NAME'],
                                'type' => 'to'
                            )
                        ),
                        'global_merge_vars' => $arGlobals,
                        'merge' => 'Y')
                );*/
                file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/cron/logs/mailOrderDone_log.txt', $ID . "\n", FILE_APPEND);
            }
        }
    }
}

if($_REQUEST['ORDER_ID']){
    orderDone($_REQUEST['ORDER_ID']);
}
echo $arFields['EMAIL'];
