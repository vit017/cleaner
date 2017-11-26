<?php

/*
 * Загрузка и компиляция скриптов
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/api_integration/utils/PackageLoader.php';

/*
 * Используем загрузчик и минимизатор JS
 */
$loader = new PackageLoader();

$loader->COMPILE_FLAG = 'COMPILE_ASSETS';

$loader->jsMap = require dirname(__FILE__) . '/js_package_loader_map.php';

echo $loader->createLoaderTags();
/*
 * Файл общего конфига
 */
$commonConfigJsPath = $_SERVER['DOCUMENT_ROOT'] . '/api_integration/config/js/init.js';
if (is_file($commonConfigJsPath)) {
    echo '<script>' . file_get_contents($commonConfigJsPath) . '</script>';
}
