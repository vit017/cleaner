<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 04.08.14
 * Time: 16:08
 */

if(!empty($arResult['ERROR_MESSAGE']) && $arResult['ERROR_MESSAGE']['ERROR_TYPE']=='LOGIN'){
    $arResult['USER_LOGIN_ERROR'] = 'Y';
}
if($arResult['BACKURL'] != trim($_REQUEST['backurl'])){
	$arResult['BACKURL'] = trim($_REQUEST['backurl']);
}
?>