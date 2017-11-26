<?php
/**
 * Created by PhpStorm.
 * User: d.osoev
 * Date: 10.10.2017
 * Time: 14:06
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

//print_r($_REQUEST);
?>

<?
    if( ($_REQUEST["ORDER_PROP_PERSONAL_PHONE"]!="") && ($_REQUEST["USER_PASSWORD"]!= "") ) {
        $phone = substr_replace($_REQUEST["ORDER_PROP_PERSONAL_PHONE"], null, 0, 2);
        $filter = Array
        (
            "PERSONAL_PHONE" => $phone
        );
        $rsUsers = CUser::GetList(($by = "personal_country"), ($order = "desc"), $filter); // выбираем пользователей
        $is_filtered = $rsUsers->is_filtered; // отфильтрована ли выборка ?
        $rsUsers->NavStart(50); // разбиваем постранично по 50 записей
        $rsUsers->NavPrint(GetMessage("PAGES")); // печатаем постраничную навигацию
        while ($rsUsers->NavNext(true, "f_")) {
            "[" . $f_ID . "] (" . $f_LOGIN . ") " . $f_NAME . " " . $f_LAST_NAME . "<br>";
            $userId = $f_ID;
            $userLogin = $f_LOGIN;
            break;
        };


        global $USER;
        if (!is_object($USER)) $USER = new CUser;
        $arAuthResult = $USER->Login($userLogin, $_REQUEST["USER_PASSWORD"], "Y");
         $APPLICATION->arAuthResult = $arAuthResult;
        print_r($arAuthResult);
        if($arAuthResult["TYPE"] == 'ERROR')
            {
                localredirect('/user/?ERROR=1');
            } else
            {
                localredirect('/user/');
            }
    } else
    {
        localredirect('/user/?ERROR=1');
    }
?>
