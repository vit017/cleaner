<?php

return array(
    '@@ идентификатор подключения желательно - tm_site_ru @@' => array(
        'type'    => 'tmNew',
        'options' => array(
            'label'                        => 'Москва Серое такси - http://site.ru',
            'defaultCity'                  => 'Москва',
            'useSmsAuthorization'          => true,
            'ip'                           => '@@ IP удаленной службы - host1.t777.ru @@',
            'port'                         => '@@ порт - 8089 @@',
            'apiKey'                       => '@@ ключ - kDc_!dMM @@',
            'apiKeyTmt'                    => '',
            'costCurrency'                 => 'руб.',
            'callTarifsFromAPI'            => true,
            'configuratorClass'            => null,
            'timeZone'                     => 0,
            'createOrderStateId'           => '@@ Id статуса нового заказа @@',
            'rejectOrderStateIdWithCar'    => '@@ Id статуса отмены заказа с назначенным авто@@',
            'rejectOrderStateIdWithoutCar' => '@@ Id статуса отмены заказа без авто  @@',
        ),
    ),
);

