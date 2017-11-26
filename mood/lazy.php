<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

session_start();

$_SESSION['LAZYLINK'] = 'Y';

localRedirect('/order/basket/');


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>