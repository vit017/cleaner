<?php

/*
 * Точка входа для клиента
 */
require_once dirname(__FILE__) . '/protected/import_common.php';

$client = new TaxiClient();
$client->processRequest();