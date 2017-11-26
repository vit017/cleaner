<?php

return array(
    'gootax_test' => array(
        'type'    => 'gootax',
        'options' => array(
            'label'               => 'gootax Kabbi',
            'ip'                  => 'ca2.kabbi.eu',
            'port'                => '8089',
			'key'                 => 'm4c4icm4ih4mxhro42mr34r#$V#%Tb4y46by5njm6',
			'iphoneHackRegistrationTel' => '79000000000',
            'callTarifsFromAPI'   => true,
            'configuratorClass'   => 'Gootax_custom',
			'useSmsAuthorization' => true,
			'tenantid'            => 2538,
            'appid'               => 1629,
			'costCurrency'        => '€',
            'dbHost'              => 'localhost',
            'dbLogin'             => '',
            'dbPass'              => '' ,
            'database'            => '',
        ),
    ),
);