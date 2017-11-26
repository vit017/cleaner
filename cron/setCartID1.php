<?php
//*/5 * * * * /usr/local/bin/php -q $HOME/gettidy.ru/www/cron/setCartID.php SERVER_NAME=gettidy.ru > /dev/null 2>&1 --delete-after
//*/5 * * * * /usr/local/bin/php -q $HOME/cleanandaway.ru/www/cron/setCartID.php SERVER_NAME=cleanandaway.ru > /dev/null 2>&1 --delete-after
//*/5 * * * * /usr/local/bin/php -q $HOME/dev.gettidy.ru/www/cron/setCartID.php SERVER_NAME=dev.gettidy.ru > /dev/null 2>&1 --delete-after
//*/5 * * * * /usr/local/bin/php -q $HOME/dev2.gettidy.ru/www/cron/setCartID.php SERVER_NAME=dev2.gettidy.ru > /dev/null 2>&1 --delete-after
die('dd');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$tmlLine = '';

if (CModule::IncludeModule('sale')){
    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/xml.php");
    $arOrders = array();
    $db = CSaleOrder::getList(array('ID'=>'ASC'), array('PAY_SYSTEM_ID'=>2, '!PROPERTY_VAL_BY_CODE_CardNumber'=>'', 'CANCELED' => 'N'));
    echo '++';
    while ($arOrder = $db->fetch()){
        xmp($arOrder);
        $orderID = $arOrder['ID'];
        $nextOrder = false;
        $cardNumber = false;
        $dbOrderProp = CSaleOrderPropsValue::GetList(Array(), Array("ORDER_ID" => $orderID));
        while ($arOrderProp = $dbOrderProp->Fetch()){
            switch ($arOrderProp['CODE']){
                case 'CardNumber':
                    $cardNumber = trim($arOrderProp['VALUE']);
                    break;
                case 'GET_CARDID':
                    if(strlen(trim($arOrderProp['VALUE']))>0){
                        $nextOrder = true;
                    }
                    break;
                case 'CardId':
                    if(strlen(trim($arOrderProp['VALUE']))>0){
                        $nextOrder = true;
                    }
                    break;
            }
        }
        if ($nextOrder){
            continue;
        }

        if ($cardNumber) {

            $cardID = bhPayture::getCardIDbyNumber($orderID, $cardNumber);

            if ($cardID) {
                $db_vals = CSaleOrderPropsValue::GetList(
                    array("SORT" => "ASC"),
                    array(
                        "ORDER_ID" => $orderID,
                        'CODE' => 'CardId'
                    )
                );
                if ($arVals = $db_vals->Fetch()) {
                    CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE" => $cardID));
                    $tmlLine = $orderID . "\n";
                } else {
                    $db_props = CSaleOrderProps::GetList(
                        array("SORT" => "ASC"),
                        array(
                            "PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"],
                            'CODE' => 'CardId'
                        )
                    );
                    if ($arProps = $db_props->Fetch()) {
                        $arFields = array(
                            "ORDER_ID" => $orderID,
                            "ORDER_PROPS_ID" => $arProps["ID"],
                            "NAME" => $arProps["NAME"],
                            "CODE" => $arProps["CODE"],
                            "VALUE" => $cardID
                        );
                        CSaleOrderPropsValue::Add($arFields);
                        $tmlLine = $orderID . "\n";
                    }
                }
            }
        } else {
            $db_props = CSaleOrderProps::GetList(
                array("SORT" => "ASC"),
                array(
                    "PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"],
                    'CODE' => 'GET_CARDID'
                )
            );
            if ($arProps = $db_props->Fetch()) {
                $arFields = array(
                    "ORDER_ID" => $orderID,
                    "ORDER_PROPS_ID" => $arProps["ID"],
                    "NAME" => $arProps["NAME"],
                    "CODE" => $arProps["CODE"],
                    "VALUE" => 'N'
                );
                CSaleOrderPropsValue::Add($arFields);
                $tmlLine = $orderID . "\n";
            }
        }
    }
    if (strlen($tmlLine)>0){
        if ($_GET['debug']){
            echo 'Orders:<br>';
            echo $tmlLine;
        } else
            file_put_contents($_SERVER["DOCUMENT_ROOT"].'/cron/logs/orders_done.txt', $tmlLine, FILE_APPEND);
    }
}