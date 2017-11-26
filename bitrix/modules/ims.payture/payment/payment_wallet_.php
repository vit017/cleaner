<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

if(!CModule::IncludeModule("ims.payture")) return;
IncludeModuleLangFile(__FILE__);
// params
$Sum = CSalePaySystemAction::GetParamValue("SHOULD_PAY");
$ShopID = bhSettings::$p_merchID;//CSalePaySystemAction::GetParamValue("SHOP_ID");
$host = bhSettings::$p_host;//CSalePaySystemAction::GetParamValue("HOST_CONNECT");
$customerNumber = CSalePaySystemAction::GetParamValue("ORDER_ID");
$orderDate = CSalePaySystemAction::GetParamValue("ORDER_DATE");
$orderNumber = CSalePaySystemAction::GetParamValue("ORDER_ID");
$SessionType = CSalePaySystemAction::GetParamValue("SESSION_TYPE");
if($_SERVER['SERVER_NAME']=='gettidy.ru' || $_SERVER['SERVER_NAME']=='cleanandaway.ru') {
    $FinalUrl = "https://" . $_SERVER['HTTP_HOST'] . "/order/result/?result={success}";//CSalePaySystemAction::GetParamValue("FINAL_URL");
}else{
    $FinalUrl = "http://" . $_SERVER['HTTP_HOST'] . "/order/result/?result={success}";
}
if($_REQUEST['view']){
    $ViewType = $_REQUEST['view'];
}else
    $ViewType = CSalePaySystemAction::GetParamValue("VIEW_TYPE");

// iframe
$iframe_width = CSalePaySystemAction::GetParamValue("IFRAME_WIDTH");
$iframe_height = CSalePaySystemAction::GetParamValue("IFRAME_HEIGHT");

$Sum_print = number_format($Sum, 2, '.', '');

$arOrder = CSaleOrder::GetByID($orderNumber); //получаем номер оплаченого заказа от ПС
//$Sum = $arOrder["PRICE"]-$arOrder["SUM_PAID"]+700;
$Sum = 1;
$Sum = number_format($Sum, 2, '', '');
// init payture

$payer = $GLOBALS['USER']->GetLogin();
$cardID = false;

/*$db = CUser::GetList($b = "ID", $o = "DESC", array("ID"=>$GLOBALS['USER']->GetID()), array('SELECT'=>array('UF_PAYTURE')));
while($sr = $db->Fetch()){
    if(strlen($sr['UF_PAYTURE'])>0)
        $cardID = trim($sr['UF_PAYTURE']);

};*/
$hash = CUser::GetPasswordHash($GLOBALS['USER']->GetParam("PASSWORD_HASH"));
$hash = hash('md5', $payer);
/*
if(!$cardID){
    $GetCradsList = 'https://'.$host.'/vwapi/GetList?VWID='.$ShopID.'&DATA='.urlencode('VWUserLgn='.$payer.';VWUserPsw='.$hash);
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
}*/
if(!$cardID){
    $cardID = 'FreePay';
}
/*
if(strlen($cardID)>0){
    $Payer = new CUSER;
    $Payer->Update($GLOBALS['USER']->GetID(), array("UF_PAYTURE" => $cardID));
    $initData = urlencode('SessionType='.$SessionType.';OrderId='.$orderNumber.';Amount='.$Sum.';IP='.$_SERVER['REMOTE_ADDR'].';Url='.$FinalUrl.';Total='.$Sum_print.';VWUserLgn='.$payer.';VWUserPsw='.$hash.';CardId='.$cardID);
}else*/
$initData = urlencode('SessionType='.$SessionType.';OrderId='.$orderNumber.';Amount='.$Sum.';IP='.$_SERVER['REMOTE_ADDR'].';Url='.$FinalUrl.';Total='.$Sum_print.';VWUserLgn='.$payer.';VWUserPsw='.$hash);
$initVars = 'VWID='.$ShopID.'&Data='.$initData;

//D26242844a6de56136436f48547d30dd2
$PayAddress = 'https://'.$host.'/vwapi/Pay?'.$initVars;
/*if($GLOBALS['USER']->getID()==219){
    echo $PayAddress;
    die;
}*/
//file_put_contents($_SERVER["DOCUMENT_ROOT"].'/logs.txt', $PayAddress."\n", FILE_APPEND);

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







