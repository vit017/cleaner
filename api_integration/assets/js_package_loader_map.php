<?php

/*
 * ! Карта подгрузки JS файлов и библиотек
 */
return array(
    /*
     * Для работы с АПИ используем путь относительно корня сайта,
     * для других же будет использовать путь относительно темы
     */
    '/api_integration/assets/js/lib/TaxiDataStore.js',
    '/api_integration/assets/js/components/TaxiCustomCarComponent.js',
    '/api_integration/assets/js/components/TaxiRouteComponent.js',
    '/api_integration/assets/js/components/cost/TaxiCost.js',
    '/api_integration/assets/js/TaxiMethod_createOrder.js',
    '/api_integration/assets/js/TaxiOrderData.js',
    '/api_integration/assets/js/TaxiErrorsInfo.js',
    '/api_integration/assets/js/TaxiClient.js',
    '/api_integration/assets/js/TaxiMethod.js',
    '/api_integration/assets/js/TaxiBitrixModalWindow.js',
    '/api_integration/assets/js/TaxiOrderProcess.js',
    '/api_integration/assets/js/bitrix/TaxiBitrixOrderProcess.js',
    /*
     * Сторонние библиотеки
     */
    '/api_integration/assets/js/bitrix/taxi/vendors/mobileLib.js',
    /*
     * Базовые объекты
     */
    '/api_integration/assets/js/bitrix/taxi/system/baseObject.js',
    /*
     * Карты
     */
    '/api_integration/assets/js/bitrix/taxi/maps/addYandexMap.js',
    '/api_integration/assets/js/bitrix/taxi/maps/addGoogleMap.js',
    '/api_integration/assets/js/bitrix/taxi/maps/addYandexGeocoder.js',
    '/api_integration/assets/js/bitrix/taxi/maps/addGoogleGeocoder.js',
    /*
     * Библиотека
     */
    '/api_integration/assets/js/bitrix/taxi/lib/YandexSuggestCaller.js',
    '/api_integration/assets/js/bitrix/taxi/lib/TaxiSuggestCaller.js',
    /*
     * Основное
     */
   '/api_integration/assets/js/bitrix/top-panel.js',
    '/api_integration/assets/js/bitrix/other.js',
    '/api_integration/assets/js/bitrix/taxi_init.js',
    /*
     * Библиотечные скрипты:
     */
    '<SITE_TEMPLATE_PATH>/js/ru/colorbox/jquery.colorbox-min.js',
    '<SITE_TEMPLATE_PATH>/js/ru/jquery.maskedinput.min.js',
    '<SITE_TEMPLATE_PATH>/bootstrap/js/bootstrap.min.js',
    '<SITE_TEMPLATE_PATH>/bootstrap/js/bootstrap-tab.js',
    '<SITE_TEMPLATE_PATH>/bootstrap/js/html5shiv.js',
);
