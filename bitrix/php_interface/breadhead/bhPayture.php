<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 31.07.14
 * Time: 13:09
 */
CModule::IncludeModule('sale');
CModule::IncludeModule('main');
CModule::IncludeModule('iblock');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/xml.php");
if(!CModule::IncludeModule("ims.payture")) return;
class bhPayture{

    public static function getAuthData($orderID){
        $arOrder = CSaleOrder::getByID($orderID);
        if(is_array($arOrder)){
            $arPropVal = array();
            $dbProps = CSaleOrderPropsValue::GetOrderProps($orderID);
            while($arProp = $dbProps -> fetch()){
                if(strlen(trim($arProp['VALUE']))>0) $arPropVal[$arProp['CODE']] = $arProp['VALUE'];
            }

            if(isset($arPropVal['PAYTURE_NEW']) && isset($arPropVal['PAYTURE_LOGIN'])){
                $payer = 'email_'.$arPropVal['PAYTURE_LOGIN'];
                $pswd = hash('md5', $payer);
            }else{
                $db = CUser::GetList($b = "ID", $o = "DESC", array("ID"=>$arOrder['USER_ID']));
                while($user = $db->Fetch()){
                    $payer = $user['LOGIN'];
                    $pswd = CUser::GetPasswordHash($payer["PASSWORD"]);
                };
            };
            $result = array('LOGIN' => $payer, 'PASSWORD' => $pswd);
            return $result;
        }else{
            return false;
        }
    }


    public static function isPayed($orderID){
        if ( $orderID <=0 || !$orderID ) return false;

        $return = false;
        $host = bhSettings::$p_host;
        $attemp = 0;
        $db_vals = CSaleOrderPropsValue::GetList(
            array("SORT" => "ASC"),
            array(
                "ORDER_ID" => $orderID,
                "CODE" => 'PAYTURE_ATTEMP'
            )
        );
        if ($arVals = $db_vals->Fetch()) {
            $attemp = intVal($arVals["VALUE"]);
        }
        for($i = $attemp-1; $i >= 0; $i--){
            if ($i == 0){
                $initData = 'OrderId='.$orderID;
            }else{
                $initData = 'OrderId='.$orderID.'_'.$i;
            }

            $initData = urlencode($initData);
            $initVars = 'VWID='.bhSettings::$p_merchID2.'&Data='.$initData;
            $payStatus = 'https://'.$host.'/vwapi/PayStatus?'.$initVars;
            $initXML = new CDataXML();
            $initXML->LoadString(self::getCurl($payStatus));
            $arInitResult = $initXML->GetArray();

            if($arInitResult['PayStatus']['@']['Success'] == 'True'){
                if ( $arInitResult['PayStatus']['@']['Status'] == 'Charged')
                {
                    return true;
                } else{}
            } else{}
        }

        return $return;
    }

    public static function getPay($orderID, $cardID, $amount, $attemp = 0){
        if ( self::isPayed($orderID) ) return true;

        $host = bhSettings::$p_host;
        $arAuth = self::getAuthData($orderID);

        if(is_array($arAuth)){
            $payer = $arAuth['LOGIN'];
            $pswd = $arAuth['PASSWORD'];
        }else{
            return false;
        }

        if ( $attemp > 0 ){
            $orderID = $orderID.'_'.$attemp;
        }

        $initData = 'VWUserLgn='.$payer.';VWUserPsw='.$pswd.';CardId='.$cardID.';OrderId='.$orderID.';Amount='.$amount*100;
        $initData = urlencode($initData);
        $initVars = 'VWID='.bhSettings::$p_merchID2.'&Data='.$initData;
        $pay = 'https://'.$host.'/vwapi/Pay?'.$initVars;
        //xmp($pay);
        $initXML = new CDataXML();
        $initXML->LoadString(self::getCurl($pay));
        $arInitResult = $initXML->GetArray();
        //xmp($arInitResult);

        if($arInitResult['Pay']['@']['Success'] == 'True'){
            return true;
        }else{
            return false;
        }
    }

    public static function getStatus($orderID){
        $host = bhSettings::$p_host;
        $merchID = bhSettings::$p_merchID;

        $getState = 'https://'.$host.'/vwapi/PayStatus?VWID='.$merchID.'&DATA=OrderId='.$orderID;
        $initXML = new CDataXML();
        $initXML->LoadString(self::getCurl($getState));
        $arInitResult = $initXML->GetArray();

        $blockAmount = false;
        if ($arInitResult['PayStatus']['@']['Success'] == 'True'){
            $blockAmount  = trim($arInitResult['PayStatus']['@']['Amount']);
        }
        return $blockAmount;
    }

    public static function getBlock($SessionType, $orderID, $sum_print, $finalUrl, $attemp = 0){
        $host = bhSettings::$p_host;
        $merchID = bhSettings::$p_merchID;

        $arAuth = self::getAuthData($orderID);
        if(is_array($arAuth)){
            $payer = $arAuth['LOGIN'];
            $pswd = $arAuth['PASSWORD'];
        }else{
            return false;
        }

        if ( $attemp > 0 ){
            $orderID = $orderID.'_'.$attemp;
        }
        /*$sum_print = 100;*/
        $initData = urlencode('SessionType='.$SessionType.';OrderId='.$orderID.';Amount='.$sum_print.';IP='.$_SERVER['REMOTE_ADDR'].';Url='.$finalUrl.';Total='.$sum_print.';VWUserLgn='.$payer.';VWUserPsw='.$pswd.';TemplateTag=Payture');
        $initData = urlencode('SessionType='.$SessionType.';OrderId='.$orderID.';Amount='.$sum_print.';IP='.$_SERVER['REMOTE_ADDR'].';Url='.$finalUrl.';Total='.$sum_print.';VWUserLgn='.$payer.';VWUserPsw='.$pswd.';TemplateTag=Payture;Product=уборку');
        $initVars = 'VWID='.$merchID.'&Data='.$initData;
        $PayAddress = 'https://'.$host.'/vwapi/Pay?'.$initVars;
//xmp($PayAddress);
        return $PayAddress;
    }

    public static function getUnblock($orderID, $amount){
        $host = bhSettings::$p_host;
        $merchID = bhSettings::$p_merchID;
        $pswd = bhSettings::$p_merchPswd;

        $unblock = 'https://'.$host.'/vwapi/Unblock?VWID='.$merchID.'&Password='.$pswd.'&OrderId='.$orderID.'&Amount='.$amount;
        return $unblock;
    }

    public static function getCardIDbyNumber($orderID, $cardNumber){
        $host = bhSettings::$p_host;
        $merchID = bhSettings::$p_merchID;

        $arAuth = self::getAuthData($orderID);
        if(is_array($arAuth)){
            $payer = $arAuth['LOGIN'];
            $pswd = $arAuth['PASSWORD'];
        }else{
            return false;
        }

        $GetCradsList = 'https://' . $host . '/vwapi/GetList?VWID=' . $merchID . '&DATA=' . urlencode('VWUserLgn=' . $payer . ';VWUserPsw=' . $pswd);

        $initXML = new CDataXML();
        $initXML->LoadString(self::getCurl($GetCradsList));
        $arInitResult = $initXML->GetArray();

        $cardID = false;
        if (isset($arInitResult["GetList"]['#']["Item"])) {
            foreach ($arInitResult["GetList"]['#']["Item"] as $card) {
                $cardName = $card["@"]['CardName'];
                if ($cardName == $cardNumber) $cardID = $card["@"]["CardId"];
            }
        }
        return $cardID;
    }

    public static function getCardID($orderID){
        $cardID = false;
        $host = bhSettings::$p_host;
        $merchID = bhSettings::$p_merchID;

        $initData = urlencode('OrderId='.$orderID);
        $initVars = 'VWID='.$merchID.'&Data='.$initData;
        $status = 'https://'.$host.'/vwapi/PayStatus?'.$initVars;
        $initXML = new CDataXML();
        $initXML->LoadString(self::getCurl($status));
        $arInitResult = $initXML->GetArray();

        if($arInitResult['PayStatus']['@']['Success'] == 'True'){
            $cardID = $arInitResult['PayStatus']['@']['CardId'];
        }
        return $cardID;
    }


    private function getCurl($line){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $line);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        //����������, ���� ���� �� ����������
        // $result=$line;
        return $result;
    }


    /*
        function getLogin($orderID){
            $arOrder = CSaleOrder::getByID($orderID);
            if(is_array($arOrder)){
                $arPropVal = array();
                $dbProps = CSaleOrderPropsValue::GetOrderProps($orderID);
                while($arProp = $dbProps -> fetch()){
                    if(strlen(trim($arProp['VALUE']))>0)
                        $arPropVal[$arProp['CODE']] = $arProp['VALUE'];
                }

                if(isset($arPropVal['PAYTURE_NEW']) && isset($arPropVal['EMAIL'])){
                    $payer = 'email_'.$arPropVal['EMAIL'];
                }else{
                    $db = CUser::GetList($b = "ID", $o = "DESC", array("ID"=>$arOrder['USER_ID']));
                    while($user = $db->Fetch()){
                        $payer = $user['LOGIN'];
                    };
                };
                return $payer;
            }else
                return false;

        }

        function getPassword($orderID){
            $arOrder = CSaleOrder::getByID($orderID);
            if(is_array($arOrder)){
                $arPropVal = array();
                $dbProps = CSaleOrderPropsValue::GetOrderProps($orderID);
                while($arProp = $dbProps -> fetch()){
                    if(strlen(trim($arProp['VALUE']))>0)
                        $arPropVal[$arProp['CODE']] = $arProp['VALUE'];
                }

                if(isset($arPropVal['PAYTURE_NEW']) && isset($arPropVal['EMAIL'])){
                    $payer = 'email_'.$arPropVal['EMAIL'];
                    $pswd = hash('md5', $payer);
                }else{
                    $db = CUser::GetList($b = "ID", $o = "DESC", array("ID"=>$arOrder['USER_ID']));
                    while($user = $db->Fetch()){
                        $pswd = CUser::GetPasswordHash($user["PASSWORD"]);
                    };
                };
                return $pswd;
            }else
                return false;

        }*/
}
