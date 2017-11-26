<?php
if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/retailcrm/export_run.php")){
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/retailcrm/export_run.php");
} else {
    ignore_user_abort(true);
    set_time_limit(0);

    global $APPLICATION;
    if (!CModule::IncludeModule("iblock")){
        return;
    }
    if (!CModule::IncludeModule("catalog")){
        return;
    }
    if (!CModule::IncludeModule("intaro.retailcrm")){
        return;
    }
    
    $rsSites = CSite::GetList($by, $sort, array('ACTIVE' => 'Y'));
    while ($ar = $rsSites->Fetch()) {
        if ($ar['DEF'] == 'Y') {
            $SERVER_NAME = $ar['SERVER_NAME'];
        }
    }

    $iblockProperties = array(
        "article" => "article",
        "manufacturer" => "manufacturer",
        "color" =>"color",
        "weight" => "weight",
        "size" => "size",
        "length" => "length",
        "width" => "width",
        "height" => "height",
    );
    $IBLOCK_PROPERTY_SKU = array();
    $IBLOCK_PROPERTY_UNIT_SKU = array();
    foreach ($iblockProperties as $prop) {
        $skuUnitProps = ('IBLOCK_PROPERTY_UNIT_SKU' . "_" . $prop);
        $skuUnitProps = $$skuUnitProps;
        if (is_array($skuUnitProps)) {
            foreach ($skuUnitProps as $iblock => $val) {
                $IBLOCK_PROPERTY_UNIT_SKU[$iblock][$prop] = $val;
            }
        }

        $skuProps = ('IBLOCK_PROPERTY_SKU' . "_" . $prop);
        $skuProps = $$skuProps;
        if (is_array($skuProps)) {
            foreach ($skuProps as $iblock => $val) {
                $IBLOCK_PROPERTY_SKU[$iblock][$prop] = $val;
            }
        }
    }
    $IBLOCK_PROPERTY_PRODUCT = array();
    $IBLOCK_PROPERTY_UNIT_PRODUCT = array();
    foreach ($iblockProperties as $prop) {
        $productUnitProps = "IBLOCK_PROPERTY_UNIT_PRODUCT" . "_" . $prop;
        $productUnitProps = $$productUnitProps;
        if (is_array($productUnitProps)) {
            foreach ($productUnitProps as $iblock => $val) {
                $IBLOCK_PROPERTY_UNIT_PRODUCT[$iblock][$prop] = $val;
            }
        }

        $productProps = "IBLOCK_PROPERTY_PRODUCT" . "_" . $prop;
        $productProps = $$productProps;
        if (is_array($productProps)) {
            foreach ($productProps as $iblock => $val) {
                $IBLOCK_PROPERTY_PRODUCT[$iblock][$prop] = $val;
            }
        }
    }

    $loader = new RetailCrmICML();
    $loader->profileID = $PROFILE_ID;
    $loader->iblocks = $IBLOCK_EXPORT;
    $loader->propertiesSKU = $IBLOCK_PROPERTY_SKU;
    $loader->propertiesUnitSKU = $IBLOCK_PROPERTY_UNIT_SKU;
    $loader->propertiesProduct = $IBLOCK_PROPERTY_PRODUCT;
    $loader->propertiesUnitProduct = $IBLOCK_PROPERTY_UNIT_PRODUCT;
    $loader->filename = $SETUP_FILE_NAME;
    $loader->serverName = $SERVER_NAME;
    $loader->application = $APPLICATION;
    $loader->loadPurchasePrice = $LOAD_PURCHASE_PRICE == 'Y';
    $loader->Load();
}