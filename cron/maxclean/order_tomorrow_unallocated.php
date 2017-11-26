<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?
if (CModule::IncludeModule('sale')){
    $smsTextSPb="Не распределены заказ/ы: ";
    $smsTextMsk="Не распределены заказ/ы: ";

    $arFilter=Array('PROPERTY_VAL_BY_CODE_DATE' =>  date('d.m.Y', time() + 86400), "CANCELED"=>"N", "PROPERTY_VAL_BY_CODE_Cleaner"=>"0");
    $db_sales = CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter, false, false, Array());
    while ($ar_sales = $db_sales->Fetch()) {
        $db_props = CSaleOrderPropsValue::GetOrderProps($ar_sales["ID"]);
        while ($arProps = $db_props->Fetch()) {
            if ($arProps["CODE"]=="TIME")
                $time=$arProps["VALUE"];
            if ($arProps["CODE"]=="DATE")
                $date=substr($arProps["VALUE"], 0, -5);
            if ($arProps["CODE"]=="PERSONAL_CITY")
                $city=$arProps["VALUE"];
        }

        if ($city==618)
            $smsTextMsk.="№".$ar_sales["ID"]." на ".$date." ".$time."; ";
        elseif ($city==617)
            $smsTextSPb.="№".$ar_sales["ID"]." на ".$date." ".$time."; ";
    }

    $smsTextSPb = substr($smsTextSPb,0,-2);
    $smsTextSPb.=".";

    $smsTextMsk = substr($smsTextMsk,0,-2);
    $smsTextMsk.=".";

    if ($smsTextMsk!="Не распределены заказ/ы."){
        sendsms(MANAGER_PHONE_MSK, $smsTextMsk);
        sendsms(MANAGER_PHONE_MSK_TEST, $smsTextMsk);
    }


    if ($smsTextSPb!="Не распределены заказ/ы."){
        sendsms(MANAGER_PHONE, $smsTextSPb);
        sendsms(MANAGER_PHONE_SPB_TEST, $smsTextSPb);
    }

}
?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
