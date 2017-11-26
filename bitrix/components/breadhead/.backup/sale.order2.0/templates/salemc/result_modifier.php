<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 23.05.14
 * Time: 14:24
 */

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
/*?><pre><? print_r($arResult);?></pre><?*/