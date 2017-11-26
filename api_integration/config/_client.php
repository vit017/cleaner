<?php

return array(
    'servers' => array(
        'local' => array(          
            'host' => 'http://uat.taxi3c.ru/hochutaxi/index_server.php',
            'secretKey' => 'TPkCV',
        ),
    ),
    'defaultAdapterKey' => 'gootax_test',
    'adaptersMap' => array(
		//'gootax_test'  => 'remote',
    ),
    // Параметры клиента
    'options' => array(
        'needWriteToBitrix'=> true
    ),
);
