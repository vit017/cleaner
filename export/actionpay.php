<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("sale");
CModule::IncludeModule("iblock");

if ($_FILES['xml']){
    $_POST['xml'] = file_get_contents($_FILES['xml']['tmp_name']);
}

if ($_POST['xml'] || $_POST['date']){
    $arFilter = array();
    if ($_POST['xml']){
        $xml = simplexml_load_string($_POST['xml']);
        $OIDS = array();
        if ($xml) foreach ($xml->item as $orderId) {
            $OIDS[] = (int)$orderId;
        }
        $arFilter['ID'] = $OIDS;
    }elseif ($_POST['date']){
        $arFilter['>=DATE_INSERT'] = date('d.m.Y', strtotime($_POST['date'])) . ' 00:00:00';
        $arFilter['<=DATE_INSERT'] = date('d.m.Y', strtotime($_POST['date'])) . ' 23:59:59';
    }


    if ($arFilter['ID'] || $_POST['date']){
        $arFilter['!PROPERTY_VAL_BY_CODE_ACTIONPAY'] = "";
        $ORDERSres = CSaleOrder::GetList(array(), $arFilter, false, false, array("PROPERTY_VAL_BY_CODE_ACTIONPAY", "ID", "PRICE", "DATE_STATUS", "STATUS_ID"));
        $ORDERS = array();

        while ($order = $ORDERSres->fetch()){
            $orderNew["apid"]=$order["ID"];
            $actionPayArray = explode(".", $order["PROPERTY_VAL_BY_CODE_ACTIONPAY"]);
            $orderNew['aim'] = trim($actionPayArray[0]);
            $orderNew['click'] = trim($actionPayArray[1]);
            $orderNew['source'] = trim($actionPayArray[2]);
            $orderNew['date'] = $order["DATE_STATUS"];
            $orderNew['price'] = $order["PRICE"];

            if ($order["STATUS_ID"]=="A" || $order["STATUS_ID"]=="N")
                $orderNew['status'] = 2;
            elseif ($order["STATUS_ID"]=="F")
                $orderNew['status'] = 1;
            elseif ($order["STATUS_ID"]=="C" || $order["STATUS_ID"]=="M")
                $orderNew['status'] = 3;            

            $ORDERS[] = $orderNew;
        }
    }
}
?>


<?if ($ORDERS){
    echo '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
    ?>
      
    <items>
        <? foreach ($ORDERS as $order){?>
            <item>
                <id><?=$order['apid'];?></id>
                <click><?=$order['click'];?></click>
                <source><?=$order['source'];?></source>
                <price><?=$order['price'];?></price>
                <status><?=$order['status'];?></status>
                <date><?=$order['date'];?></date>
                <aim><?=$order['aim'];?></aim>
            </item>
        <?}?>

    </items>
<?}?>
    
