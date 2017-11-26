<?
$arResult["BASKET_PRICE"]=$_SESSION["periodTotalPrice"];

$arResult["totalPrice"]=$_SESSION["totalPrice"];
$arResult["duration"]=$_SESSION["duration"];
$arResult["totalPriceDiscount"]=$_SESSION["totalPriceDiscount"];
$arResult["periodName"]=$_SESSION["periodName"];
$arResult["periodDiscountPercent"]=$_SESSION["periodDiscountPercent"];
$arResult["periodDiscount"]=$_SESSION["periodDiscount"];
$arResult["periodTotalPrice"]=number_format($_SESSION["periodTotalPrice"],0,'.',' ');

//echo "<pre>"; print_r($_SESSION); echo "</pre>";

if ($_GET["ORDER_ID"]){

	$arOrder = CSaleOrder::GetByID($_SESSION["ORDER_ID"]);



	$friends=$bonus=0;
	$db = CUser::GetList($b = "ID", $o = "DESC", array("ID"=>$USER->GetID()), array('SELECT'=>array('UF_FRIENDS', 'UF_BONUS')));
	while($sr = $db->Fetch()){
	    if(strlen($sr['UF_FRIENDS']))
	        $friends = $sr['UF_FRIENDS'];
	    if(strlen($sr['UF_BONUS']))
	        $bonus = $sr['UF_BONUS'];
	}


    if (!$_SESSION["MNOGORU"]){

        //actionpay
        if ($_COOKIE['actionpay']) {
            if ($_COOKIE['actionPayFirstCome']){
                $picId =12766;
                setcookie("actionPayFirstCome", "", strtotime('+30 day'), '/');
            }
            else
                $picId =12767;

            $arResult['actionPayImgPath'] = '//apypx.com/ok/' . $picId . '.png?actionpay=' . $_COOKIE['actionpay'] . '&apid=' . $arOrder['ID'] . '&price=' . $arOrder['PRICE'];
            $USER->Update($USER->GetID(), array('UF_ACTIONPAY_ID' => "Да"));

            $dbOrderProperties = CSaleOrderProps::GetList(
                array(),
                array("PERSON_TYPE_ID" => 1, "ACTIVE" => "Y", "CODE" => "ACTIONPAY"),
                false,
                false,
                array("ID", "NAME", "CODE")
            )->fetch();

    		//CSaleOrderPropsValue::Add(array(
            \Bitrix\Sale\Internals\OrderPropsValueTable::Add(array(
                "ORDER_ID" => $_GET["ORDER_ID"],
                "ORDER_PROPS_ID" => $dbOrderProperties["ID"],
                "NAME" => $dbOrderProperties["NAME"],
                "CODE" => $dbOrderProperties["CODE"],
                "VALUE" => $picId.".".$_COOKIE['actionpay']
            ));
        }else
            $arResult['actionPayImgPath'] = false;
        if ($arResult['actionPayImgPath'])
            echo '<img src="'.$arResult["actionPayImgPath"].'" height="1" width="1" style="display:none;">';


        //advertise
        if ($_COOKIE['adv_uid']){
            $dbOrderProperties = CSaleOrderProps::GetList(
                array(),
                array("PERSON_TYPE_ID" => 1, "ACTIVE" => "Y", "CODE" => "ADVERTISE"),
                false,
                false,
                array("ID", "NAME", "CODE")
            )->fetch();

    		//CSaleOrderPropsValue::Add(array(
            \Bitrix\Sale\Internals\OrderPropsValueTable::Add(array(
                "ORDER_ID" => $_GET["ORDER_ID"],
                "ORDER_PROPS_ID" => $dbOrderProperties["ID"],
                "NAME" => $dbOrderProperties["NAME"],
                "CODE" => $dbOrderProperties["CODE"],
                "VALUE" => $_COOKIE['adv_uid']
            ));


            $advertise_tracking = '922113f70b03426b';
            $advertise_uid = $_COOKIE['adv_uid'];
            $arResult['advertiseImgPath'] = '//advertiseru.net/tracking/'.$advertise_tracking.'/img/?uid='.$advertise_uid.'&order_id='.$arOrder['ID'].'&client_id='.$arOrder["USER_ID"].'&amount='.$arOrder['PRICE'];
            setcookie("adv_uid", "", strtotime('+30 day'), '/');
        }else
            $arResult['advertiseImgPath'] = false;
        if ($arResult['advertiseImgPath'])
            echo '<img src="'.$arResult["advertiseImgPath"].'" height="1" width="1"  alt="">';

        //admitad
        if ($_COOKIE['admitad_uid']){
            $db = CUser::GetList($b = "ID", $o = "DESC", array("ID"=>$USER->GetID()), array('SELECT'=>array('UF_ADMITAD_UID')));
            while($admitadUser = $db->Fetch()){
                if($admitadUser['UF_ADMITAD_UID'])
                    $action_code=2;
                else
                    $action_code=1;
            }
            $USER->Update($USER->GetID(), array('UF_ADMITAD_UID' => $_COOKIE['admitad_uid']));
            $url = 'https://ad.admitad.com/r?campaign_code=07ab7897dc&postback=1&postback_key=D49B85ce72207cCB6cC70b7405384b21&action_code='.$action_code.'&uid='.$_COOKIE['admitad_uid'].'&order_id='.$arOrder['ID'].'&tariff_code=1&currency_code=RUB&price='.$arOrder['PRICE'].'&quantity=1&position_id=&position_count=&product_id=&client_id=&payment_type=sale';
            file_get_contents($url);
        }




    }


    if ($_SESSION["SALE_COUPON_UTM"])
        unset($_SESSION["SALE_COUPON_UTM"]);
    unset($_SESSION["confirmPhone"]);
    unset($_SESSION["period"]);
    unset($_SESSION["checkCode"]);
    unset($_SESSION["ORDER_ID"]);
    unset($_SESSION["ORDER_PROP_PERSONAL_STREET"]);
    unset($_SESSION["ORDER_PROP_TIME"]);
    unset($_SESSION["ORDER_PROP_PERSONAL_PHONE"]);
    unset($_SESSION["ORDER_PROP_USER_LOGIN"]);
    unset($_SESSION["ORDER_PROP_PERSONAL_CITY"]);
    unset($_SESSION["ORDER_PROP_DATE"]);
    unset($_SESSION["ORDER_NAME"]);
    unset($_SESSION["ORDER_PROP_NAME"]);
    unset($_SESSION["periodDiscount"]);
    unset($_SESSION["periodTotalPrice"]);
    unset($_SESSION["periodName"]);
    unset($_SESSION["DURATION"]);
    unset($_SESSION["SALE_USER_ID"]);
}


$arResult["bonus"]=$bonus;
$arResult["friends"]=$friends;
$arResult["user_id"]=$USER->GetID();

$urlVk = FULL_SERVER_NAME . '/?ref_inviter='.$USER->GetID();
$urlFb = FULL_SERVER_NAME . '/?ref_inviter='.$USER->GetID();
$urlVk = urlencode($urlVk);
$urlFb = urlencode($urlFb);

$arResult["urlVk"]=$urlVk;
$arResult["urlFb"]=$urlFb;
$arResult["SHARE_TITLE"]=SHARE_TITLE;
$arResult["SHARE_DESCRIPTION"]=SHARE_DESCRIPTION;
$arResult["FULL_SERVER_NAME"]=FULL_SERVER_NAME;
?>