<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("sale");
CModule::IncludeModule("iblock");


if ($_GET['order_ids'] || $_GET['date'] || ($_GET['date_start'] && $_GET['date_end'])){
    $arFilter = array();
    if ($_GET['order_ids']){
        foreach ($_GET['order_ids'] as $orderId)
            $arFilter['ID'][] = (int)$orderId;
    }elseif ($_GET['date']){
        $arFilter['>=DATE_INSERT'] = date('d.m.Y', strtotime($_GET['date'])) . ' 00:00:00';
        $arFilter['<=DATE_INSERT'] = date('d.m.Y', strtotime($_GET['date'])) . ' 23:59:59';
    }elseif ($_GET['date_start'] && $_GET['date_end']){
        $arFilter['>=DATE_INSERT'] = date('d.m.Y', strtotime($_GET['date_start'])) . ' 00:00:00';
        $arFilter['<=DATE_INSERT'] = date('d.m.Y', strtotime($_GET['date_end'])) . ' 23:59:59';
    }
    $arFilter['!PROPERTY_VAL_BY_CODE_ADVERTISE'] = "";

    $ORDERSres = CSaleOrder::GetList(array(), $arFilter, false, false, array("PROPERTY_VAL_BY_CODE_ADVERTISE", "ID", "PRICE", "DATE_STATUS", "STATUS_ID", "USER_ID", "REASON_CANCELED"));
    $ORDERS = array();
    while ($order = $ORDERSres->fetch()){
        $orderNew["id"]=$order["PROPERTY_VAL_BY_CODE_ADVERTISE"];
        $orderNew["order_id"]=$order["ID"];
        $orderNew["comment"]=$order["REASON_CANCELED"];
        $orderNew["user_id"]=$order["USER_ID"];
        $orderNew['date'] = $order["DATE_STATUS"];
        $orderNew['amount'] = $order["PRICE"];
        if ($order["STATUS_ID"]=="A" || $order["STATUS_ID"]=="N")
            $orderNew['status']=2;
        elseif ($order["STATUS_ID"]=="F")
            $orderNew['status']=1;
        elseif ($order["STATUS_ID"]=="C" || $order["STATUS_ID"]=="M")
            $orderNew['status']=3;
        else
            $orderNew['status']=4;
        $ORDERS[] = $orderNew;
    }
}


if ($ORDERS){
    echo '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
    ?> 
    <items>
        <? foreach ($ORDERS as $order){?>
            <item>
                <id><?=$order['id'];?></id>
                <user_id><?=$order['user_id'];?></user_id>
                <order_id><?=$order['order_id'];?></order_id>
                <status><?=$order['status'];?></status>
                <amount><?=$order['amount'];?></amount>
                <payout>900</payout>
                <date><?=$order['date'];?></date>
                <comment><?=$order['comment'];?></comment>
            </item>
        <?}?>
    </items>
<?}?>
    
