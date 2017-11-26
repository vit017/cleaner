<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?
if (CModule::IncludeModule('sale')){
    $user_array=array();
    $rsUsers = CUser::GetList(($by="id"), ($order="asc"), Array("GROUPS_ID" => Array(5), "ACTIVE"=>"Y"));
    while ($arUser = $rsUsers->Fetch()){
        $user_array[]=$arUser["ID"];
        $user_phone[]=$arUser["PERSONAL_PHONE"];
    }

    foreach($user_array as $userId=>$User){
        $smsText="Заказы на завтра: ";
        $sms=$smsSend=array();
        $i=0;
        $arFilter=Array('PROPERTY_VAL_BY_CODE_DATE' => date('d.m.Y', time() + 86400), "CANCELED"=>"N", "PROPERTY_VAL_BY_CODE_Cleaner"=>$User);
        $db_sales = CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter, false, false, Array());
        while ($ar_sales = $db_sales->Fetch()) {
            $arPaySys = CSalePaySystem::GetByID($ar_sales["PAY_SYSTEM_ID"]);
            $sms[$i]["PAYSYSTEM"]=$arPaySys["NAME"];
            $sms[$i]["ID"]=$ar_sales["ID"];
            $sms[$i]["PRICE"]=$ar_sales["PRICE"];
            $db_props = CSaleOrderPropsValue::GetOrderProps($ar_sales["ID"]);
            while ($arProps = $db_props->Fetch()) {
                $sms[$i][$arProps["CODE"]]=$arProps["VALUE"];
            }
            $i++;
        }

        foreach ($sms as $id=>$smsItem){
            $idNew=$id+1;
            $cleanerPrice=$smsItem["DURATION"]*350;
            if ($smsItem["PAYSYSTEM"]=="Наличными")
                $paysystem="Нал";
            else
                $paysystem=$smsItem["PAYSYSTEM"];

            $smsText.=$idNew.". №".$smsItem["ID"]." ".$smsItem["TIME"].", ".$smsItem["DURATION"].", ".$paysystem.", ".$smsItem["PRICE"]."р, ".$cleanerPrice."р. ";
        }

        if ($smsText=="Заказы на завтра: ")
            $smsText="На завтра нет заказов, приятного отдыха.";

        file_get_contents('https://intra.becar.ru/f8/spservice/request.php?xml=&dima-phone=8'.$user_phone[$userId].'&messagebody='.$smsText.'&MaxClean=');
    }
}
?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
