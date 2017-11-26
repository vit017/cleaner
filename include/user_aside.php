<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 11.02.2015
 * Time: 18:01
 */ ?>

<div class="page-blocks__item page-blocks__item_type_aside">
    <?$APPLICATION->IncludeComponent("bitrix:menu", "aside", array(
            "ROOT_MENU_TYPE" => "left",
            "MENU_CACHE_TYPE" => "N",
            "MENU_CACHE_TIME" => "3600",
            "MENU_CACHE_USE_GROUPS" => "Y",
            "MENU_CACHE_GET_VARS" => array(
            ),
            "MAX_LEVEL" => "1",
            "CHILD_MENU_TYPE" => "",
            "USE_EXT" => "N",
            "DELAY" => "N",
            "ALLOW_MULTI_SELECT" => "N"
        ),
        false
    );?>
    <div class="" style="margin: -30px 0 20px 0;">
        <p>
            Если у вас возникли проблемы, вопросы или просто хочется
            с кем-нибудь поговорить — звоните.
        </p>
        <h2><span class="phone phone_big js-phone"><?=phonePurify(TOLLFREENUMBER);?></span></h2>
    </div>

    <?
    $APPLICATION->IncludeComponent("breadhead:basket.catalog2.0", "cleaner", array(
        "IBLOCK_TYPE" => "main",
        "IBLOCK_ID" => "1",
        "SECTION_ID" => "1",
        "INCLUDE_SUBSECTIONS" => "Y",
        "SHOW_ALL_WO_SECTION" => "Y",
        "PRICE_CODE" => array(
            0 => "base",
        ),
        "CHECK_MUSTBE" => "Y",
        "PROPERTY_CHECK" => "MUSTBE",
        "BASKET_URL" => "/order/basket/",
        "ACTION_VARIABLE" => "action",
        "ACTION_NAME" => "checkPrice",
        "PRODUCT_ID_VARIABLE" => "id",
        "USE_PRODUCT_MINIMUM" => "Y",
        "PROPERTY_MINIMUM" => "ORDER_MIN",
        "PROPERTY_NAME_FORMS" => "NAME_FORMS",
        "PROPERTY_DURATION" => "DURATION",
        "SUBMIT_TITLE" => "Узнать стоимость",
        "PRODUCT_QUANTITY_VARIABLE" => "quantity"
    ),
        false
    );
    ?>

</div>