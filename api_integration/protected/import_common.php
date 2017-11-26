<?php

ini_set('error_reporting', E_ALL);
ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");
ini_set('allow_url_fopen', '1');

date_default_timezone_set('Europe/Moscow');

date_default_timezone_set('Europe/Moscow');

/*
 * Общий загрузчик для всех классов
 */

/**
 * Конфигурация среды и настройки:
 *  - рабочие директории
 *  - настройки логов\конфигов
 *  - флаги разработчика
 */
class TaxiEnv
{
    /**
     * Корневая директория сайта, обычно на уровень выше чем Apii
     * @var string
     */
    public static $DIR_SERVER_ROOT;

    /**
     * Корневая директория с АПИ
     * @var string
     */
    public static $DIR_ROOT;

    /**
     * Директория защищаемая - /protected
     * @var string
     */
    public static $DIR_PROTECTED;

    /**
     * Временная RUNTIME директория - /runtime
     * @var string
     */
    public static $DIR_RUNTIME;

    /**
     *
     * @var boolean Флаг показа всех ошибок
     */
    public static $FLAG_DISPLAY_ERRORS = true;

    /**
     * Флаг отладки
     * @var boolean
     */
    public static $DEBUG = false;

    /**
     * Включить ли прямой вывод лога в консоль
     * @var boolean
     */
    public static $PRINT_LOG_CONSOLE_LOG = false;

    /**
     * Текущий загруженный конфиг системы
     * @var TaxiConfig
     */
    public static $config;

    /**
     * Текущий автоподгрузчик системы
     * @var TaxiAutoloader
     */
    public static $autoloader;

    /**
     * Типы ошибок - включить в сообщения
     */
    public static $errorTypes = E_ALL;

    /**
     * Подключить начальные необходимые файлы для загрузки системы
     * (безопасный сценарий подключения)
     */
    public static function includeBootstrapFiles()
    {
        $files = array(
            TaxiEnv::$DIR_PROTECTED . '/common/system/core/autoloader/TaxiAutoloader.php',
            TaxiEnv::$DIR_PROTECTED . '/common/interfaces.php',
        );
        foreach ($files as $path) {
            if (is_file($path)) {
                include_once $path;
            }
        }
    }

}

TaxiEnv::$errorTypes = E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_NOTICE;
error_reporting(TaxiEnv::$errorTypes);

TaxiEnv::$DIR_PROTECTED = dirname(__FILE__);
TaxiEnv::$DIR_ROOT = dirname(TaxiEnv::$DIR_PROTECTED);
TaxiEnv::$DIR_SERVER_ROOT = dirname(TaxiEnv::$DIR_ROOT);
TaxiEnv::$DIR_RUNTIME = TaxiEnv::$DIR_ROOT . '/runtime';


/*
 * Интерфейсы и базовые классы системы
 */
TaxiEnv::includeBootstrapFiles();

/*
 * Автоподгрузчик
 */
TaxiEnv::$autoloader = new TaxiAutoloader();

TaxiEnv::$autoloader->baseDir = TaxiEnv::$DIR_PROTECTED;
TaxiEnv::$autoloader->scanMap[] = TaxiEnv::$DIR_ROOT . '/config';
TaxiEnv::$autoloader->scanMap[] = TaxiEnv::$DIR_ROOT . '/adapters';

/*
 * Внутренний обработчик исключений, конфиг
 */
TaxiEnv::$autoloader->registerPhpAutoloader();

TaxiEnv::$autoloader->includeClass('TaxiExceptionHandler');
TaxiEnv::$autoloader->includeClass('TaxiConfig');

set_exception_handler(array('TaxiExceptionHandler', 'handle'));
//set_error_handler(array('TaxiExceptionHandler', 'handleError'), TaxiEnv::$errorTypes);

TaxiEnv::$config = new TaxiConfig();
