<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 29.07.14
 * Time: 14:43
 */

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("LANG", "s1");
foreach ($argv as $arg) {
    $e=explode("=",$arg);
    if (count($e)==2)
        $_GET[$e[0]]=$e[1];
    else
        $_GET[$e[0]]=0;
}
if ($_GET['SERVER_NAME']){
    $_SERVER['SERVER_NAME'] = $_GET['SERVER_NAME'];
}

if ( !isset($_SERVER['DOCUMENT_ROOT']) || $_SERVER['DOCUMENT_ROOT'] == '' ){
    $_SERVER['DOCUMENT_ROOT'] = '/home/u429586/'.$_SERVER['SERVER_NAME'].'/www/';
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

function sayThanks($date = false)
{
    CModule::IncludeModule('sale');
    $today = new DateTime();
    $todayStamp = $today->format('d.m.Y H:i:s');
    if ($date){
        $yesterday = new DateTime($date);
    }else{
        $yesterday = new DateTime('-1days');
    }
    $yesterday = $yesterday->format('d.m.Y');
    $arFilter = Array(
        "LID" => 's1',
        "CANCELED" => "N",
        "STATUS_ID" => "F",
        'PROPERTY_VAL_BY_CODE_DATE' =>$yesterday
    );

    if($_GET['debug']){
        echo 'Filter:';
        xmp($arFilter);
    }
    $excludeIds = array();

    $dbOrder = CSaleOrder::GetList(Array("ID" => "DESC"), $arFilter, false, false, Array("ID", "USER_ID"));
    $arOld = array();

    $arOrders = array();
    while ($arOrder = $dbOrder -> GetNext()) {
        $arOrders[$arOrder["ID"]] = $arOrder["USER_ID"];
    }

    if (empty($arOrders)) return;

    $db_sales = CSaleOrder::GetList(array("ID"=>"ASC"), array("LID" => 's1', "CANCELED" => "N", "STATUS_ID" => "F", "USER_ID"=>$arOrders));
    while ($ar_sales = $db_sales->Fetch()){
        $arOld[$ar_sales['USER_ID']][] = $ar_sales['ID'];
    }

    if (empty($arOld)) return;

    $arUsers = array();
    $dbUser = CUser::getList(($by="ID"), ($order="asc"), array("ID" => array_keys($arOld)));
    while ($arUser = $dbUser->fetch()){
        $arUsers[$arUser['ID']] = array(
            "NAME" => $arUser["NAME"],
            "EMAIL" => $arUser["EMAIL"]
        );
    }
    foreach ($arOld as $user_id=>$orders){
        if(count($orders) == 1){
            $orderID = array_shift($orders);
            //payer
            $bSend = false;
            $arUser = $arUsers[$user_id];
            if (!empty($arUser)) {
                $arFields = Array(
                    "ORDER_ID" => $orderID,
                    "NAME" => $arUser["NAME"],
                    "EMAIL" => $arUser["EMAIL"]
                );

                if (!in_array($orderID, $excludeIds))
                    $bSend = true;
                if ($bSend) {
                    if ($_GET['debug']) {
                        echo $orderID;
                        xmp(array(
                            //'subject'=>$_SERVER['SERVER_NAME'].': Новый заказ N'.$arOrder['ID'],
                            'to' => array(
                                array(
                                    'email' => $arFields['EMAIL'],
                                    'name' => $arFields['NAME'],
                                    'type' => 'to'
                                )
                            ),
                            'global_merge_vars' => array(
                                array('name' => 'SERVER_NAME', 'content' => $_SERVER['SERVER_NAME']),
                                array('name' => 'NAME', 'content' => $arFields['NAME']),
                                array('name' => 'SITE_NAME', 'content' => $_SERVER['SERVER_NAME'])),
                            'merge' => 'Y'));
                    }
                    else {
                        $token = bhSettings::$mandrillKey;
                        $mandrill = new Mandrill($token);

                        $mandrill->messages->sendTemplate(
                            'gettidy',
                            array(),
                            array(
                                'to' => array(
                                    array(
                                        'email' => $arFields['EMAIL'],
                                        'name' => $arFields['NAME'],
                                        'type' => 'to'
                                    )
                                ),
                                'global_merge_vars' => array(
                                    array('name' => 'SERVER_NAME', 'content' => $_SERVER['SERVER_NAME']),
                                    array('name' => 'NAME', 'content' => $arFields['NAME']),
                                    array('name' => 'SITE_NAME', 'content' => $_SERVER['SERVER_NAME'])),
                                'merge' => 'Y')
                        );
                        file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/cron/logs/mailThanks_log.txt', $todayStamp . '-' . $orderID . "\n", FILE_APPEND);
                    }
                    $excludeIds[] = $orderID;
                }
            }
        }
    }
}
$date = false;
if ($_REQUEST['date']){
    $date = trim($_REQUEST['date']);
}
sayThanks($date);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
