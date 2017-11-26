<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 08.04.15
 * Time: 15:24
 */
CModule::IncludeModule('iblock');
CModule::IncludeModule('sale');
class bhApartment{

    public static function getFlat($userId = false){
        if ( !$userId ){
            global $USER;
            $userId = $USER->getID();
        }

        $arFlat = array();
        if ( $userId > 0 ){
            $arFilter = Array(
                "IBLOCK_ID" => bhSettings::$IBlock_flats,
                "ACTIVE" => "Y",
                "PROPERTY_user" => $userId
            );

            $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1));
            if ($ob = $res->getNextElement()) {
                $fields = $ob->getFields();
                $arFlat = array(
                    'NAME'=>$fields['NAME'],
                    'CODE'=>$fields['CODE'],
                    'ID'=>$fields['ID']
                );
                $props = $ob->getProperties();
                foreach ($props as  $prop){
                    $arFlat['PROPS'][$prop['CODE']] = $prop;
                }
            }
        }
        return $arFlat;
    }

    public static function setFlat($userID, $arBasket, $address, $wishCleaner = false){
        $flatParams = array('user' => $userID);
        foreach ($arBasket['MAIN'] as $item){
            $flatParams['flat']['VALUE'] = $item['PRODUCT_ID'];
        }
        $arQuantity = array();
        foreach ($arBasket['ADDITIONAL'] as $item){
            $flatParams['services'][] = $item['PRODUCT_ID'];
            $arQuantity['service_'.$item['PRODUCT_ID']] = $item['QUANTITY'];
        }
        $properties = CIBlockProperty::GetList(
            array(),
            array(
                "ACTIVE" => "Y",
                "IBLOCK_ID" => bhSettings::$IBlock_flats,
            )
        );
        while ($prop_fields = $properties->GetNext())
        {
            if ( isset($arQuantity[$prop_fields["CODE"]]) ) {
                $flatParams[$prop_fields["CODE"]] = $arQuantity[$prop_fields["CODE"]];
                unset($arQuantity[$prop_fields["CODE"]]);
            }
        }

        if ( !empty($arQuantity) ){
            foreach ($arQuantity as $code=>$val){
                $arFields = array(
                    "CODE" => $code,
                    "NAME" => $code,
                    "ACTIVE" => "Y",
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => bhSettings::$IBlock_flats
                );
                $ibp = new CIBlockProperty;
                if($ibp->Add($arFields)){
                    $flatParams[$code] = $val;
                }
            }
        }

        if ( $wishCleaner > 0 ){
            $flatParams["wish_cleaner"] = $wishCleaner;
        }

        $flatParams["address"] = $address;
        $flatID = self::hasFlat($userID);

        if ( $flatID ){
            $flatParams["cleaner"] = self::getCleaners($userID);
        }

        $arFlatArray = Array(
            "NAME"    => $address,
            "IBLOCK_ID"      => bhSettings::$IBlock_flats,
            "PROPERTY_VALUES" => $flatParams
        );

        $el = new CIBlockElement;
        if ( !$flatID ){
            return $el->Add($arFlatArray);
        } else {
            return $el->Update($flatID, $arFlatArray);
        }
    }

    public static function hasFlat($userID){
        $arFilter = Array(
            "IBLOCK_ID" => bhSettings::$IBlock_flats,
            "ACTIVE" => "Y",
            "PROPERTY_user" => $userID
        );

        $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1));
        if ($ob = $res->getNextElement()) {
            $fields = $ob->getFields();
            return $fields['ID'];
        } else {
            return false;
        }
    }

    public static function getFlatFormated($userId = false){
        $arFlat = self::getFlat($userId);
        $arCatalog = self::getFullList();
        return self::format($arFlat, $arCatalog);
    }

    public static function getCleaners($userId, $arFlat = false){
        if ( !$arFlat ) $arFlat = self::getFlat($userId);
        if ( !empty($arFlat['PROPS']['cleaner']['VALUE']) ){
            return $arFlat['PROPS']['cleaner']['VALUE'];
        } else {
            return array();
        }
    }

    public static function getProp($flatID, $code){
        $db = CIBlockElement::GetProperty(bhSettings::$IBlock_flats, $flatID, array(), array('CODE'=>$code));
        if ( $arProp = $db->fetch()){
            return $arProp;
        } else {
            return false;
        }
    }

    private function getFullList(){
        $prop_verb = bhSettings::$catalog_verb;

        $arFilter = array(
            "IBLOCK_ID" => bhSettings::$IBlock_catalog,
            "IBLOCK_ACTIVE"=>"Y",
            "ACTIVE"=>"Y",
            "GLOBAL_ACTIVE"=>"Y",
            "INCLUDE_SUBSECTIONS" => 'Y',
        );

        $arSelect = array(
            "ID",
            "IBLOCK_ID",
            "CODE",
            "NAME",
            "ACTIVE",
            "SORT",
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
                'CODE' => $fields['CODE']
            );
            foreach($props as $prop){
                if ( $prop['VALUE'] ){
                    $arItem['PROPERTIES'][$prop['CODE']] = array(
                        'ID' => $prop['ID'],
                        'NAME' => $prop['NAME'],
                        'VALUE' => $prop['VALUE'],
                        'CODE' => $prop['CODE']
                    );
                }
            }

            //add verb to services
            $verb = false;
            if ( $arItem['PROPERTIES'][$prop_verb]['VALUE'] ){
                $verb = trim($arItem['PROPERTIES'][$verb]['VALUE']);
            }
            if ( $verb ) {
                $name = $verb.' '.$arItem['NAME'];
                $arName = explode(' ', $name);
                if ( count($arName) > 2 ){
                    $fName = array_shift($arName).' '.array_shift($arName)."<br/>";
                    $fName .= implode(' ', $arName);
                }else{
                    $fName = $name;
                }
                $arItem['NAME_FORMATED'] = $fName;
            } else {
                $arItem['NAME_FORMATED'] = $arItem['NAME'];
            }

            //get icon for service
            if ( $arItem['PROPERTIES']['ICON']['VALUE'] ){
                $arItem['PROPERTIES']['ICON']['VALUE'] = CFile::getPath($arItem['PROPERTIES']['ICON']['VALUE']);
            }
            //end

            $arCatalog[$arItem['CODE']] = $arItem;
        }
        return $arCatalog;
    }

    private function format($arFlat, $arCatalog){
        foreach ($arCatalog as $i=>$product){
            if ( strstr($product['CODE'], 'services') ){
                $qnt = 0 ;
                if ( in_array($product['ID'], $arFlat['PROPS']['services']['VALUE']) ){
                    $qnt = 1;
                    if ( isset($arFlat['PROPS']['service_'.$product['ID']]) ){
                        $qnt = intVal($arFlat['PROPS']['service_'.$product['ID']]['VALUE']);
                    }
                }
                $product['VALUE'] = $qnt;

                if ( $product['PROPERTIES']['VERB']['VALUE'] ){
                    $product['NAME_FORMATED'] = $product['PROPERTIES']['VERB']['VALUE'].' '.$product['NAME'];
                }else{
                    $product['NAME_FORMATED'] = $product['NAME'];
                }

                if ( $product['PROPERTIES']['ICON']['VALUE'] ){
                    $product['ICON'] = CFile::getPath($product['PROPERTIES']['ICON']['VALUE']);
                }

                $arFlat['PROPS']['services']['SERVICES'][$product['CODE']] = $product;
            } elseif( strstr($product['CODE'], 'FLAT') ){
                if ( $arFlat['PROPS']['flat']['VALUE'] == $product['ID'] ){
                    $product['VALUE'] = 1;
                };

                if ( $product['PROPERTIES']['MUSTBE']['VALUE'] ){
                    $product['NAME_FORMATED'] = $product['NAME']." м&#178;";
                }else{
                    $product['NAME_FORMATED'] = $product['NAME'];
                };

                $arFlat['PROPS']['flat']['VARIANTS'][$product['ID']] = $product;
            }
        }
        $result = $arFlat['PROPS'];
        $result['ID'] = $arFlat['ID'];
        return $result;
    }

    public static function saveCleaner($cleanerID, $userID){
        $arFlat = self::getFlat($userID);
        $arCleaners = self::getCleaners($userID, $arFlat);

        if ( !array_search($cleanerID, $arCleaners) ) {
            $arCleaners[] = $cleanerID;
            $arCleaners = array_unique($arCleaners);
            return CIBlockElement::SetPropertyValuesEx($arFlat['ID'], false, array('cleaner' => $arCleaners));
        } else{
            return true;
        }
    }
}