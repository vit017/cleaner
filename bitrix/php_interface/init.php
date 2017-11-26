<?
date_default_timezone_set('Europe/Moscow');
CModule::IncludeModule('htc.twigintegrationmodule');


AddEventHandler("main", "OnAfterUserAdd", Array("bhHandler", "OnBeforeUserAdd"));
AddEventHandler("main", "OnBeforeUserUpdate", Array("bhHandler", "OnBeforeUserAdd"));
AddEventHandler("main", "OnBeforeUserDelete", Array("bhHandler", "OnAfterBeforeDelete"));
AddEventHandler("sale", "OnSaleStatusOrder", Array("bhHandler", "OnSaleStatusOrder"));

AddEventHandler("sale", "OnOrderAdd", Array("bhHandler", "OnOrderAdd"));
AddEventHandler("sale", "OnOrderUpdate", Array("bhHandler", "OnOrderUpdate"));
//AddEventHandler("main", "OnBeforeUserLogin", Array("bhHandler", "OnBeforeUserLogin"));
//AddEventHandler("main", "OnAfterUserLogin", Array("bhHandler", "OnAfterUserLogin"));
AddEventHandler("sale", "OnSaleCancelOrder", Array("bhHandler", "OnSaleCancelOrder"));
AddEventHandler("sale", "OnSalePayOrder", Array("bhHandler", "OnSalePayOrder"));
AddEventHandler("sale", "OnOrderStatusSendEmail", Array("bhHandler", "OnOrderStatusSendEmail"));
AddEventHandler("sale", "OnSaleComponentOrderComplete", 'orderComplete');


AddEventHandler("sale", "OnSaleCancelOrder", "OrderCancelSms");



AddEventHandler("sale", "OnOrderSave", "OrderUpdateSms");
AddEventHandler("sale", "OnOrderSave", 'OrderSubscribe');



function OrderSubscribe($orderId, $arFields, $arOrder){

    if ($arFields["STATUS_ID"]=="F" && $arOrder["ORDER_PROP"][634]!="Y" && $arOrder["ORDER_PROP"][615]!="Один раз"){

        $arFields["STATUS_ID"]="N";
        $arFields["PAYED"]="N";
        $newOrderId = CSaleOrder::Add($arFields);
        $totalPrice=0;
        foreach ($arOrder["BASKET_ITEMS"] as $arBasket){
            $arBasket["ORDER_ID"]=$newOrderId;
            $arBasket["PRICE"]=$arBasket["BASE_PRICE"];
            $arBasket["DISCOUNT_PRICE"]=0;
            CSaleBasket::Add($arBasket);
            $totalPrice+=$arBasket["PRICE"];
        }

        $FUSER_ID = CSaleBasket::GetBasketUserID();
        CSaleBasket::OrderBasket($newOrderId, $FUSER_ID, SITE_ID, false);

        $dateArray = explode(".", $arOrder["ORDER_PROP"][6]);
        $day=$mouth=0;
        if ($arOrder["ORDER_PROP"][615]=="Раз в неделю"){
            $day=7;
            $discountPrice=($totalPrice*20)/100;
        }
        elseif ($arOrder["ORDER_PROP"][615]=="Раз в 2 недели"){
            $day=14;
            $discountPrice=($totalPrice*15)/100;
        }
        elseif ($arOrder["ORDER_PROP"][615]=="Раз в месяц"){
            $mouth=1;
            $discountPrice=($totalPrice*10)/100;
        }
        $arOrder['PRICE']=$totalPrice-$discountPrice;
        $newDate=date("d.m.Y", mktime(0, 0, 0, $dateArray[1]+$mouth, $dateArray[0]+$day, $dateArray[2]));

        $dbOrderProperties = CSaleOrderProps::GetList(
            array(),
            array("PERSON_TYPE_ID" => 1, "ACTIVE" => "Y", "UTIL" => "N"),
            false,
            false,
            array("ID", "NAME", "CODE")
        );

        while ($arOrderProperties = $dbOrderProperties->Fetch()){
            $curVal = $arOrder["ORDER_PROP"][$arOrderProperties["ID"]];
            if (strlen($curVal) > 0 && $arOrderProperties["CODE"]!="DATE" && $arOrderProperties["CODE"]!="Cleaner" && $arOrderProperties["CODE"]!="CLEANER_ID" && $arOrderProperties["CODE"]!="SUBSCRIBE_NEW_ORDER"){
                $arFieldsProp = array(
                    "ORDER_ID" => $newOrderId,
                    "ORDER_PROPS_ID" => $arOrderProperties["ID"],
                    "NAME" => $arOrderProperties["NAME"],
                    "CODE" => $arOrderProperties["CODE"],
                    "VALUE" => $curVal
                );
                \Bitrix\Sale\Internals\OrderPropsValueTable::Add($arFieldsProp);
            }elseif ($arOrderProperties["CODE"]=="DATE"){
                $arFieldsProp = array(
                    "ORDER_ID" => $newOrderId,
                    "ORDER_PROPS_ID" => $arOrderProperties["ID"],
                    "NAME" => $arOrderProperties["NAME"],
                    "CODE" => $arOrderProperties["CODE"],
                    "VALUE" => $newDate
                );
                \Bitrix\Sale\Internals\OrderPropsValueTable::Add($arFieldsProp);
            }elseif ($arOrderProperties["CODE"]=="Cleaner"){
                $arFieldsProp = array(
                    "ORDER_ID" => $newOrderId,
                    "ORDER_PROPS_ID" => $arOrderProperties["ID"],
                    "NAME" => $arOrderProperties["NAME"],
                    "CODE" => $arOrderProperties["CODE"],
                    "VALUE" => 0
                );
                \Bitrix\Sale\Internals\OrderPropsValueTable::Add($arFieldsProp);
            }elseif ($arOrderProperties["CODE"]=="SUBSCRIBE_NEW_ORDER"){
                //в старом заказе указываем, что создан новый
                unset($cleaderPropId);
                $rsVals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"), array("ORDER_ID" => $orderId, "CODE"=>"SUBSCRIBE_NEW_ORDER"));
                while ($arVals  = $rsVals->Fetch()){
                    $cleaderPropId=$arVals["ID"];
                }
                $arProp = CSaleOrderProps::GetList(array(), array('CODE' => "SUBSCRIBE_NEW_ORDER"))->Fetch();
                if ($cleaderPropId){
                    \Bitrix\Sale\Internals\OrderPropsValueTable::Update($cleaderPropId, array(
                        'NAME' => $arOrderProperties['NAME'],
                        'CODE' => $arOrderProperties['CODE'],
                        'ORDER_PROPS_ID' => $arOrderProperties['ID'],
                        'ORDER_ID' => $orderId,
                        'VALUE' => "Y",
                    ));
                }else{
                    \Bitrix\Sale\Internals\OrderPropsValueTable::Add(array(
                        'NAME' => $arOrderProperties['NAME'],
                        'CODE' => $arOrderProperties['CODE'],
                        'ORDER_PROPS_ID' => $arOrderProperties['ID'],
                        'ORDER_ID' => $orderId,
                        'VALUE' => "Y",
                    ));
                }

                $arFieldsOrderUpdate = array(
                    "PRICE" => $arOrder['PRICE']
                );
                CSaleOrder::Update($newOrderId, $arFieldsOrderUpdate);

                $smsToClient = $arOrder["ORDER_PROP"][4].', ваша следующая уборка по подписке, запланирована на '.substr($newDate, 0, -5).' в '.$arOrder["ORDER_PROP"][8].'. В случае необходимости вы можете изменить или перенести ее в личном кабинете на сайте Maxclean.ru';
                sendsms($arOrder["ORDER_PROP"][3], $smsToClient);
            }
        }

        //echo "<pre>"; print_r($arFields); echo "</pre>";
        //echo "<pre>"; print_r($arOrder); echo "</pre>";
        //exit();
    }

}


function orderComplete($orderId, $arOrder){
    global $USER;
    $oUser = new CUser;
    if (isset($_SESSION['LAZYLINK'])) unset($_SESSION['LAZYLINK']);
    if (isset($_SESSION['REF_DISCOUNT']) && !isset($_SESSION['NEW_REF_USER'])) {
        $userData = $oUser->GetByID($USER->GetID())->fetch();
        $UF_BONUS = (float)$userData['UF_BONUS'] - $_SESSION['REF_DISCOUNT'] > 0 ? (float)$userData['UF_BONUS'] - $_SESSION['REF_DISCOUNT'] : 0;
        $aFields = array(
            'UF_BONUS' => $UF_BONUS,
        );
        $oUser->Update($USER->GetID(), $aFields);
    }
    if (isset($_SESSION['REF_DISCOUNT'])) unset($_SESSION['REF_DISCOUNT']);
    if (isset($_SESSION['NEW_REF_USER']) || isset($_SESSION['REF_INVITER'])) {
        bhOrder::addProps($arOrder['PERSON_TYPE_ID'], $orderId, array('inviter_user' => $_SESSION['REF_INVITER']));
        $aFields = array(
            'UF_INVITER' => $_SESSION['REF_INVITER'],
        );
        $oUser->Update($USER->GetID(), $aFields);
    }
    unset($_SESSION['REF_INVITER']);
    unset($_SESSION['NEW_REF_USER']);
    unset($_SESSION['ORDER_COMMENT']);
    call_user_func_array('admitadProcess', func_get_args());
    call_user_func_array('actionPayProcess', func_get_args());
}
function addDiscountScore($inviter){
    global $USER;
    $oUser = new CUser;
    if ($invUserData = $oUser->GetByID($inviter)->fetch()) {
        $UF_FRIENDS = $invUserData['UF_FRIENDS'] + 1;
        $UF_BONUS = (float)$invUserData['UF_BONUS'] + BONUS_FOR_REF_INVITER;
        $aFields = array(
            'UF_FRIENDS' => $UF_FRIENDS,
            'UF_BONUS' => $UF_BONUS
        );
        $res = $oUser->Update($invUserData['ID'], $aFields);
    }
}
function actionPayProcess(){
    if (isset($_SESSION['actionPayFirstCome'])) {
        global $USER;
        $USER->Update($USER->GetID(), array('UF_ACTIONPAY_ID' => $_COOKIE['actionpay']));
        unset($_SESSION['actionPayFirstCome']);
    }
}
function admitadProcess($orderID, $orderParams){
    global $USER;
    $oUser = new CUser;
    $userData = $oUser->GetByID($USER->GetID())->fetch();
    $regTime = $userData['DATE_REGISTER'] ? strtotime($userData['DATE_REGISTER']) : time();
    $difTimeInDays = (time() - $regTime) / 60 / 60 / 24;
    if ($difTimeInDays <= 30 && ($_SESSION['admitad_uid'] || $userData['UF_ADMITAD_UID'])) {
        if (!$userData['UF_ADMITAD_UID']) {
            $aFields = array(
                'UF_ADMITAD_UID' => $_SESSION['admitad_uid']
            );
            $oUser->Update($USER->GetID(), $aFields);
            $newUser = true;
            $userData['UF_ADMITAD_UID'] = $_SESSION['admitad_uid'];
        } else {
            $newUser = false;
        }

        $admintadUrl = "https://ad.admitad.com/r?campaign_code=07ab7897dc&postback=1&postback_key=D49B85ce72207cCB6cC70b7405384b21";
        $paramsAr = array();
        $paramsAr['action_code'] = $newUser ? 1 : 2;
        $paramsAr['uid'] = $userData['UF_ADMITAD_UID'];
        $paramsAr['order_id'] = $orderID;
        $paramsAr['tariff_code'] = 1;
        $paramsAr['price'] = $orderParams['PRICE'];
        $paramsAr['quantity'] = 1;
        $paramsAr['position_id'] = 1;
        $paramsAr['position_count'] = 1;
        $paramsAr['product_id'] = '';
        $paramsAr['client_id'] = $USER->GetID();
        $paramsAr['payment_type=sale'];
        $paramsStr = '';
        foreach ($paramsAr as $parKey => $parVal) {
            $paramsStr .= $parKey . '=' . $parVal . '&';
        }
        $paramsStr = substr($paramsStr, 0, strlen($str) - 1);
        $admintadQuery = $admintadUrl . '&' . $paramsStr;
        file_get_contents($admintadQuery);
    }
}


//Отмена уборки
function OrderCancelSms($id, $val){

    if ($val=="Y"){

        $db_props = CSaleOrderPropsValue::GetOrderProps($id);
        while ($arProps = $db_props->Fetch()) {
            $PROPS[] = $arProps;
            if ($arProps["CODE"]=="PERSONAL_PHONE")
                $phone=$arProps["VALUE"];
            if ($arProps["CODE"]=="Cleaner")
                $cleanerId=$arProps["VALUE"];
            if ($arProps["CODE"]=="DATE")
                $date=substr($arProps["VALUE"], 0, -5);
            if ($arProps["CODE"]=="TIME")
                $time=$arProps["VALUE"];
            if ($arProps["CODE"]=="PERSONAL_STREET")
                $address=$arProps["VALUE"];
            if ($arProps["CODE"] == "DURATION")
                $duration = $arProps["VALUE"];
            if ($arProps["CODE"] == "PERSONAL_CITY")
                $city_id = $arProps["VALUE"];
            if ($arProps["CODE"] == "NAME")
                $USERNAME = $arProps["VALUE"];
            if ($arProps["CODE"] == "EMAIL")
                $EMAIL = $arProps["VALUE"];
        }

        if ($phone){
            $phone = str_replace(array("+", "-", "(", ")", " "), "", $phone);
            if ($phone[0]=="7")
                $phone[0]="8";
            sendsms($phone, 'Ваша уборка отменена. До свежей встречи!');
        }

        $rsUser = CUser::GetByID($cleanerId);
        $arCleaner = $rsUser->Fetch();
        $cleanerPhone = $arCleaner["PERSONAL_PHONE"];

        $sms="Заказ №".$id." на ".$date." ".$time.", ".$duration."ч., ".$address." - отменен.";

        if ($cleanerPhone)
            sendsms($cleanerPhone, $sms);

        $arEventFields = array(
            "ORDER_ID"      =>  $id,
            "ORDER_DATE"    =>  $date,
            "USERNAME"      =>  $USERNAME,
            "EMAIL"         =>  $EMAIL
        );
        CEvent::Send("CANCEL_ORDER", "s1", $arEventFields, "N", 53);
    }
}


//Назначение клинера
function OrderUpdateSms($orderId, $arFields, $arOrder){
    $db_props = CSaleOrderProps::GetList(array(), array("PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"]), false, false, array());

    while ($props = $db_props->Fetch()){
        if ($props["CODE"]=="Cleaner")
            $cleaner=$props["ID"];

        if ($props["CODE"]=="CLEANER_ID")
            $cleaner_id=$props["ID"];

        if ($props["CODE"]=="DURATION")
            $duration_id=$props["ID"];
    }

    if ($arOrder["ORDER_PROP"][$cleaner] && $arOrder["ORDER_PROP"][$cleaner]>0 && $arOrder["ORDER_PROP"][$cleaner_id]!="Y"){
        $db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"), array("ORDER_ID" => $orderId));
        $orProps = array();
        while ($prop = $db_vals->fetch()){
            $orProps[$prop['CODE']] = $prop;
        }

        $rsUser = CUser::GetByID($arOrder["ORDER_PROP"][$cleaner]);
        $arUser = $rsUser->Fetch();
        $user_phone=$arUser["PERSONAL_PHONE"];

        $cleanerPrice=350*$arOrder['ORDER_PROP'][$duration_id];
        $dop="";
        foreach ($arOrder['BASKET_ITEMS'] as $arProperty){
            if (preg_match("/до /iU", $arProperty["NAME"]))
                $area=$arProperty["NAME"];
            elseif ($arProperty["NAME"]=="духовку")
                $dop.="Дух, ";
            elseif ($arProperty["NAME"]=="пылесос")
                $dop.="Пыл, ";
            elseif ($arProperty["NAME"]=="внутри кухонных шкафчиков")
                $dop.="Кух, ";
            elseif ($arProperty["NAME"]=="внутри холодильника")
                $dop.="Хол, ";
            elseif ($arProperty["NAME"]=="окна")
                $dop.="Окн(".$arProperty['QUANTITY']."), ";
            elseif ($arProperty["NAME"]=="микроволновка")
                $dop.="СВЧ, ";
        }
        $sms="Вы назначены на заказ ".$orderId." ".substr($arOrder['ORDER_PROP'][6], 0, -5)." ".$arOrder['ORDER_PROP'][8].", ".$arOrder['ORDER_PROP'][2].", ";
        $sms.=$area.", ".$dop;
        $comment=$arFields["USER_DESCRIPTION"];
        if ($comment)
            $sms.=$comment.", ";
        $sms.=$arOrder['PRICE']."р, ".$cleanerPrice."р, ".$arOrder['ORDER_PROP'][7]."ч.";
        //sendsms(8 . $user_phone, $sms);


        $smsToCliner="Заказ ".$orderId.", сумма ".$arOrder['PRICE']."р, зп ".$cleanerPrice."р, ".substr($arOrder['ORDER_PROP'][6], 0, -5)." ".$arOrder['ORDER_PROP'][8].", ".$orProps["NAME"]["VALUE"].", ".$orProps["PERSONAL_PHONE"]["VALUE"].", ".$orProps["PERSONAL_STREET"]["VALUE"].", Подробности: https://maxclean.help/cleaners/";
        sendsms(8 . $user_phone, $smsToCliner);

        $smsToClient = 'Вам назначен клинер - ' . $arUser['NAME'] . ' ' . $arUser['LAST_NAME'];
        sendsms($orProps['PERSONAL_PHONE']['VALUE'], $smsToClient);

        unset($cleaderPropId);
        $rsVals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"), array("ORDER_ID" => $orderId, "CODE"=>"CLEANER_ID"));
        while ($arVals  = $rsVals->Fetch()){
            $cleaderPropId=$arVals["ID"];
        }
        $arProp = CSaleOrderProps::GetList(array(), array('CODE' => "CLEANER_ID"))->Fetch();
        if ($cleaderPropId){
            \Bitrix\Sale\Internals\OrderPropsValueTable::Update($cleaderPropId, array(
                'NAME' => $arProp['NAME'],
                'CODE' => $arProp['CODE'],
                'ORDER_PROPS_ID' => $arProp['ID'],
                'ORDER_ID' => $orderId,
                'VALUE' => "Y",
            ));
        }else{
            \Bitrix\Sale\Internals\OrderPropsValueTable::Add(array(
                'NAME' => $arProp['NAME'],
                'CODE' => $arProp['CODE'],
                'ORDER_PROPS_ID' => $arProp['ID'],
                'ORDER_ID' => $orderId,
                'VALUE' => "Y",
            ));
        }
        CSaleOrder::StatusOrder($orderId, "A");
    }
}

//not in use now
class CleanerRatingSetter
{
    function OnAfterIBlockElementUpdateHandler(&$arFields)
    {
        if($arFields["IBLOCK_ID"] == bhSettings::$IBlock_comments){
            $cleanerID = array_shift($arFields['PROPERTY_VALUES'][16]);
            $cleanerID = $cleanerID["VALUE"];
            $mark = 0;
            $c = 0;
            $db = CIBlockElement::GetList(array(), array("IBLOCK_ID"=>bhSettings::$IBlock_comments, "PROPERTY_CLEANER" => $cleanerID), false, false, array("PROPERTY_MARK", "PROPERTY_ORDER"));
            while($ar = $db->Fetch()){
                $mark += $ar['PROPERTY_MARK_VALUE'];
                $c++;
            }
            $user = new CUser;
            $user->Update($cleanerID, array("UF_RATING" => $mark/$c));
        }
    }
}

bhClassloadList();

function bhClassloadList()
{
    $called  = false;
    if ( !$called )
    {
        $classes = array(
            'bhSettings',
            'bhMailchimp',
            'bhSaleBasket',
            'bhCleaner',
            'smsc_smpp',
            'bhPayture',
            'bhHandler',
            'bhOrder',
            'bhApartment',
            'bhBasket',
            'bhCalendar',
            'bhTools',
            'bhSmsHttp',
            'Cities',
            'mailSmsRecipients'
        );

        foreach ( $classes as $className )
        {
            bhClassloader( $className );
        }
        $called = true;
    }
}
/**
 *
 *  * @param string $className
 */
function bhClassloader( $className )
{
    require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/breadhead/' .$className. '.php';
}

if (!function_exists('deb')) {
    function deb(){
        $args = func_get_args();
        if ($_GET['debug'] || (defined('MYDEBUG') && MYDEBUG)) {
            $bt = current(debug_backtrace());

            echo '<pre onclick="showHideDebugContent(this);">' . PHP_EOL;
            echo '<h1 style="font-size: 20px">DEBUG ' . $bt['file'] . ' - ' . $bt['line'] . '</h1>' . PHP_EOL;
            echo '<div style="display: none">' . PHP_EOL;
            foreach ($args as $arg) {
                if (is_object($arg) || is_array($arg)) {
                    print_r($arg);
                } else {
                    var_dump($arg);
                }
            }
            ?>
            </div>
            <script>
                function showHideDebugContent(p){
                    var el = p.getElementsByTagName('div')[0];
                    el.style.display = el.style.display == 'none' ? 'block' : 'none';
                }
            </script>
            </pre>
            <?
        }
    }
}
if (!function_exists('jdeb')) {
    function jdeb(){
        $args = func_get_args();
        if ($_GET['debug'] || (defined('MYDEBUG') && MYDEBUG)) {
            $bt = current(debug_backtrace());
            ?>
            <script>
                console.warn('JDEB', JSON.parse('<?=json_encode($args)?>'), '<?=$bt['file'] . ' - ' . $bt['line'];?>');
            </script>
            <?
        }
    }
}
if (!function_exists('debdie')) {
    function debdie(){
        $args = func_get_args();
        if ($_GET['debug'] || (defined('MYDEBUG') && MYDEBUG)) {
            $bt = current(debug_backtrace());

            echo '<pre onclick="showHideDebugContent(this);">' . PHP_EOL;
            echo '<h2 style="font-size: 20px">DEBUG ' . $bt['file'] . ' - ' . $bt['line'] . '</h2>' . PHP_EOL;
            echo '<div style="display: none">' . PHP_EOL;
            foreach ($args as $arg) {
                if (is_object($arg) || is_array($arg)) {
                    print_r($arg);
                } else {
                    var_dump($arg);
                }
            }
            ?>
            </div>
            <script>
                function showHideDebugContent(p){
                    var el = p.getElementsByTagName('div')[0];
                    el.style.display = el.style.display == 'none' ? 'block' : 'none';
                }
            </script>
            </pre>
            <?
            die('exit');
        }
    }
}
if (!function_exists('check_square_id')) {
    function check_square_id($id){
        return in_array($id, array(3745, 3746, 3747, 3748, 3749, 3750));
    }
}
if (!function_exists('sendsms')) {
    function sendsms($phone, $message){
        if (defined('NOSMS') && NOSMS) return true;

        $messageURL = urlencode($message);
        $validPhone = mb_ereg_replace('[^\d]+' ,'', $phone);
        if ($validPhone[0] == 7)
            $validPhone[0] = 8;
        $url = 'https://intra.becar.ru/f8/spservice/request.php?xml=&dima-phone=' . $validPhone . '&messagebody=' . $messageURL . '&MaxClean=';
        return @file_get_contents($url);
    }
}
if (!function_exists('phonePurify')) {
    function phonePurify($sPhone){
        $sPhone = mb_ereg_replace("[^0-9]",'',$sPhone);
        if (strlen($sPhone) == 11 && $sPhone[0] == 8) {
            $sPhone = substr($sPhone, 1);
        } else if (strlen($sPhone) != 10) {
            return false;
        }
        $sArea = substr($sPhone, 0,3);
        $sPrefix = substr($sPhone,3,3);
        $sNumber = substr($sPhone,6,2);
        $sNumber2 = substr($sPhone,8,2);
        $sPhone = 8 . " (".$sArea.") ".$sPrefix."-".$sNumber . '-' . $sNumber2;
        return($sPhone);
    }
}
if (!function_exists('getOrderData')) {
    function getOrderData($ORDER){
        $ORDERAr = CSaleOrder::GetByID($ORDER);
        $ORDERAr['ORDER_PRICE'] = $ORDERAr['PRICE'];
        $db_vals = CSaleOrderPropsValue::GetList(
            array("SORT" => "ASC"),
            array(
                "ORDER_ID" => $ORDER
            )
        );
        $ORDERAr['ORDER_PROP'] = array();
        while ($prop = $db_vals->fetch()) {
            $ORDERAr['ORDER_PROP'][$prop['ORDER_PROPS_ID']] = $prop['VALUE'];
        }
        $ORDERAr['ORDER_PROP'][7] = $ORDERAr['ORDER_PROP'][7] ? $ORDERAr['ORDER_PROP'][7] : $ORDERAr['ORDER_PROP'][351];
        $ORDERAr['ORDER_PROP'][6] = $ORDERAr['ORDER_PROP'][6] ? $ORDERAr['ORDER_PROP'][6] : $ORDERAr['ORDER_PROP'][350];
        $ORDERAr['ORDER_PROP'][8] = $ORDERAr['ORDER_PROP'][8] ? $ORDERAr['ORDER_PROP'][8] : $ORDERAr['ORDER_PROP'][352];
        $dbBasketItems = CSaleBasket::GetList(
            array(
                "NAME" => "ASC",
                "ID" => "ASC"
            ),
            array(
                "LID" => SITE_ID,
                "ORDER_ID" => $ORDER
            ),
            false,
            false,
            array("ID", "CALLBACK_FUNC", "MODULE",
                "PRODUCT_ID", "QUANTITY", "DELAY",
                "CAN_BUY", "PRICE", "WEIGHT", "NAME", "PRICE")
        );
        while ($basketItem = $dbBasketItems->fetch()) {
            $ORDERAr['BASKET_ITEMS'][] = $basketItem;
        }
        return $ORDERAr;
    }
}

//define('VASILIEV_PHONE', 89219460148);
define('VASILIEV_PHONE', 89165925607);
define('MANAGER_PHONE_SPB', 89112329260);

define('MANAGER_PHONE_MSK', 89266606930);
define('MANAGER_PHONE_MSK2', 89160857506);
define('TOLLFREENUMBER', 88002228330);

define('ROOTDIR',  realpath(__DIR__ . '/../../'));
define('HTTP_OR_HTTPS',  (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http');
define('FULL_SERVER_NAME',  HTTP_OR_HTTPS . '://' . $_SERVER['SERVER_NAME']);
define('CRON_LOGS_DIR', ROOTDIR . '/cron/logs/');
define('IS_PRODUCTION', $_SERVER['SERVER_NAME'] == 'maxclean.help');
define('IS_DEV', !IS_PRODUCTION);
define('MINORDERTIMEMINUTES', 120);//минимальный порог времени заказа
define('DISCOUNT_FOR_NEW_REF_USER', 0);//скидка новому пользователю на заказ, прошедшему по реферальной ссылке.
define('DISCOUNT_FOR_REF_INVITER', 500);//скидка пригласившему пользователю
define('BONUS_FOR_REF_INVITER', 500);//бонус на накопителый счет скидок пригласившему пользователю
define('SHARE_TITLE', "Получить скидку в 500р на Экоуборку квартиры.");//тайтл, который используется в том числе при расшаривании реферальной ссылки в соц сети
define('SHARE_DESCRIPTION', "Уборка квартир в Москве и Санкт-Петербурге от 1512р");//дескрипшен, который используется в том числе при расшаривании реферальной ссылки в соц сети

if (!defined('COMMON_EMAIL')) define('COMMON_EMAIL', 'otvet@maxclean.help');
if (!defined('KAZACHENKO_EMAIL')) define('KAZACHENKO_EMAIL', COMMON_EMAIL);
if (!defined('MANAGER_EMAIL')) define('MANAGER_EMAIL', 'otvet@maxclean.help');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/Mandrill/src/Mandrill.php");
bhSaleBasket::init();

session_start();
//$_SESSION['PHONE'] = bhSettings::$phone_spb;
$_SESSION['PHONE'] = phonePurify(MANAGER_PHONE);
$_SESSION['ADDRESS'] = bhSettings::$address_spb;
$_SESSION['CITY_ID'] = bhSettings::$city_id_spb;
$_SESSION['HOUR_PRICE'] = bhSettings::$hour_price_spb;
$_SESSION['CATALOG_PRICE_TYPE'] = bhSettings::$cpt_spb;

?>