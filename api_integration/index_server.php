<?php

/*
 * Точка входа для сервера
 */

require_once dirname(__FILE__) . '/protected/import_common.php';

$server = new TaxiServer();
$server->processRequest();