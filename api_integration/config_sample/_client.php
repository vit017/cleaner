<?php

return array(
    'servers' => array(
        'local' => array(
            'host' => 'http://megataxi.pro/api_integration/index_server.php',
            'secretKey' => '@@ уникальный ключ между нашим сервером-клиентом -- weuI27SdsD1dI1PXd @@',
        ),
    ),
    'defaultAdapterKey' => '@@ идентификатор подключения желательно - tm_site_ru @@',
    'adaptersMap' => array(

    ),
    // Параметры клиента
    'options' => array(
        'needWriteToBitrix' => true,

    ),
);
