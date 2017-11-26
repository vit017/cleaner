<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 02.04.15
 * Time: 13:31
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Выполненный заказ');
?>
<div class="container">

    <h1 class="page-title"><?$APPLICATION->showTitle()?></h1>

    <section class="page-blocks clearfix">
        <span id="js-basket">
            <?$APPLICATION->IncludeComponent('breadhead:trade.detail', '', array(
                "ORDER_ID" => $_REQUEST["ID"],
                "USER_ID" => $USER->getID()
            ));?>
        </span>
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
        </div>
    </section>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>