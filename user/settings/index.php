<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 13.05.14
 * Time: 13:56
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Мои настройки");
?>
    <div class="container">
        <h1 class="page-title">Настройки</h1>
        <section class="page-blocks clearfix">
            <div class="page-blocks__item page-blocks__item_type_main">
                <?$APPLICATION->IncludeComponent("breadhead:main.profile2.0",'', array());?>
            </div>
            <?include($_SERVER["DOCUMENT_ROOT"]."/include/user_aside.php");?>
        </section>
    </div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/include/select_cleaner_support.php");?>