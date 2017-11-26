<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$LOGNAME = basename(__FILE__, '.php');
$LOGFILE = CRON_LOGS_DIR . $LOGNAME . '.log';

echo '<pre>';

if (CModule::IncludeModule('sale')) {
   /* $date1 = date('d.m.Y H:i:s');
    $date2 = date('d.m.Y H:i:s', strtotime($date1 . ' -60 minutes'));*/

    //берём клинёров, которые не могут работать
    $no_worker=[];
    $arS = Array("ID", "PROPERTY_61", "PROPERTY_62");
    $arF = Array("IBLOCK_ID"=>10, "ACTIVE"=>"Y");
    $res = CIBlockElement::GetList(Array(), $arF, false, Array(), $arS);
    while($ob = $res->GetNext(false,false)){
        $datesArr=explode(',',$ob['PROPERTY_62_VALUE']);
        foreach($datesArr as $v){
            $no_worker[$v]=$ob['PROPERTY_61_VALUE'];
        }
    }

    $arFilter = Array(

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

                $date_1 = date('d.m.Y H:i:s');
                $date_2 = date('d.m.Y H:i:s', strtotime($date_1 . ' +2880 minutes'));
                $date_3 = date('d.m.Y H:i:s', strtotime($date_1 . ' +2820 minutes'));
            }
            $data_order = $date." ".$time.":00";
            $data_order = date('d.m.Y H:i:s', strtotime($data_order));
            //----Обрамляем в наше условие
            if ( (strtotime($data_order)<=strtotime($date_2)) &&(strtotime($data_order)>strtotime($date_3)) )
            {
                if((8<=date("H"))&&(date("H")<=22))
                {
                    if ($city == 618) {
                        $cleanerCity = 619;
                        $managerPhone = MANAGER_PHONE_MSK;
                    } elseif ($city == 617) {
                        $cleanerCity = 617;
                        $managerPhone = MANAGER_PHONE_SPB;
                    }

                    $smsText = "Свободный заказ: #" . $ar_sales["ID"] . ", " . CurrencyFormat($ar_sales["PRICE"], 'RUB') . "р, " . CurrencyFormat($duration * 350, 'RUB') . "р, " . substr($date, 0, -5) . " " . $time . ":00. " . "Для назначения на заказ, отправьте смс " . $ar_sales["ID"] . " на номер +79857707575";//Подробнее ".$managerPhone.", в личном кабинете www.maxclean.help";
                    if (IS_DEV) $smsText = 'TEST ' . $smsText;

                    $user_phone = array();
                    $rsUsers = CUser::GetList(($by = "id"), ($order = "asc"), Array("GROUPS_ID" => Array(5), "ACTIVE" => "Y", "PERSONAL_CITY" => $cleanerCity));
                    while ($arUser = $rsUsers->Fetch()) {
                        //если клинёр не хочет работать в этот день, то не отсылаем ему sms
                        if(isset($no_worker[$date][$arUser["ID"]])){
                            continue;
                        }
                        //Начинаема пиздец
                        $iskArr = [];
                        $arFilter_mod = Array(
                            "PROPERTY_VAL_BY_CODE_Cleaner" => array($arUser["ID"])
                        );

                        $db_sales_mod = CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter_mod, false, false, Array());

                        while ($ar_sales_mod = $db_sales_mod->Fetch()) {
                            $db_props_mod = CSaleOrderPropsValue::GetOrderProps($ar_sales_mod["ID"]);

                            while ($arProps_mod = $db_props_mod->Fetch()) {
                                // print_r($arProps_mod);
                                if ($arProps_mod["CODE"] == "TIME")
                                    $time_mod = $arProps_mod["VALUE"];
                                if ($arProps_mod["CODE"] == "DATE")
                                    $date_mod = $arProps_mod["VALUE"];
                            }
                            $summdate = $date_mod." ".$time_mod;
                            $data_order_mod = substr($data_order, 0,10);
                            if($data_order_mod==$date_mod)
                            {
                                if(((($summdate[11].$summdate[12])-2) < ($data_order[11].$data_order[12])) && ((($summdate[11].$summdate[12])+3) > ($data_order[11].$data_order[12])))
                                {
                                    $iskArr[] = $arUser["PERSONAL_PHONE"]; //Записываем номера в исключения
                                }
                            }

                            echo '<br />';
                        }
                        //Заканчиваем пиздец

                        if(in_array($arUser["PERSONAL_PHONE"],$iskArr))
                        {
                        } else {
                            $user_phone[] = "8" . $arUser["PERSONAL_PHONE"];
                        }
                    }
                   // print_r($user_phone);
                   // $user_phone = array(89817180789, 89119483239);
                    foreach ($user_phone as $arPhone) {
                         print_r($arPhone);
                        sendsms($arPhone, $smsText);
                    }
                }
                    else
                {
                   //---
                }
            }
            {
                //---
            }
            //----Обрамляем в наше условие
        }
    }
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");

