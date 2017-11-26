<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 23.05.14
 * Time: 14:24
 */


$arResult["BASKET_PRICE"]=$_SESSION["periodTotalPrice"];

if($arParams["SHOW_MENU"] == "Y")
{
    $arMenu = array();

    if ($arResult["CurrentStep"] < 6){
        $arMenuLine = array(
            1 => "Дата и время",
            2 => "Ваши данные",
            3 => "",
            4 => "Проверка заказа",
            5 => "Оплата",

        );
        array_unshift($arMenuLine, 'Площадь квартиры');
        for ($i = 0; $i < count($arMenuLine); $i++)
        {
            if($i == 3) continue;
            $arMenu[$i] = array(
                "NAME" => $arMenuLine[$i],
                "URL" => $i>0?'':'/order/basket/',
                "ID" => $i>0?$i-1:'',
            );
            $step = floor($arResult["CurrentStep"]);
	        if(strlen($arResult["USER_LOGIN_ERROR"])>0 && $arResult['POST']['AUTH'] == 'Y'){
				$step = 2;
			}
           /* if ($arResult["SKIP_FIRST_STEP"] == "Y" && $i == 0)
                $arMenu[$i]["PASSED"] = true;
            if ($arResult["SKIP_FIRST_STEP"] == "Y" && $i == 0)
                $arMenu[$i]["PASSED"] = true;
            if ($arResult["SKIP_SECOND_STEP"] == "Y" && $i == 1)
                $arMenu[$i]["PASSED"] = true;
            if ($arResult["SKIP_THIRD_STEP"] == "Y" && $i == 2)
                $arMenu[$i]["PASSED"] = true;
            if ($arResult["SKIP_FORTH_STEP"] == "Y" && $i == 3)
                $arMenu[$i]["PASSED"] = true;*/
            if ($step == $i){
                $arMenu[$i]["ACTIVE"] = true;
            }elseif($step > $i){
                $arMenu[$i]["PASSED"] = true;
            }
        }
    }
}

if($_SESSION['CONFIRM_CODE_RESEND'] == $_SESSION['PHONE_CONFIRM_CODE']){
    $arResult['CHECK_NUMBER'] = 'Y';
}else{
    $arResult['CHECK_NUMBER'] = 'N';
}
ksort($arResult['PRINT_PROPS_FORM']['USER_PROPS_N']);
$arResult["MENU"] = $arMenu;
//xmp($arResult);
if(strlen($arResult['POST']['ORDER_PROP_DATE'])>0){
    $date = new DateTime($arResult['POST']['ORDER_PROP_DATE']);
    $arResult["WEEK_DAY"] = bhTools::convertDayNameLong($date->format('l'));
};

$arResult['ADD_LINE'] = bhTools::makeAddLine($arResult['BASKET_ITEMS']['ADDITIONAL']);

//Мега костыли
if(isset($_SESSION['street'])){
    $arResult['PRINT_PROPS_FORM']['USER_PROPS_N']['PERSONAL_STREET']['VALUE']=$_SESSION['street'];
}
if(isset($_SESSION['CITY_ID'])){
    $arResult['PRINT_PROPS_FORM']['USER_PROPS_N']['PERSONAL_CITY']['VALUE']=$_SESSION['CITY_ID'];
}

//echo "<pre>"; print_r($_SESSION); echo "</pre>";
//echo "<pre>"; print_r($arResult); echo "</pre>";

?>

<?
$arResult["totalPrice"]=$_SESSION["totalPrice"];
$arResult["duration"]=$_SESSION["duration"];
$arResult["totalPriceDiscount"]=$_SESSION["totalPriceDiscount"];
$arResult["periodName"]=$_SESSION["periodName"];
$arResult["periodDiscountPercent"]=$_SESSION["periodDiscountPercent"];
$arResult["periodDiscount"]=$_SESSION["periodDiscount"];
$arResult["periodTotalPrice"]=number_format($_SESSION["periodTotalPrice"],0,'.',' ');


?>

<?
$friends=$bonus=0;
$db = CUser::GetList($b = "ID", $o = "DESC", array("ID"=>$USER->GetID()), array('SELECT'=>array('UF_FRIENDS', 'UF_BONUS')));
while($sr = $db->Fetch()){
    if(strlen($sr['UF_FRIENDS']))
        $friends = $sr['UF_FRIENDS'];
    if(strlen($sr['UF_BONUS']))
        $bonus = $sr['UF_BONUS'];
}

if ($arResult["CurrentStep"] == 7){
    $user = new CUser;
    $user->Update($USER->GetID(), Array("UF_MNOGORU" => $_SESSION['mnogoru']));
    unset($_SESSION['mnogoru']);
}

$arResult["bonus"]=$bonus;
$arResult["friends"]=$friends;
$arResult["user_id"]=$USER->GetID();

$urlVk = FULL_SERVER_NAME . '/order/basket/?ref_inviter='.$USER->GetID();
$urlFb = FULL_SERVER_NAME . '/fb/?ref_inviter='.$USER->GetID();
$urlVk = urlencode($urlVk);
$urlFb = urlencode($urlFb);

$arResult["urlVk"]=$urlVk;
$arResult["urlFb"]=$urlFb;
$arResult["SHARE_TITLE"]=SHARE_TITLE;
$arResult["SHARE_DESCRIPTION"]=SHARE_DESCRIPTION;
$arResult["FULL_SERVER_NAME"]=FULL_SERVER_NAME;
?>


<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/modal-clean-tools.twig') ?>
<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/modal-what-we-clean.twig') ?>
<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/regular_cleaning.twig') ?>

