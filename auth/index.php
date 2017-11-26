<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 13.05.14
 * Time: 14:16
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
if($USER->isAuthorized()){
    localRedirect('/user/');
}
$APPLICATION->setTitle('Вход');
?>
<div class="container">
    <?
        if($_REQUEST['forgot_password'] == 'yes'){
            $APPLICATION->IncludeComponent("bitrix:system.auth.forgotpasswd", ".default", array(
                    "REGISTER_URL" => "",
                    "FORGOT_PASSWORD_URL" => "/auth/",
                    "PROFILE_URL" => "/user/",
                    "SHOW_ERRORS" => "Y"
                ),
                false
            );
        }elseif($_REQUEST['change_password'] == 'yes'){
            $APPLICATION->IncludeComponent("bitrix:system.auth.changepasswd", ".default", array(
                    "REGISTER_URL" => "",
                    "FORGOT_PASSWORD_URL" => "/auth/",
                    "PROFILE_URL" => "/user/",
                    "SHOW_ERRORS" => "Y"
                ),
                false
            );
        }else{
            $APPLICATION->IncludeComponent("bitrix:system.auth.form", ".default", array(
                    "REGISTER_URL" => "",
                    "FORGOT_PASSWORD_URL" => "/auth/",
                    "PROFILE_URL" => "/user/",
                    "SHOW_ERRORS" => "Y"
                ),
                false
            );
        }
    ?>
</div>
<?
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>