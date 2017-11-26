<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

if(!CModule::IncludeModule("ims.payture")) return;
IncludeModuleLangFile(__FILE__);
// params
$Sum = CSalePaySystemAction::GetParamValue("SHOULD_PAY");
$merchID = bhSettings::$p_merchID;//CSalePaySystemAction::GetParamValue("SHOP_ID");
$host = bhSettings::$p_host;//CSalePaySystemAction::GetParamValue("HOST_CONNECT");

$orderNumber = CSalePaySystemAction::GetParamValue("ORDER_ID");
$SessionType = CSalePaySystemAction::GetParamValue("SESSION_TYPE");

if($_SERVER['SERVER_NAME']=='maxclean.help' || $_SERVER['SERVER_NAME']=='test.maxclean.help') {
    $finalUrl = "http://" . $_SERVER['HTTP_HOST'] . "/order/result/?result={success}";//CSalePaySystemAction::GetParamValue("FINAL_URL");
}else{
    $finalUrl = "http://" . $_SERVER['HTTP_HOST'] . "/order/result/?result={success}";
}
if($_REQUEST['view']){
    $ViewType = $_REQUEST['view'];
}else
    $ViewType = CSalePaySystemAction::GetParamValue("VIEW_TYPE");

// iframe
$iframe_width = CSalePaySystemAction::GetParamValue("IFRAME_WIDTH");
$iframe_height = CSalePaySystemAction::GetParamValue("IFRAME_HEIGHT");

$Sum = 1;
$sum_print = number_format($Sum, 2, '', '');
/*
$cardID = false;
if(!$cardID){
    $GetCradsList = 'https://'.$host.'/vwapi/GetList?VWID='.$shopID.'&DATA='.urlencode('VWUserLgn='.$payer.';VWUserPsw='.$hash);
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/xml.php");
    $initXML = new CDataXML();
    $initXML->LoadString(file_get_contents($GetCradsList));
    $arInitResult = $initXML->GetArray();

    if (count($arInitResult)>0 && $arInitResult["GetList"]["@"]["Success"] == "True")
    {
        if(isset($arInitResult["GetList"]['#']["Item"])){
            $cardID = $arInitResult["GetList"]['#']["Item"][0]["@"]["CardId"];
        }else {
            $cardID = false;
        }
    }
    else {
        $cardID = false;
    }
}
if(!$cardID){
    $cardID = 'FreePay';
}

if(strlen($cardID)>0){
    $Payer = new CUSER;
    $Payer->Update($GLOBALS['USER']->GetID(), array("UF_PAYTURE" => $cardID));
    $initData = urlencode('SessionType='.$SessionType.';OrderId='.$orderNumber.';Amount='.$Sum.';IP='.$_SERVER['REMOTE_ADDR'].';Url='.$finalUrl.';Total='.$sum_print.';VWUserLgn='.$payer.';VWUserPsw='.$hash.';CardId='.$cardID);
}*/
$arProps = bhOrder::getProps($orderNumber);
$arOrder = CSaleOrder::getByID($orderNumber);
$attemp = 0;
if ( strlen($arProps['PAYTURE_ATTEMP']['VALUE']) >0 ){
    $attemp = $arProps['PAYTURE_ATTEMP']['VALUE'];
}
$PayAddress = bhPayture::getBlock($SessionType, $orderNumber, $sum_print, $finalUrl, $attemp);
$attemp++;

bhOrder::setProp($orderNumber, 'PAYTURE_ATTEMP', $attemp, $arOrder["PERSON_TYPE_ID"]);

if ($ViewType == "iframe") {


?><!--/archive/eWallet_Pay.html-->

<iframe src="<?=$PayAddress?>" width="<?=$iframe_width?>" height="<?=$iframe_height?>" frameBorder="0"></iframe>
<?
}

if ($ViewType == "button") {
    ?>
    <a href="/order/payment.php?ID=<?=$orderNumber?>" id="pay_btn" class="btn btn_with_icons btn_responsive_true">
        Добавить карту
    </a>
<?
}
if ($ViewType == "current") {
    ?>
    <script type="text/javascript">
        window.top.location.href='<?=CUtil::JSEscape($PayAddress)?>';
    </script>
<?
}







