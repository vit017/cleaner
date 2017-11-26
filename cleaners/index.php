<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 02.04.15
 * Time: 12:32
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Уборки');
?>
<div class="container">

  <h1 class="page-title">Уборки</h1>

    <section class="page-blocks clearfix">
      <?$APPLICATION->IncludeComponent('breadhead:trade.list', '', array(
          'USER_ID' => $USER->getID(),
          'CLEANER' => false
      ));?>
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