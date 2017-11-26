<?php

/*
 * Точка входа для независимой подсистемы централизованного обновления
 */

if (is_file(dirname(__FILE__) . '/protected/import_common.php')){
    include_once dirname(__FILE__) . '/protected/import_common.php';
}

TaxiEnv::$autoloader->enableTracking = true;

$updatesComponent = new TaxiUpdates();
$updatesComponent->processRequest();