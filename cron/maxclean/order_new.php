<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$LOGNAME = basename(__FILE__, '.php');
$LOGFILE = CRON_LOGS_DIR . $LOGNAME . '.log';

echo '<pre>';

if (CModule::IncludeModule('sale')) {
    $date1 = date('d.m.Y H:i:s');
    $date2 = date('d.m.Y H:i:s', strtotime($date1 . ' -60 minutes'));

    $arFilter = Array(
        ">=DATE_STATUS" => $date2,
        "<=DATE_STATUS" => $date1,
        "PROPERTY_VAL_BY_CODE_Cleaner" => array("0", " "),
        "STATUS_ID" => array("A", "N")
    );
    $db_sales = CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter, false, false, Array());
    while ($ar_sales = $db_sales->Fetch()) {
        $dateOrder=$ar_sales["DATE_INSERT"];
        $dateScript=date('d.m.Y H:i:s');;
        $dateSend=date('d.m.Y H:i:s', strtotime($dateScript . ' -45 minutes'));
        if ($dateSend>=$dateOrder){
            $db_props = CSaleOrderPropsValue::GetOrderProps($ar_sales["ID"]);
            while ($arProps = $db_props->Fetch()) {
                if ($arProps["CODE"] == "TIME")
                    $time = $arProps["VALUE"];
                if ($arProps["CODE"] == "DATE")
                    $date = $arProps["VALUE"];
                if ($arProps["CODE"] == "DURATION")
                    $duration = $arProps["VALUE"];               
                if ($arProps["CODE"] == "PERSONAL_CITY")
                    $city = $arProps["VALUE"];
            }
            //----Обрамляем в наше условие
            if ($city==618){
                $cleanerCity=619;
                $managerPhone=MANAGER_PHONE_MSK;
            }
            elseif ($city==617){
                $cleanerCity=617;
                $managerPhone=MANAGER_PHONE_SPB;
            }

            $smsText="Свободный заказ: #".$ar_sales["ID"].", ".CurrencyFormat($ar_sales["PRICE"], 'RUB')."р, ".CurrencyFormat($duration*350, 'RUB')."р, ".substr($date, 0, -5)." ".$time.":00. "."Для назначения на заказ, отправьте смс ".$ar_sales["ID"]." на номер +79857707575";//Подробнее ".$managerPhone.", в личном кабинете www.maxclean.help";
            if (IS_DEV) $smsText = 'TEST ' . $smsText;

            $user_phone=array();
            $rsUsers = CUser::GetList(($by="id"), ($order="asc"), Array("GROUPS_ID" => Array(5), "ACTIVE"=>"Y", "PERSONAL_CITY"=>$cleanerCity));
            while ($arUser = $rsUsers->Fetch()){
                $user_phone[]="8".$arUser["PERSONAL_PHONE"];
            }
           // $user_phone = array(89817180789 );
            foreach ($user_phone as $arPhone){
                //sendsms($arPhone, $smsText);
            }
            //----Обрамляем в наше условие
        }
    }
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");

