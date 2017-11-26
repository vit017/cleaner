<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<? $APPLICATION->SetTitle("Заказать уборку");?>


<div id="js-basket">
    <?
    $APPLICATION->IncludeComponent("breadhead:basket.catalog2.0", "orderNew", array(
        "IBLOCK_TYPE" => "main",
        "IBLOCK_ID" => "1",
        "SECTION_ID" => "",
        "INCLUDE_SUBSECTIONS" => "Y",
        "SHOW_ALL_WO_SECTION" => "Y",
        "PRICE_CODE" => array(
            0 => "base",
        ),
        "PROPERTY_CHECK" => "MUSTBE",
        "BASKET_URL" => "/order/",
        "ACTION_VARIABLE" => "action",
        "ACTION_NAME" => "refreshBasket",
        "PRODUCT_ID_VARIABLE" => "id",
        "USE_PRODUCT_MINIMUM" => "Y",
        "PROPERTY_MINIMUM" => "ORDER_MIN",
        "PROPERTY_NAME_FORMS" => "NAME_FORMS",
        "PROPERTY_DURATION" => "DURATION",
        "SUBMIT_TITLE" => "Выбрать дату",
        "PRODUCT_QUANTITY_VARIABLE" => "QUANTITY"
        ),
        false
    );

    ?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

