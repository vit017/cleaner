<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 02.11.2016
 * Time: 10:54
 */
if ($_GET['pass'] != '234567892315dsFD') die;
if (!isset($_GET['login']) || !isset($_GET['userpass'])) die('no login or userpass');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

global $USER;
$myUSER = $USER->GetByLogin($_GET['login'])->fetch();
if (!$myUSER) die('no user ' . $_GET['login']);
$fields = Array(
    "LOGIN" => $_GET['login'],
    "PASSWORD"       => $_GET['userpass'], //новый пароль
    "CONFIRM_PASSWORD"  => $_GET['userpass'], // подтверждение нового пароля
    "ACTIVE"  => 'Y', // подтверждение нового пароля
);
echo '<pre>';
echo 'result update: ' ;
var_dump($USER->Update($myUSER['ID'],$fields));
if ($USER->LAST_ERROR) {
    echo PHP_EOL . 'last error: ' . $USER->LAST_ERROR;
}
echo '</pre>';
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");