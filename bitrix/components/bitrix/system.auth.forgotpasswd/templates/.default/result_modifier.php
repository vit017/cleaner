<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 05.08.14
 * Time: 12:36
 */
if(!empty($arParams['AUTH_RESULT'])){
	$arParams['AUTH_RESULT']['MESSAGE'] = 'Пользователь с такой почтой не зарегистрирован';
}
xmp($arParams);
xmp($arResult);
?>