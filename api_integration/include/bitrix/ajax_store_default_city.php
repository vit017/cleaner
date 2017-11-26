<?php

/*
 * Сохранить город по умолчанию
 */

require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/include/city.php', strip_tags($_POST['defaultCity']));