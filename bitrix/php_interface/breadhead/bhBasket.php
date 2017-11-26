<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 08.04.15
 * Time: 16:13
 */
CModule::IncludeModule('iblock');
CModule::IncludeModule('catalog');
CModule::IncludeModule('sale');
class bhBasket{

    public static function update($fuserID, $logout = false){
        $arBasket = self::getRealBasket($fuserID);
        $props = self::getBasketProps(array_keys($arBasket));

        foreach ($props as $id=>$props){
            $arBasket[$id]['PROPS'] = $props;
        }

        if ( $logout ){
            global $USER;
            $USER->Logout();
        }

        if ( $fuserID != CSaleBasket::GetBasketUserID() ){
            CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());
            CSaleBasket::DeleteAll($fuserID);
            if ( count($arBasket) > 0 ){
                foreach ($arBasket as $item){
                    $newID = Add2BasketByProductID($item['PRODUCT_ID'], round($item["QUANTITY"]), false, $item['PROPS']);
                }
            }
            $fuserID = CSaleBasket::GetBasketUserID();
        }
        return $fuserID;
    }

    public static function getRealBasket($fuserID){
        if ( !$fuserID )
            return false;
        $arBasket = array();
        $filter = array(
            'CAN_BUY' => 'Y',
            'DELAY' => 'N',
            'FUSER_ID' => $fuserID,
            'ORDER_ID' => "NULL"
        );
        $dbBasketItems = CSaleBasket::GetList(
            array("ID" => "ASC"),
            $filter,
            false,
            false,
            array("ID", "PRICE", "NAME", "PRODUCT_ID", "DISCOUNT_PRICE", "QUANTITY", "DELAY", "CAN_BUY", "CURRENCY",
                "PRODUCT_PROVIDER_CLASS", "CALLBACK_FUNC"
            )
        );

        while ($arItem = $dbBasketItems->Fetch()){
            $arBasket[$arItem['PRODUCT_ID']] = $arItem;
        }
        return $arBasket;
    }

    public static function getBasketProps($prodID){
        $return = array();
        $db = CIBlockElement::getList(array(), array('IBLOCK_ID'=>bhSettings::$IBlock_catalog, 'ID'=>$prodID));
        while($el = $db->getNextElement()){
            $id  = $el->fields['ID'];
            $props = $el->GetProperties();
            foreach($props as $prop){
                $return[$id][$prop['CODE']] = $prop['VALUE'];
            }
        }
        return $return;
    }

    public static function getBasket($fuserID, $priceType, $arFlat, $arBasket = false, $active = true){
        $arItems = self::getFullList($priceType, $active);
        if(!$arBasket){
            $arBasket = self::getRealBasket($fuserID);
        }
        $arItems = self::setQuantity($arItems, $arBasket, $arFlat);
        return $arItems;
    }

    public static function getBasketFormated($fuserID, $priceType, $arFlat = false, $arBasket = false, $addWord = true){
        $checkWord = bhSettings::$catalog_mustBe;
        $prop_duration = bhSettings::$catalog_duration;
        $prop_verb = bhSettings::$catalog_verb;

        if ( !is_array($arFlat) && !empty($arBasket) ) {
            $arItems = $arBasket;
        }elseif ( is_array($arFlat) ){
            $arItems = self::getBasket($fuserID, $priceType, $arFlat, $arBasket);
        }else{
            return false;
        }
        $arResult = array();

        foreach($arItems as $arItem){
            if( !isset($arItem['NAME_FORMATED']) ){
                $verb = false;
                if ( is_array($arItem['PROPERTIES'][$prop_verb]) && strlen($arItem['PROPERTIES'][$prop_verb]['VALUE']) > 0 ){
                    $verb = trim($arItem['PROPERTIES'][$prop_verb]['VALUE']);
                } elseif( !is_array($arItem['PROPERTIES'][$prop_verb]) && strlen($arItem['PROPERTIES'][$prop_verb]) > 0 ){
                    $verb = trim($arItem['PROPERTIES'][$prop_verb]);
                }
                if ( $verb ) {
                    $name = $verb.' '.$arItem['NAME'];
                    $arName = explode(' ', $name);
                    if ( count($arName) > 2 ){
                        $fName = array_shift($arName).' '.array_shift($arName)."&nbsp;";
                        $fName .= implode(' ', $arName);
                    }else{
                        $fName = $name;
                    }
                    $arItem['NAME_FORMATED'] = $fName;
                } else {
                    $arItem['NAME_FORMATED'] = $arItem['NAME'];
                }

            }

            if ($arItem['QUANTITY'] > 0 && $addWord){
                if ( (!is_array($arItem["PROPERTIES"]["SET_QUANTITY"]) && $arItem["PROPERTIES"]["SET_QUANTITY"] != '') ||  (is_array($arItem["PROPERTIES"]["SET_QUANTITY"]) && $arItem["PROPERTIES"]["SET_QUANTITY"]['VALUE'] != '') ) {
                    if ( is_array($arItem['PROPERTIES']['WORD']) ) {
                        $word = strlen($arItem['PROPERTIES']['WORD']['VALUE']) > 0 ? ' ' . trim($arItem['PROPERTIES']['WORD']['VALUE']) : '';
                    }
                    else {
                        $word = strlen($arItem['PROPERTIES']['WORD']) > 0 ? ' ' . trim($arItem['PROPERTIES']['WORD']) : '';
                    }

                    $arItem['NAME_FORMATED'] = $arItem['NAME_FORMATED'] . ' (' . round($arItem['QUANTITY']) . $word . ')';
                }
            }
            if ( strlen($arItem["PROPERTIES"][$checkWord]['VALUE']) > 0 ){
                $arResult['MAIN'][] = $arItem;
            } elseif ( strlen($arItem["PROPERTIES"]['SERVICE']['VALUE']) > 0 ){
                $arResult['SERVICES'][$arItem['CODE']] = $arItem;
            } else {
                $arResult['ADDITIONAL'][] = $arItem;
            }

            if ( $arItem['QUANTITY'] > 0 ){
                if ( is_array($arItem['PROPERTIES'][$prop_duration]) ) {
                    $arResult['FLAT_TIME'] += ($arItem['PROPERTIES'][$prop_duration]['VALUE'] * $arItem['QUANTITY']) / 60;
                } elseif (strlen($arItem['PROPERTIES'][$prop_duration])>0){
                    $arResult['FLAT_TIME'] += ($arItem['PROPERTIES'][$prop_duration] * $arItem['QUANTITY']) / 60;
                }
            }
        }

        return $arResult;
    }

    public static function getDuration($ids, $cnt){
        $duration = 0;
        $dbE = CIblockElement::GetList(array(), array('IBLOCK_ID'=>bhSettings::$IBlock_catalog, 'ID'=>$ids), false, false, array('ID', 'PROPERTY_DURATION'));
        while($element = $dbE->Fetch()){
            $duration += $element['PROPERTY_DURATION_VALUE'] * $cnt[$element['ID']];
        }
        return $duration;
    }

    private function getFullList($priceType, $active = true){
        $prop_verb = bhSettings::$catalog_verb;

        $arFilter = array(
            "IBLOCK_ID" => bhSettings::$IBlock_catalog,
            "IBLOCK_ACTIVE"=>"Y",
            "INCLUDE_SUBSECTIONS" => 'Y',
        );

        if ( $active ){
            $arFilter["ACTIVE"] = "Y";
        }
        $arSelect = array(
            "ID",
            "IBLOCK_ID",
            "CODE",
            "NAME",
            "ACTIVE",
            "SORT",
            "IBLOCK_SECTION_ID",
            "CATALOG_QUANTITY",
            "CATALOG_GROUP_".$priceType,
            "PROPERTY_*"
        );

        $arCatalog = array();
        $rsElements = CIBlockElement::GetList(array('SORT' => 'ASC'), $arFilter, false, false, $arSelect);
        while ($ob = $rsElements->getNextElement()) {
            $fields = $ob->getFields();
            $props = $ob->getProperties();

            $arItem = array(
                'ID' => $fields['ID'],
                'NAME' => $fields['NAME'],
                'SORT' => $fields['SORT'],
                'CODE' => $fields['CODE'],
                'PRICE' => $fields['CATALOG_PRICE_'.$priceType],
                'CURRENCY' => $fields['CATALOG_CURRENCY_'.$priceType],
                'PRODUCT_ID' => $fields['ID']
            );
            foreach($props as $prop){
                $arItem['PROPERTIES'][$prop['CODE']] = array(
                    'ID' => $prop['ID'],
                    'NAME' => $prop['NAME'],
                    'VALUE' => $prop['VALUE'],
                    'TYPE' => $prop['TYPE'],
                    'CODE' => $prop['CODE']
                );
            }

            //add verb to services

            $verb = false;
            if ( strlen($arItem['PROPERTIES'][$prop_verb]['VALUE']) > 0 ){
                $verb = trim($arItem['PROPERTIES'][$prop_verb]['VALUE']);
            }
            if ( $verb ) {
                $name = $verb.' '.$arItem['NAME'];
                $arName = explode(' ', $name);
                if ( count($arName) > 2 ){
                    $fName = array_shift($arName).' '.array_shift($arName)."&nbsp;";
                    $fName .= implode(' ', $arName);
                }else{
                    $fName = $name;
                }
                $arItem['NAME_FORMATED'] = $fName;
            } else {
                $arItem['NAME_FORMATED'] = $arItem['NAME'];
            }

            //get icon for service
            if ($arItem['PROPERTIES']['ICON']['VALUE']){
                $arIcon = CFile::GetFileArray($arItem['PROPERTIES']['ICON']['VALUE']);
                $arItem['PROPERTIES']['ICON']['VALUE'] = $arIcon['SRC'];
            }
            //end

            $arCatalog[] = $arItem;
        }
        return $arCatalog;
    }

    private function setQuantity($arItems, $arBasket, $arFlat){
        $prop_min = bhSettings::$catalog_min;
        $result = array();
        $mustbe = false;
        foreach($arItems as $arItem) {
            if (!$mustbe &&  strlen($arItem['PROPERTIES']['MUSTBE']['VALUE']) > 0){
                $mustbe = $arItem['ID'];
            }
            $arItem['QUANTITY'] = 0;
            if ( !empty($arBasket) ) {
                if ( isset($arBasket[$arItem["ID"]]) ) {
                    if ( strlen($arItem["PROPERTIES"][$prop_min]["VALUE"]) > 0 && $arItem["PROPERTIES"][$prop_min]["VALUE"] > round($arBasket[$arItem['ID']]["QUANTITY"]) )
                        $arItem['QUANTITY'] = $arItem["PROPERTIES"][$prop_min]["VALUE"];
                    else
                        $arItem['QUANTITY'] = round($arBasket[$arItem['ID']]["QUANTITY"]);
                    $arItem['BASKET_ID'] = $arBasket[$arItem['ID']]['ID'];
                }
            } else {
                if ( $arFlat['PROPS']['flat']['VALUE'] == $arItem['ID'] ) {
                    $arItem['QUANTITY'] = 1;
                } elseif ( !empty($arFlat['PROPS']['services']['VALUE']) && $arFlat['PROPS']['services']['VALUE'] != '' ) {
                    if ( in_array($arItem['ID'], $arFlat['PROPS']['services']['VALUE']) ) {
                        $arItem['QUANTITY'] = 1;
                        if ( strlen($arFlat['PROPS']['service_'.$arItem['ID']]['VALUE']) > 0 ){
                            $arItem['QUANTITY'] = intVal($arFlat['PROPS']['service_'.$arItem['ID']]['VALUE']);
                        }
                    }
                }
                if ( $arItem["PROPERTIES"][$prop_min]["VALUE"] && $arItem["PROPERTIES"][$prop_min]["VALUE"] > $arItem['QUANTITY'] ) {
                    $arItem['QUANTITY'] = $arItem["PROPERTIES"][$prop_min]["VALUE"];
                }
                if($arItem['QUANTITY'] == 0 && $arItem['ID'] == $mustbe ){
                    $arItem['QUANTITY'] = 1;
                }
            }
            /*if ( strlen($arItem["PROPERTIES"]["SET_QUANTITY"]["VALUE"]) > 0 && $arItem['QUANTITY'] > 0 ){
                $arItem['NAME_FORMATED'] = $arItem['NAME_FORMATED'].' (x'.round($arItem['QUANTITY']).')';
            }*/
            $result[$arItem['ID']] = $arItem;
        }
        return $result;
    }

    public static function add2basket($ID, $priceType, $arFields){
        $arPrice = array();
        $db_res = CPrice::GetList(
            array(),
            array(
                "PRODUCT_ID" => $ID,
                "CATALOG_GROUP_ID" => $priceType
            )
        );
        while ($ar_res = $db_res->Fetch()) {
            $arPrice[$ar_res["PRODUCT_ID"]]["PRODUCT_PRICE_ID"] = $ar_res["ID"];
            $arPrice[$ar_res["PRODUCT_ID"]]["PRICE"] = $ar_res["PRICE"];
        }
        foreach($ID as $i){
            $arFields[$i]['PRODUCT_PROVIDER_CLASS'] = '';
            $qnt = $arFields[$i]['QUANTITY'];
            $newID = Add2BasketByProductID($i, $qnt, array(), $arFields[$i]);
            CSaleBasket::Update($newID, array_merge($arFields[$i], $arPrice[$i]));
        }

    }

    public static function getByOrderId($orderID){
        if ( !$orderID || (is_array($orderID) && empty($orderID)) || (!is_array($orderID) && $orderID <= 0) ) return false;

        $filter = array(
            //'CAN_BUY' => 'Y',
            //'DELAY' => 'N',
            'ORDER_ID' => $orderID
        );

        $dbBasketItems = CSaleBasket::GetList(
            array("ID" => "ASC"),
            $filter,
            false,
            false,
            array("ID", "PRICE", "NAME", "PRODUCT_ID", "DISCOUNT_PRICE", "QUANTITY", "DELAY", "CAN_BUY", "CURRENCY",
                "PRODUCT_PROVIDER_CLASS", "CALLBACK_FUNC", "ORDER_ID", "FUSER_ID"
            )
        );

        $arProdIds = array();
        while ($arItem = $dbBasketItems->Fetch()){
            $arBaskets[$arItem["ORDER_ID"]][$arItem['PRODUCT_ID']] = $arItem;
            $arProdIds[] = $arItem['PRODUCT_ID'];
        }

        $arProps = self::getBasketProps($arProdIds);

        $fuserID = 0;
        $arOrders = array();
        foreach ( $arBaskets as $id => $order ) {
            foreach ( $order as &$product ) {
                $fuserID = $product['FUSER_ID'];
                $product['PROPERTIES'] = $arProps[$product['PRODUCT_ID']];
            }
            $arBasket = self::getBasketFormated($fuserID, false, false, $order);
            $arOrders[$id] = $arBasket;
        }
        return $arOrders;
    }


    public static function getItemsByOrderId($orderID){
        $filter = array(
            //'CAN_BUY' => 'Y',
            //'DELAY' => 'N',
            'ORDER_ID' => $orderID
        );
        $dbBasketItems = CSaleBasket::GetList(
            array("ID" => "ASC"),
            $filter,
            false,
            false,
            array("ID", "PRICE", "NAME", "PRODUCT_ID", "DISCOUNT_PRICE", "QUANTITY", "DELAY", "CAN_BUY", "CURRENCY",
                "PRODUCT_PROVIDER_CLASS", "CALLBACK_FUNC", "ORDER_ID", "FUSER_ID"
            )
        );

        $arBasketIds = array();
        while ($arItem = $dbBasketItems->Fetch()){
            $arBasketIds[$arItem["PRODUCT_ID"]] = $arItem;
        }
        return $arBasketIds;
    }
}
