<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 05.08.14
 * Time: 12:50
 */
if($arParams['AUTH_RESULT']['TYPE']=='ERROR'){
	$arParams['AUTH_RESULT'] = $arParams['~AUTH_RESULT'];
}
?>