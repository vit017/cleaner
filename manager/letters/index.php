<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 18.03.15
 * Time: 16:47
 */
define('NEED_AUTH', 'Y');
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('form');
CModule::IncludeModule('sale');
$arFilter = Array(
    "SITE_ID"       => "s1",
    "ACTIVE"        => "Y",
);
if($_REQUEST['NAME']){
    $arFilter['EVENT_NAME'] = $_REQUEST['NAME'];
}
if($_REQUEST['ID']){
    $ID = $_REQUEST['ID'];
}else{
    $ID = 2065;
}
$rsMess = CEventMessage::GetList($by="id", $order="desc", $arFilter);
while($arMess = $rsMess->fetch()){
    echo '<a target="_blank" href="?NAME='.$arMess['EVENT_NAME'].'">Открыть отдельно</a><br/>';
    switch($arMess['EVENT_NAME']){
        case 'USER_PASS_REQUEST':
            $dbUSER = CUser::getByID(1);
            $arUser = $dbUSER ->fetch();
            $arFields = array(
                "NAME" => $arUser["NAME"],
                "LAST_NAME" => $arUser["LAST_NAME"],
                "SERVER_NAME" => $_SERVER["SERVER_NAME"],
                "CHECKWORD" => "CHECKWORD",
                "URL_LOGIN" => urlencode($arUser["LOGIN"])
            );
            /*$arEventFields = array(
                "EMAIL_TO"				=> $arUser['EMAIL'],
                "RS_FORM_ID"			=> $arForm["ID"],
                "RS_FORM_NAME"			=> $arForm["NAME"],
                "RS_FORM_VARNAME"		=> $arForm["SID"],
                "RS_FORM_SID"			=> $arForm["SID"],
                "RS_RESULT_ID"			=> $arResult["ID"],
                "RS_DATE_CREATE"		=> $arResult["DATE_CREATE"],
                "RS_USER_ID"			=> $arResult['USER_ID'],
                "RS_USER_EMAIL"			=> $arUser['EMAIL'],
                "RS_USER_NAME"			=> $arUser["NAME"]." ".$arUser["LAST_NAME"],
                "RS_STATUS_ID"			=> $arStatus["ID"],
                "RS_STATUS_NAME"		=> $arStatus["TITLE"],
            );*/
          //  xmp($arFields);
            $flds = "";
            if(is_array($arFields))
            {

                foreach($arFields as $key => $value)
                {
                    $arMess["MESSAGE"] = str_replace("#".$key."#", $value, $arMess["MESSAGE"]);
                }
            }
            echo $arMess["MESSAGE"];
            break;
        case 'FORM_FILLING_VACANCY':
            $FORM_SID = "VACANCY";
            $rsForm = CForm::GetBySID($FORM_SID);
            $arForm = $rsForm->Fetch();
            $rsResults = CFormResult::GetList($arForm['ID'],
                ($by="s_timestamp"),
                ($order="desc"));
            if ($arAnswer = $rsResults->Fetch())
            {
                CForm::GetResultAnswerArray($arForm['ID'],
                    $arrColumns,
                    $arrAnswers,
                    $arrAnswersVarname,
                    array("RESULT_ID" => $arAnswer["ID"]));
                foreach($arrAnswersVarname[$arAnswer["ID"]] as $code=>$vals){
                    $arMess["MESSAGE"] = str_replace("#".$code."#", $vals[0]["USER_TEXT"], $arMess["MESSAGE"]);
                }
                $arFields = array(
                    "RS_FORM_NAME" => $arForm["NAME"],
                    "RS_FORM_ID" => $arForm["ID"],
                    "RS_RESULT_ID" => $arAnswer["ID"],
                    "RS_DATE_CREATE" => $arAnswer["DATE_CREATE"],
                    "SERVER_NAME" => $_SERVER["SERVER_NAME"]
                );
                foreach($arFields as $key => $value)
                {
                    $arMess["MESSAGE"] = str_replace("#".$key."#", $value, $arMess["MESSAGE"]);
                }
            }

            echo $arMess["MESSAGE"];
            break;
        case 'FORM_FILLING_FEEDBACK':
            $FORM_SID = "FEEDBACK";
            $rsForm = CForm::GetBySID($FORM_SID);
            $arForm = $rsForm->Fetch();
            $rsResults = CFormResult::GetList($arForm['ID'],
                ($by="s_timestamp"),
                ($order="desc"));
            if ($arAnswer = $rsResults->Fetch())
            {
                CForm::GetResultAnswerArray($arForm['ID'],
                    $arrColumns,
                    $arrAnswers,
                    $arrAnswersVarname,
                    array("RESULT_ID" => $arAnswer["ID"]));
                foreach($arrAnswersVarname[$arAnswer["ID"]] as $code=>$vals){
                    $arMess["MESSAGE"] = str_replace("#".$code."#", $vals[0]["USER_TEXT"], $arMess["MESSAGE"]);
                }
                $arFields = array(
                    "RS_FORM_NAME" => $arForm["NAME"],
                    "RS_FORM_ID" => $arForm["ID"],
                    "RS_RESULT_ID" => $arAnswer["ID"],
                    "RS_DATE_CREATE" => $arAnswer["DATE_CREATE"],
                    "SERVER_NAME" => $_SERVER["SERVER_NAME"]
                );
                foreach($arFields as $key => $value)
                {
                    $arMess["MESSAGE"] = str_replace("#".$key."#", $value, $arMess["MESSAGE"]);
                }
            }

            echo $arMess["MESSAGE"];
            break;
        case 'SALE_STATUS_CHANGED_M':
            $arFields = array(
                "SERVER_NAME" => $_SERVER["SERVER_NAME"],
                "ORDER_ID" => $ID,
                "TEXT" => ''
            );
            foreach($arFields as $key => $value)
            {
                $arMess["MESSAGE"] = str_replace("#".$key."#", $value, $arMess["MESSAGE"]);
            }
            echo $arMess["MESSAGE"];
            break;
        case 'SALE_STATUS_CHANGED_C':
            $arFields = array(
                "SERVER_NAME" => $_SERVER["SERVER_NAME"],
                "ORDER_ID" => $ID,
                "TEXT" => ''
            );
            foreach($arFields as $key => $value)
            {
                $arMess["MESSAGE"] = str_replace("#".$key."#", $value, $arMess["MESSAGE"]);
            }
            echo $arMess["MESSAGE"];
            break;
        case 'SALE_NEW_ORDER':
            $arOrder = CSaleOrder::getByID($ID);
            $dbUser = CUser::getByID($arOrder['USER_ID']);
            $arUser = $dbUser->fetch();
            $prop_line = '<div style="display: block;"><span style="color: #6e7677;">Логин:</span> '.$arUser['EMAIL'].'</div>';

            $dbProps = CSaleOrderPropsValue::GetOrderProps($ID);
            while($prop = $dbProps->fetch()){
                if($prop['CODE'] == 'PERSONAL_CITY'){
                    $city = CSaleLocation::GetByID($prop['VALUE'], 'ru');
                    $prop['VALUE'] = $city['CITY_NAME'];
                }
                $arOrderProps[$prop['CODE']]['ID'] = $prop['ORDER_PROPS_ID'];
                $arOrderProps[$prop['CODE']]['NAME'] = $prop['NAME'];
                $arOrderProps[$prop['CODE']]['VALUE'] = $prop['VALUE'];

                if($prop['CODE'] == 'PERSONAL_STREET' || $prop['CODE'] == 'PERSONAL_CITY' || $prop['CODE'] == 'PERSONAL_PHONE' || $prop['CODE'] == 'NAME'){
                    $prop_line .= '<div style="display: block;"><span style="color: #6e7677;">'.$prop['NAME'].':</span> '.$prop['VALUE'].'</div>';
                }
            }

            //date
            $date = new DateTime($arOrderProps['DATE']['VALUE']);
            $date->format("d.m.y");
            $month = bhTools::months(true);
            $date_line = $date->format("d ");
            $date_line .= $month[(int)$date->format("m")];
            $date_line .= $date->format(" Y");
            $arOrderProps['DATE']['PRINT_VALUE'] = $date_line;
            //time
            $arOrderProps['TIME_TO']['VALUE'] = $arOrderProps['TIME']['VALUE'] + $arOrderProps['DURATION']['VALUE'];
            $arOrderProps['TIME']['PRINT_VALUE'] = $arOrderProps['TIME']['VALUE'] != floor($arOrderProps['TIME']['VALUE'])?floor($arOrderProps['TIME']['VALUE']).':30':$arOrderProps['TIME']['VALUE'].':00';
            $arOrderProps['TIME_TO']['PRINT_VALUE'] = $arOrderProps['TIME_TO']['VALUE'] != floor($arOrderProps['TIME_TO']['VALUE'])?floor($arOrderProps['TIME_TO']['VALUE']).':30':$arOrderProps['TIME_TO']['VALUE'].':00';
            //duration
            $arOrderProps['DURATION']['PRINT_VALUE'] = $arOrderProps['DURATION']['VALUE_FORMATED'];
            $arOrderProps['TIME']['PRINT_VALUE'].=' до '.$arOrderProps['TIME_TO']['PRINT_VALUE'];
            //END props

            $basket = $arIDs = $arProdIds = array();
            $discount = 0;
            $db = CSaleBasket::getList(array(), array('ORDER_ID' => $ID));
            while($arBasket = $db->fetch()){
                if($arBasket['DISCOUNT_PRICE']>0){
                    $discount += $arBasket['DISCOUNT_PRICE'];
                }
                $clear_price += $arBasket['PRICE'] * $arBasket['QUANTITY'];
                $basket[$arBasket['ID']] = $arBasket;
                $arIDs[] = $arBasket['ID'];
                $arProdIds[] = $prod['PRODUCT_XML_ID'];

            }

            $db = CSaleBasket::GetPropsList(array(), array('BASKET_ID' => $arIDs));
            while($arProp = $db->fetch()){
                if(strlen($arProp['VALUE'])>0)
                    $basket[$arProp['BASKET_ID']]['PROPS'][$arProp['CODE']] = $arProp;
            }

            $arResult["BASKET"]['MAIN'] = array();
            $arResult["BASKET"]['ADDITIONAL'] = array();

            $arCodes = array();
            $arVerbs = array();
            $dbElems = CIBlockElement::getList(array(), array('IBLOCK_ID'=>bhSettings::$IBlock_catalog, 'ID'=>$arProdIds), false, false,array('ID', 'CODE', 'PROPERTY_NAME_FORMS', 'PROPERTY_VERB'));
            while($arEl = $dbElems->Fetch()){
                if(strlen($arEl['CODE'])>0) {
                    $arCodes[$arEl['ID']] = $arEl['CODE'];
                }
                if(strlen($arEl['PROPERTY_VERB_VALUE'])>0){
                    $arVerbs[$arEl['ID']] = trim($arEl['PROPERTY_VERB_VALUE']);
                }
            }
            $additional_line = $mail_line = '';
            $i = 0;
            foreach($basket as $prod){
                $mustbe = false;
                $service = false;
                foreach($prod['PROPS'] as $prop){
                    if (strlen($prop['VALUE'])>0) {
                        switch ($prop['CODE']) {
                            case 'MUSTBE':
                                $mustbe = true;
                                break;
                            case 'SERVICE':
                                $service = true;
                                break;
                        }
                    }
                };
                if (isset($arVerbs[$prod['PRODUCT_XML_ID']])){
                    $prod['NAME'] = $arVerbs[$prod['PRODUCT_XML_ID']].' '.$prod['NAME'];
                }

                if (isset($arCodes[$prod['PRODUCT_XML_ID']]))
                    $prod['CODE'] = $arCodes[$prod['PRODUCT_XML_ID']];

                if ($mustbe && $service){
                    continue;
                } elseif ($mustbe){
                    if($prod['QUANTITY']>0){
                        $mail_line .= $prod["NAME"].'м&#178;';
                    }
                } else{
                    if($i>0){
                        $additional_line .= ', ';
                    }
                    $additional_line .= $prod['NAME'];
                    $i++;
                }

            }
            $arResult["ORDER_PRICE_FORMATED"] = SaleFormatCurrency($clear_price, 'RUB');

            $res = CSaleUserTransact::GetList(Array("ID" => "DESC"), array("ORDER_ID" => $ID, 'DEBIT'=>'N', 'DESCRIPTION'=>'PAYED by free hours'));

            $SUM_PAID = 0;
            while ($r = $res->Fetch()){
                $SUM_PAID += $r['AMOUNT'];
            }
            $sum_paid_line = '';
            if($SUM_PAID > 0){
                $SUM_PAID = round($SUM_PAID);
                $SUM_PAID_FORMATED = SaleFormatCurrency($SUM_PAID, 'RUB');
                $sum_paid_line = '<div style="display: block;">Использовано бонусов: '.$SUM_PAID_FORMATED.' Р </div>';
            }

            $arResult['PAID_BY_FREE_HOURS'] = $SUM_PAID;
            if (!$arOrderProps['HOUR_PRICE']['VALUE'] || $arOrderProps['HOUR_PRICE']['VALUE']<=0){
                $hour_price = 700;
            } else {
                $hour_price = $arOrderProps['HOUR_PRICE']['VALUE'];
            }
            $arResult["SUM_PAID_TIME"] = $SUM_PAID;
            $arResult["SUM_PAID_TIME_FORMATED"] = SaleFormatCurrency($SUM_PAID, 'RUB');

            $arResult['NEED_TO_PAY'] = $clear_price - $SUM_PAID;
            if ($arResult['SUM_PAID'] >= $clear_price){
                $arResult['NEED_TO_PAY'] = 0;
            }
            $arResult['NEED_TO_PAY_FORMATED'] = SaleFormatCurrency($arResult['NEED_TO_PAY'], 'RUB');

            $pay_line = '';
            if($arResult["PAY_SYSTEM_ID"]==1){
                $pay_line = ' (оплата наличными)';
            }elseif($arResult["PAY_SYSTEM_ID"]==2){
                $pay_line = ' (оплата картой)';
            }


            if($discount>0){
                $DISCOUNT = floor($discount);
                $discount_line = '<div style="display: block;">Скидка: -'.$DISCOUNT.' Р </div>';
            }
            $Pay2_line = '';
            $Pay2_line = '<div style="display: block;">Итого: '.($clear_price - intVal($DISCOUNT) - intVal($SUM_PAID)).' Р ';
            $Pay2_line .='<span class="grey" style="text-transform: lowercase;"> (';
            if($arOrder["PAY_SYSTEM_ID"]==2)
                $Pay2_line .= 'списывается';
            else
                $Pay2_line .= 'оплата';

            $Pay2_line .=' после выполнения заказа)</span>';
            $Pay2_line .='</div>';

            $text = '<tr><td width="100%" style="vertical-align: top; padding: 0 30px 20px 30px;">
              <table style="font-family: Arial, Helvetica, sans-serif; width: 540px; border-collapse:collapse; border:none; table-layout:fixed; background: #eff1f1; font-size: 20px; line-height: 35px;">
                <tbody><tr>
                  <td width="100%" style="vertical-align: top; padding: 20px 40px 30px 40px;">
                    <span style="display: block; font-size: 15px; line-height: 30px; color: #6e7677; margin: 0; padding: 0 0 15px 0;">
                      <img src="http://gettidy.ru/layout/emails/images/icon-date.png" width="24" height="30" alt="" style="vertical-align: middle; display: inline-block; margin: 0 10px 0 0;">Дата и время
                    </span>
                    <div style="display: block;  margin: 0;">'.$arOrderProps['DATE']['PRINT_VALUE'].'</div>
                    <div style="display: block;  margin: 0;">с '.$arOrderProps['TIME']['PRINT_VALUE'].' <span style="color: #6e7677;">('.$arOrderProps['DURATION']['PRINT_VALUE'].')</span></div>
                  </td>
                </tr>
              </tbody></table>
            </td>
          </tr>
          <tr>
            <td width="100%" style="vertical-align: top; padding: 0 30px 20px 30px;">
              <table style="font-family: Arial, Helvetica, sans-serif; width: 540px; border-collapse:collapse; border:none; table-layout:fixed; background: #eff1f1; font-size: 20px; line-height: 35px;">
                <tbody><tr>
                  <td width="100%" style="vertical-align: top; padding: 20px 40px 30px 40px;">
                    <span style="display: block; font-size: 15px; line-height: 2; color: #6e7677; padding: 0 0 15px 0;">
                      <img src="http://gettidy.ru/layout/emails/images/icon-params.png" width="24" height="30" alt="" style="vertical-align: middle; display: inline-block; margin: 0 10px 0 0;">22Параметры квартиры
                    </span>
                    <div style="display: block;">'.$mail_line.'</div>'.(strlen($additional_line)>0?'<div style="display: block;"><span style="color: #6e7677;">Дополнительно:</span> '.$additional_line.'</div>':'').'

                  </td>
                </tr>
              </tbody></table>
            </td>
          </tr>
          <tr>
            <td width="100%" style="padding: 0 30px 20px 30px;">
              <table style="font-family: Arial, Helvetica, sans-serif; width: 540px; border-collapse:collapse; border:none; table-layout:fixed; background: #eff1f1; font-size: 20px; line-height: 35px;">
                <tbody><tr>
                  <td width="100%" style="padding: 20px 40px 30px 40px;">
                    <span style="display: block; font-size: 15px; line-height: 2; color: #6e7677; padding: 0 0 15px 0;">
                      <img src="http://gettidy.ru/layout/emails/images/icon-contacts.png" width="16" height="30" alt="" style="vertical-align: middle; display: inline-block; margin: 0 10px 0 0;">Контактные данные
                    </span>
                    '.$prop_line.'
                  </td>
                </tr>
              </tbody></table>
            </td>
          </tr>
          <tr>
            <td width="100%" style="padding: 0 30px 20px 30px;">
              <table style="font-family: Arial, Helvetica, sans-serif; width: 540px; border-collapse:collapse; border:none; table-layout:fixed; background: #eff1f1; font-size: 20px; line-height: 35px;">
                <tbody><tr>
                  <td width="100%" style=" padding: 20px 40px 30px 40px;">
                    <span style="display: block; font-size: 15px; line-height: 2; color: #6e7677; padding: 0 0 15px 0;">
                      <img src="http://gettidy.ru/layout/emails/images/icon-pay.png" width="22" height="30" alt="" style="vertical-align: middle; display: inline-block; margin: 0 10px 0 0;">Стоимость
                    </span>
                    <div style="display: block;">'.$arResult['ORDER_PRICE_FORMATED'].' Р за '.$arOrderProps['DURATION']['PRINT_VALUE'].'<span style="color: #6e7677;">'.$pay_line.'</span></div>'.$discount_line.$sum_paid_line.$Pay2_line.'
                  </td>
                </tr>
              </tbody></table>
            </td>
          </tr>
          <tr>
            <td width="100%" style="padding: 5px 30px 35px 30px;">
              <span style="display: block;">Отменить заказ можно в <a href="http://gettidy.ru/user/history/?ID='.$arOrder["ID"].'" target="_blank" style="border: none; outline: 0; text-decoration: none; color: #07b19a !important; font-weight: bold;"><span style="color: #07b19a">личном кабинете</span></a></span>
            </td>
          </tr>
 ';
            $arFields = array(
                "SERVER_NAME" => $_SERVER["SERVER_NAME"],
                "ORDER_ID" => $arOrder['ID'],
                "TEXT" => $text
            );
            foreach($arFields as $key => $value)
            {
                $arMess["MESSAGE"] = str_replace("#".$key."#", $value, $arMess["MESSAGE"]);
            }
            echo $arMess["MESSAGE"];
            break;
    }

}
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");

