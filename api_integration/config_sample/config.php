<?php

/*
 * Все основные настройки в одном файле
 */

return array(
    /*
     * Клиент
     */
    'client' => require dirname(__FILE__) . '/_client.php',
    /*
     * Наш локальный сервер
     */
    'server' =>  require dirname(__FILE__) . '/_server.php',    
    /*
     * Настройки всех типов поддерживаемых адаптеров
     */
    'types' => require dirname(__FILE__) . '/_types.php',
    /*
     * Адаптеры внешних подключений с сторонним АПИ
     */
    'adapters' => require dirname(__FILE__) . '/_adapters.php',
    /*
     * Настройки логов
     */
    'logs' => require dirname(__FILE__) . '/_logs.php',
    
);

