<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 08.04.15
 * Time: 11:53
 */
CModule::IncludeModule('sale');
class bhHandler{

    public static function OnBeforeUserAdd(&$arFields){
        if ( !bhTools::isCleanerGroup($arFields['GROUP_ID']) )
            return;

        global $APPLICATION;
        $error = false;

        if ( $arFields['NAME'] == ''){
            $APPLICATION->throwException("Пожалуйста, введите имя клинера.");
            $error = true;
        }

        if ( $arFields['PERSONAL_PHONE'] == ''){
            $APPLICATION->throwException("Пожалуйста, введите номер телефона клинера (без +7).");
            $error = true;
        }

        if ( $arFields['PERSONAL_CITY'] == ''){
            $APPLICATION->throwException("Пожалуйста, введите город клинера.<br>Допустимые значения:<br>".bhSettings::$city_id_spb." - СПб <br>".bhSettings::$city_id_msc." - Москва");
            $error = true;
        } else {
            if ( !in_array($arFields['PERSONAL_CITY'], array(bhSettings::$city_id_spb, bhSettings::$city_id_msc)) ){
                $APPLICATION->throwException("Введен неверный город<br>Допустимые значения:<br>".bhSettings::$city_id_spb." - СПб <br>".bhSettings::$city_id_msc." - Москва");
                $error = true;
            }
        }

        if ( !isset($arFields['PERSONAL_PHOTO'] )){
            if ( isset($arFields['ID']) && $arFields['ID'] > 0 ){
                $bd = CUser::getByID($arFields['ID']);
                if ( $arUser = $bd->fetch() ){
                    if ( $arUser['PERSONAL_PHOTO'] == '' ){
                        $APPLICATION->throwException("Пожалуйста, добавьте фотографию клинера.");
                        $error = true;
                    };
                }
            } else {
                $APPLICATION->throwException("Пожалуйста, добавьте фотографию клинера.");
                $error = true;
            }
        }

        if ( $error ) return false;

        if ( $arFields['ACTIVE'] == 'Y' ){
            if ( !bhCleaner::addCleanerToOrderPropValues($arFields) ) {
                return false;
            }
        } elseif ( isset($arFields['ID']) ){
            if ( !bhCleaner::deleteCleanerFromOrderPropValues($arFields['ID']) ) {
                return false;
            }
        }
    }

    public static function OnBeforeUserDelete($user_id){
        if ( !bhCleaner::deleteCleanerFromOrderPropValues($user_id) ) {
            return false;
        }
    }

    public static function OnSaleStatusOrder($ID, $val){
        $arOrder = CSaleOrder::getByID($ID);
        $arProps = bhOrder::getProps($ID);

        if (empty($arOrder)) return;

        if ( $val == 'F' ){
            bhOrder::setStatusF($arOrder, $arProps);
            if ( isset($arProps['Cleaner']) && $arProps['Cleaner']['VALUE'] > 0 ){
                bhApartment::saveCleaner($arProps['Cleaner']['VALUE'], $arOrder['USER_ID']);
            }
        }
    }

    public static function OnOrderAdd($ID, $fields){
        if ( $fields["PAY_SYSTEM_ID"] == 1 ){
            $fields["ID"] = $ID;
            bhOrder::setStatusA($fields);
        }
    }

    public static function OnOrderUpdate($ID, $arFields){
        $arBasket = bhBasket::getItemsByOrderId($ID);
        $ids = array();
        $cnt = array();
        foreach ($arBasket as $product){
            $ids[] = $product['PRODUCT_ID'];
            $cnt[$product['PRODUCT_ID']] += $product['QUANTITY'];
        };

        $db_vals = CSaleOrderPropsValue::GetList(
            array("SORT" => "ASC"),
            array(
                "ORDER_ID" => $ID,
                "CODE" => 'Cleaner'
            )
        );
        if ($arVals = $db_vals->Fetch()) {
            $val = CSaleOrderPropsVariant::GetByValue($arVals['ORDER_PROPS_ID'], $arVals['VALUE']);
            if ( $val['NAME'] == 'Нет клинера'){
                bhCleaner::removeFromOrder($arVals['ID'], $ID);
            };
        }

        $duration = bhBasket::getDuration($ids, $cnt);
        $duration = $duration/60;
        return bhOrder::setProp($ID, 'DURATION', $duration);
    }

    public static function OnBeforeUserLogin(&$arFields)
    {
        if($arFields['LOGIN'] != 'TEST') {
            CModule::IncludeModule('sale');
            $fuser_id = CSaleBasket::GetBasketUserID();

            $arBasket = bhBasket::getRealBasket($fuser_id);
            if (count($arBasket) > 0) {
                $_SESSION['BH_BEFORE_LOGIN_FUSER_ID'] = $fuser_id;
                $_SESSION['BH_BEFORE_LOGIN_BASKET'] = serialize($arBasket);
                $_SESSION['BH_SAVE_DATE_TIME'] = true;
            }
        }
    }

    public static function OnAfterUserLogin(&$fields)
    {
        // если логин успешен
        if ( $fields['USER_ID'] > 0 ) {
            CModule::IncludeModule('sale');
            if ( !bhTools::isCleaner() ) {
                if(strlen($_SESSION["SALE_COUPON_UTM"]) > 0){
                    unset($_SESSION["BH_SALE_BASKET_MESSAGE"]);
                    unset($_SESSION["SALE_BASKET_MESSAGE"]);
                    unset($_SESSION["SALE_SOURCE_UTM"]);
                    unset($_SESSION["SALE_COUPON_UTM"]);
                }

                if ( isset($_SESSION['BH_BEFORE_LOGIN_FUSER_ID'] ) && $_SESSION['BH_BEFORE_LOGIN_FUSER_ID']>0){

                    CModule::IncludeModule('catalog');
                    $fuser_id = $_SESSION['BH_BEFORE_LOGIN_FUSER_ID'];
                    $arBasket = unserialize($_SESSION['BH_BEFORE_LOGIN_BASKET']);

                    if($fuser_id != CSaleBasket::GetBasketUserID()){

                        CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());
                        CSaleBasket::DeleteAll($fuser_id);
                        if(count($arBasket)>0){
                            foreach($arBasket as $qnt){
                                $newID = Add2BasketByProductID($qnt['ID'], $qnt["QNT"], false, $qnt['PROPS']);
                            }
                        }
                        unset($_SESSION['BH_BEFORE_LOGIN_FUSER_ID']);
                        unset($_SESSION['BH_BEFORE_LOGIN_BASKET']);
                        if(!empty($_SESSION['ORDER'][$fuser_id])){
                            $_SESSION['ORDER'][CSaleBasket::GetBasketUserID()] = $_SESSION['ORDER'][$fuser_id];
                            unset($_SESSION['ORDER'][$fuser_id]);
                        }
                    }
                }
                global $USER;
                $dbUser = $USER->getById($fields['USER_ID']);
                if($arUSer = $dbUser->fetch()){
                    if(strlen($arUSer['PERSONAL_PHONE'])>0){
                        bhTools::setConfirm($arUser['PERSONAL_PHONE'], false, false);
                    }
                }

                if ( $_SESSION['BH_CITY_CHANGED'] ){
                    bhTools::setPriceType();
                } else {
                    bhTools::setPriceType(true);
                }

                bhTools::updatePrices();
            } else {
                CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());

            }
            $arUser = CUser::GetByID($fields['USER_ID'])->fetch();
            $_SESSION['CITY_ID'] = $arUser['PERSONAL_CITY'] ? $arUser['PERSONAL_CITY'] : Cities::$names['spb'];
            $_SESSION['TOWN'] = Cities::$codes[$_SESSION['CITY_ID']];
        }
    }

    public static function OnSaleCancelOrder($ID, $val, $description){
        if ( $val == 'Y' ){
            bhOrder::onCancel($ID);
        }
    }

    public static function OnSalePayOrder($ID, $val){
        if ( $val == 'Y' ){
            bhOrder::onPay($ID);
        }
    }

    public static function OnOrderStatusSendEmail($ID, &$eventName, &$arFields, $val){

        if ( $val == 'F') {
            require_once($_SERVER["DOCUMENT_ROOT"]."/cron/mailOrderDone.php");
            orderDone($ID);
        }
    }

}