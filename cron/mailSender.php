<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 29.07.14
 * Time: 14:43
 */
//30 16,20 * * * /usr/local/bin/php -q $HOME/gettidy.ru/www/cron/mailSender.php SERVER_NAME=gettidy.ru > /dev/null 2>&1 --delete-after
//30 16,20 * * * /usr/local/bin/php -q $HOME/cleanandaway.ru/www/cron/mailSender.php SERVER_NAME=cleanandaway.ru > /dev/null 2>&1 --delete-after
//30 16,20 * * * /usr/local/bin/php -q $HOME/dev.gettidy.ru/www/cron/mailSender.php SERVER_NAME=dev.gettidy.ru > /dev/null 2>&1 --delete-after
//30 16,20 * * * /usr/local/bin/php -q $HOME/dev2.gettidy.ru/www/cron/mailSender.php SERVER_NAME=dev2.gettidy.ru > /dev/null 2>&1 --delete-after

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("LANG", "s1");
foreach ($argv as $arg) {
    $e=explode("=",$arg);
    if (count($e)==2)
        $_GET[$e[0]]=$e[1];
    else
        $_GET[$e[0]]=0;
}
if ($_GET['SERVER_NAME']){
    $_SERVER['SERVER_NAME'] = $_GET['SERVER_NAME'];
}

if ( !isset($_SERVER['DOCUMENT_ROOT']) || $_SERVER['DOCUMENT_ROOT'] == '' ){
    $_SERVER['DOCUMENT_ROOT'] = '/home/u429586/'.$_SERVER['SERVER_NAME'].'/www/';
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

function RemindClean()
{
    CModule::IncludeModule('sale');
    $today = new DateTime();
    $todayStamp = $today->format('d.m.Y H:i:s');
    $tomorrow = new DateTime('tomorrow');
    $tomorrow = $tomorrow->format('d.m.Y');
    $arFilter = Array(
        "LID" => 's1',
        "CANCELED" => "N",
        "STATUS_ID" => "A",
        '!PROPERTY_VAL_BY_CODE_cleaner' =>'',
        'PROPERTY_VAL_BY_CODE_DATE' =>$tomorrow
    );

    if($_GET['debug']){
        echo 'Filter:';
        xmp($arFilter);
    }
    $excludeIds = array();
    $arCleaners = bhTools::getCleaners();

    $arPropEmailByPersonType = array();
    $dbPropEmail = CSaleOrderProps::GetList(array(), array('CODE'=>'REMIND_EMAIL'), false, false, array('ID', 'PERSON_TYPE_ID'));
    while ($arPropEmail = $dbPropEmail->fetch()){
        $arPropEmailByPersonType[$arPropEmail['PERSON_TYPE_ID']] = $arPropEmail['ID'];
    }

    $dbOrder = CSaleOrder::GetList(Array("ID" => "DESC"), $arFilter, false, false, Array("ID", "DATE_INSERT", "PAYED", "USER_ID", "LID", "PRICE", "CURRENCY", "ACCOUNT_NUMBER", "PERSON_TYPE_ID"));

    while ($arOrder = $dbOrder -> GetNext())
    {
        $nextOrder = false;
        $dbOrderProp = CSaleOrderPropsValue::GetList(Array(), Array("ORDER_ID" => $arOrder["ID"]));
        while ($arOrderProp = $dbOrderProp->Fetch()){
            switch ($arOrderProp['CODE']){
                case "Cleaner":
                    $cleanerID = $arOrderProp['VALUE'];
                    break;
                case 'REMIND_EMAIL':
                    if(strlen(trim($arOrderProp['VALUE']))>0){
                        $nextOrder = true;
                    }
                    break;
                case 'TIME':
                    $order_time = trim($arOrderProp['VALUE']).':00';
                    break;
                case 'PERSONAL_STREET':
                    $order_address = trim($arOrderProp['VALUE']);
                    break;
                case 'PERSONAL_PHONE':
                    $order_phone = trim($arOrderProp['VALUE']);
                    break;
            }
        }
        if ($nextOrder || !$cleanerID){
            continue;
        }

        //cleaner info
        $arCleaner = $arCleaners[$cleanerID];
        if (!isset($arCleaner["UF_RATING"]) || $arCleaner["UF_RATING"]<=0){
            $arCleaner["UF_RATING"] = 0;
        }
        $rating_line = '';
        for ($i=1; $i < 6; $i++){
            if($arCleaner["UF_RATING"]>=$i){
                $class = 'star';
            }else{
                $class = 'star_disabled';
            }
            $rating_line .=  '<img src="http://gettidy.ru/layout/emails/images/'.$class.'.jpg" alt="" width="15" height="13">';
        }
        if (strlen($arCleaner['PERSONAL_PHOTO']) > 0) {
            $photo = CFile::getPath($arCleaner['PERSONAL_PHOTO']);
        }
        else {
            $photo = '/layout/assets/images/content/cleaner-unknown.png';
        }
        $arCleaner["PHOTO"] = 'https://' . $_SERVER["SERVER_NAME"] . $photo;

        //payer
        $dbUser = CUser::GetByID($arOrder["USER_ID"]);
        $bSend = false;
        if ($arUser = $dbUser->Fetch()) {
            $arFields = Array(
                "ORDER_ID" => $arOrder["ACCOUNT_NUMBER"],
                "NAME" => $arUser["NAME"],
                "BCC" => COption::GetOptionString("sale", "order_email", "order@" . $_SERVER["SERVER_NAME"]),
                "EMAIL" => $arUser["EMAIL"],
                "SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@" . $_SERVER["SERVER_NAME"]),
                "CLEANER_NAME" => $arCleaner["NAME"],
                "DESCRIPTION" => htmlspecialchars($arCleaner["PERSONAL_NOTES"]),
                "RATING" => $rating_line,
                "PHOTO" => $arCleaner["PHOTO"]
            );


            if (!in_array($arOrder['ID'], $excludeIds))
                $bSend = true;
        }
        if($bSend)
        {
            if($_GET['debug']){
                echo $arOrder['ID'];
                xmp(array(
                    //'subject'=>$_SERVER['SERVER_NAME'].': Новый заказ N'.$arOrder['ID'],
                    'to'=>array(
                        array(
                            'email' => $arFields['EMAIL'],
                            'name' => $arFields['NAME'],
                            'type' => 'to'
                        )
                    ),
                    'global_merge_vars'=>array(
                        array('name'=>'SERVER_NAME', 'content'=>$_SERVER['SERVER_NAME']),
                        array('name'=>'NAME', 'content'=>$arFields['NAME']),
                        array('name'=>'CLEANER_NAME', 'content'=>$arFields['CLEANER_NAME']),
                        array('name'=>'PHOTO', 'content'=>$arFields['PHOTO']),
                        array('name'=>'RATING', 'content'=>$arFields['RATING']),
                        array('name'=>'DESCRIPTION', 'content'=>$arFields['DESCRIPTION']),
                        array('name'=>'ORDER_ID', 'content'=>$arFields['ORDER_ID']),
                        array('name'=>'DEFAULT_EMAIL_FROM', 'content'=>'hello@'.$_SERVER['SERVER_NAME']),
                        array('name'=>'SITE_NAME', 'content'=>$_SERVER['SERVER_NAME'])),
                    'merge'=>'Y'));

                xmp(array(
                    //'subject'=>*|NAME|*, завтра уборка!,
                    'to'=>array(
                        array(
                            'email' => $arFields['EMAIL'],
                            'name' => $arFields['NAME'],
                            'type' => 'to'
                        )
                    ),
                    'global_merge_vars'=>array(
                        array('name'=>'SERVER_NAME', 'content'=>$_SERVER['SERVER_NAME']),
                        array('name'=>'NAME', 'content'=>$arCleaner['NAME']),
                        array('name'=>'CLIENT_NAME', 'content'=>$arFields['NAME']),
                        array('name'=>'TIME', 'content'=>$order_time),
                        array('name'=>'ADDRESS', 'content'=>$order_address),
                        array('name'=>'CLIENT_PHONE', 'content'=>$order_phone),
                        array('name'=>'ORDER_ID', 'content'=>$arFields['ORDER_ID']),
                        array('name'=>'DEFAULT_EMAIL_FROM', 'content'=>'hello@'.$_SERVER['SERVER_NAME']),
                        array('name'=>'SITE_NAME', 'content'=>$_SERVER['SERVER_NAME'])),
                    'merge'=>'Y'));
            }else{
                //$event = new CEvent;
                // $event->Send('CLEANER_FOR_ORDER', SITE_ID, $arFields, "N");
                $token = bhSettings::$mandrillKey;
                $mandrill = new Mandrill($token);

                $mandrill->messages->sendTemplate(
                    'cleaner-for-order',
                    array(),
                    array(
                        //'subject'=>*|NAME|*, уборка уже завтра!,
                        'to'=>array(
                            array(
                                'email' => $arFields['EMAIL'],
                                'name' => $arFields['NAME'],
                                'type' => 'to'
                            )
                        ),
                        'global_merge_vars'=>array(
                            array('name'=>'SERVER_NAME', 'content'=>$_SERVER['SERVER_NAME']),
                            array('name'=>'NAME', 'content'=>$arFields['NAME']),
                            array('name'=>'CLEANER_NAME', 'content'=>$arFields['CLEANER_NAME']),
                            array('name'=>'PHOTO', 'content'=>$arFields['PHOTO']),
                            array('name'=>'RATING', 'content'=>$arFields['RATING']),
                            array('name'=>'DESCRIPTION', 'content'=>$arFields['DESCRIPTION']),
                            array('name'=>'ORDER_ID', 'content'=>$arFields['ORDER_ID']),
                            array('name'=>'DEFAULT_EMAIL_FROM', 'content'=>'hello@'.$_SERVER['SERVER_NAME']),
                            array('name'=>'SITE_NAME', 'content'=>$_SERVER['SERVER_NAME'])),
                        'merge'=>'Y')
                );
                $mandrill->messages->sendTemplate(
                    'template',
                    array(),
                    array(
                        //'subject'=>*|NAME|*, завтра уборка!,
                        'to'=>array(
                            array(
                                'email' => $arCleaner['EMAIL'],
                                'name' => $arCleaner['NAME'],
                                'type' => 'to'
                            )
                        ),
                        'global_merge_vars'=>array(
                            array('name'=>'SERVER_NAME', 'content'=>$_SERVER['SERVER_NAME']),
                            array('name'=>'NAME', 'content'=>$arCleaner['NAME']),
                            array('name'=>'CLIENT_NAME', 'content'=>$arFields['NAME']),
                            array('name'=>'TIME', 'content'=>$order_time),
                            array('name'=>'ADDRESS', 'content'=>$order_address),
                            array('name'=>'CLIENT_PHONE', 'content'=>$order_phone),
                            array('name'=>'ORDER_ID', 'content'=>$arFields['ORDER_ID']),
                            array('name'=>'DEFAULT_EMAIL_FROM', 'content'=>'hello@'.$_SERVER['SERVER_NAME']),
                            array('name'=>'SITE_NAME', 'content'=>$_SERVER['SERVER_NAME'])),
                            'merge'=>'Y')
                );
                file_put_contents($_SERVER["DOCUMENT_ROOT"].'/cron/logs/mailSender_log.txt', $todayStamp.'-'.$arFields['ORDER_ID']."\n", FILE_APPEND);
                $arFields = array(
                    "ORDER_ID" => $arOrder['ID'],
                    "ORDER_PROPS_ID" => $arPropEmailByPersonType[$arOrder['PERSON_TYPE_ID']],
                    "NAME" => "REMIND_EMAIL",
                    "CODE" => "REMIND_EMAIL",
                    "VALUE" => $todayStamp
                );
                CSaleOrderPropsValue::Add($arFields);
            }
            $excludeIds[] = $arOrder['ID'];
        }

    }
}
RemindClean();
