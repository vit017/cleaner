<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 04.08.14
 * Time: 16:08
 */
if(!empty($arParams['AUTH_RESULT']) && $arParams['AUTH_RESULT']['ERROR_TYPE']=='LOGIN' && !empty($_POST)){
    $arResult['USER_LOGIN_ERROR'] = 'Y';

	$arResult['ERROR_MESSAGE']['MESSAGE'] = $arParams['~AUTH_RESULT']["MESSAGE"];
}else{
	unset($arParams['AUTH_RESULT']);
}
if($arResult['BACKURL'] != trim($_REQUEST['backurl'])){
	$arResult['BACKURL'] = trim($_REQUEST['backurl']);
}
?>