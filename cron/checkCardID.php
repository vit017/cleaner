<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 12.11.14
 * Time: 16:33
 */
//*/30 * * * * /usr/local/bin/php -q $HOME/gettidy.ru/www/cron/checkCartID.php SERVER_NAME=gettidy.ru > /dev/null 2>&1 --delete-after
//*/30 * * * * /usr/local/bin/php -q $HOME/cleanandaway.ru/www/cron/checkCartID.php SERVER_NAME=cleanandaway.ru > /dev/null 2>&1 --delete-after
//*/30 * * * * /usr/local/bin/php -q $HOME/dev.gettidy.ru/www/cron/checkCartID.php SERVER_NAME=dev.gettidy.ru > /dev/null 2>&1 --delete-after
//*/30 * * * * /usr/local/bin/php -q $HOME/dev2.gettidy.ru/www/cron/checkCartID.php SERVER_NAME=dev2.gettidy.ru > /dev/null 2>&1 --delete-after

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (CModule::IncludeModule('sale')){

    $date_1 = new DateTime('-45 minutes');
    $date_2 = new DateTime('-15 minutes');

    $arOrders = array();
    $db = CSaleOrder::getList(array('ID'=>'ASC'),
        array(
            'DATE_FROM' => $date_1->format('d.m.Y H:i'),
            'DATE_TO' => $date_2->format('d.m.Y H:i'),
            'PAY_SYSTEM_ID' => 2,
            'CANCELED' => 'N')
    );
    while ($arOrder = $db->fetch()){
        $IDs[] = $arOrder['ID'];
        $arOrders[$arOrder['ID']] = $arOrder;
    }

    if ( empty($IDs) )
        return false;

    $dbOrderProp = bhOrder::getProps($IDs);
    foreach ($dbOrderProp as $id => $arFields) {
        $inform = false;
        if ( !isset($arFields['CardId']) || $arFields['CardId']['VALUE'] == '' ) {
            $inform = true;
        }
        if ( isset($arFields['CardNumber']) && $arFields['CardNumber']['VALUE'] != '' ) {
            $inform = false;
        }

        if ( !$inform ) {
            $eventName = 'SALE_ORDER_WITHOUT_CARD_ID';
            $order = $arOrders[$id];
            $arFieldsLetter = array(
                'ORDER_ID' => $id,
                'LOGIN' => $order['USER_LOGIN'],
                'USER_ID' => $order['USER_ID'],
            );

            if ( $_GET['debug'] ) {
                xmp($arFieldsLetter);
            }
            else {
                $event = new CEvent;
                $event->Send($eventName, SITE_ID, $arFieldsLetter, "N");
                file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/cron/logs/checkCardID.txt', $id, FILE_APPEND);
            }
        }
    }
}