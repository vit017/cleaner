<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
?>


<html>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet"> 

    <script type="text/javascript">
    $(document).ready( function(){
        $(".sendSmsForm").on( "submit", function(e){
            e.preventDefault();
            var formPlace=$(this);
            $.ajax({
                type: "POST", 
                url: "/export/orderListAjax.php",  
                data: $(this).serialize(),  
                success: function(html){  
                    formPlace.html(html);  
                }
            });
        }); 
    });
    </script>
    <style>
    	body{
    		font-family: 'Open Sans', sans-serif;
    	}
        h1{
            text-align:center;
            font-family: 'Open Sans', sans-serif;
            font-size:25px;
        }

        .sms_table{
            border:1px solid gray;
            border-collapse: collapse;
            font-family: 'Open Sans', sans-serif;
            font-size:13px;
        }        
        .sms_table tr td, .sms_table tr th{
            border:1px solid gray;
            padding: 10px;
        }
        .sms_table textarea{
            width:100%;
            resize:none;
            border:1px solid gray;
            margin-bottom:10px;
            padding:10px;
            font-family: 'Open Sans', sans-serif;
            font-size:13px;
        }        
        .sms_table input[type="submit"]{
            border:1px solid gray;
            cursor: pointer;
            font-family: 'Open Sans', sans-serif;
            font-size:13px;            
        }
    </style>    
</head>
<body>



<?
CModule::IncludeModule("sale");
CModule::IncludeModule("iblock");
if (isset($_GET['getAnaliticsContacts'])) {
    ob_start();
    $FROM = $_GET['from'];
    $requiredParams = array(
        'ID' => 'ID',
        'DATE_INSERT' => 'Дата создания',
        'Cleaner' => 'Cleaner',
        'STATUS_ID' => 'Статус',
        'DURATION' => 'Продолжительность',
        'PRICE' => 'Сумма',
        'func(%Продолжительность% * 350)' => 'З/П Клинера',
        'func(%Сумма% / 100 * 17)' => 'Платформа',
        'func(%Сумма% - %З/П Клинера% - %Платформа%)' => 'УК',
        'PAY_SYSTEM_ID' => 'Платежная система',
        'PAYED' => 'Оплачен',
        'USER_NAME' => 'Ваше имя',
        'PERSONAL_PHONE' => 'Телефон',
        'PERSONAL_STREET' => 'Адрес',
        'TIME' => 'Время',
        'DATE' => 'Дата',
        'USER_LOGIN' => 'Email покупателя',
        'USER_ID' => 'Покупатель',
        'PERSONAL_CITY' => 'Город'
    );
    $ORDERS = array();
    $arFilter = array();
    if ($FROM) $arFilter['>=DATE_INSERT'] = $FROM;
    $db_sales = CSaleOrder::GetList(array("DATE_INSERT" => "DESC"), $arFilter, false, false, array());
    while ($arSales = $db_sales->Fetch()) {
        $orderID = $arSales['ID'];
        $db_vals = CSaleOrderPropsValue::GetList(
            array("SORT" => "ASC"),
            array(
                "ORDER_ID" => $orderID
            )
        );
        $orderProps = array();
        while ($prop = $db_vals->fetch()) {
            $orderProps[$prop['CODE']] = $prop;
        }

        $orderID = '9467';
        $orderCommentAr = CIblockElement::GetList(array('ID' => 'ASC'), array('IBLOCK_ID' => 5, 'PROPERTY_ORDER_VALUE' => $orderID), false, false, array('ID', 'PREVIEW_TEXT'))->fetch();
        $orCommentMarkAr = CIBlockElement::GetProperty(5, $orderID, array("sort" => "asc"), Array("CODE" => "MARK"))->fetch();

        $ORDER = array();
        $funcParams = array();
        foreach ($requiredParams as $rParamKey => $rParamVal) {
            if ($rParamKey == 'PERSONAL_CITY') {
                $cityID = isset($orderProps[$rParamKey]) ? (int)$orderProps[$rParamKey]['VALUE'] : '';
                $ORDER[$rParamVal] = Cities::$codes[$cityID];
            } else if (in_array($rParamKey, array('Cleaner', 'USER_ID'))) {
                $uid = false;
                if ($rParamKey == 'Cleaner') {
                    $uid = $orderProps[$rParamKey] ? (int)$orderProps[$rParamKey]['VALUE'] : false;
                } else if ($rParamKey == 'USER_ID') {
                    $uid = $arSales[$rParamKey];
                }
                $ORDER[$rParamVal] = '';
                if ($uid) {
                    $user = CUser::GetByID($uid)->fetch();
                    $ORDER[$rParamVal] = $user['NAME']." ".$user['LAST_NAME'];
                }
            } else if (in_array($rParamKey, array('DURATION', 'PERSONAL_PHONE', 'PERSONAL_STREET', 'TIME', 'DATE'))) {
                $ORDER[$rParamVal] = isset($orderProps[$rParamKey]) ? $orderProps[$rParamKey]['VALUE'] : '';
            } else if (mb_ereg('^func\(([^\)]+?)\)', $rParamKey, $matchFunc)) {
                $ORDER[$rParamVal] = $matchFunc[1];
                $funcParams[] = $rParamVal;
            } else {
                $ORDER[$rParamVal] = isset($arSales[$rParamKey]) ? $arSales[$rParamKey] : '';
            }
        }

        foreach ($funcParams as $paramName) {
            foreach ($ORDER as $pKey => $pVal) {
                $ORDER[$paramName] = mb_ereg_replace('\%' . $pKey . '\%', $pVal, $ORDER[$paramName]);
            }
            eval('$ORDER[$paramName] =' . $ORDER[$paramName] . ';');
        }
        $ORDERS[] = $ORDER;
    }
    ?>

    <h1>Заказы с <?=$_GET["from"];?></h1>
    <table class="sms_table">
        <tr>
            <th>ID</th>
            <th>Дата создания</th>
            <th>Статус</th>
            <th>Дата заказа</th>
            <th>Время заказа</th>
            <th>Клинер</th>
            <th>Продолжи-<br>тельность</th>
            <th>Сумма</th>
            <th>З/п клинера</th>
            <th>Имя клиента</th>
            <th>Адрес клиента</th>
            <th>Город клиента</th>
            <th>Email клиента</th>
            <th width=120>Телефон клиента</th>
            <th>Смс клиенту</th>
        </tr>
        <?foreach ($ORDERS as $arOrder){?>
            <tr>
                <td><?=$arOrder["ID"]?></td>
                <td style="text-align:center;"><?=$arOrder["Дата создания"]?></td>
                <td style="text-align:center;">
                    <?
                        switch ($arOrder["Статус"]) {
                            case "A":
                                echo "Подтвержден";
                                break;
                            case "C":
                                echo "Отменен пользователем";
                                break;
                            case "F":
                                echo "Выполнен";
                                break;                            
                            case "M":
                                echo "Отменен менеджером";
                                break;                           
                            case "N":
                                echo "Новый";
                                break;
                        }
                    ?>
                </td>
                <td style="text-align:center;"><?=$arOrder["Дата"]?></td>
                <td style="text-align:center;"><?=$arOrder["Время"]?>:00</td>
                <td><?=$arOrder["Cleaner"]?></td>
                <td style="text-align:center;"><?=$arOrder["Продолжительность"]?> ч.</td>
                <td style="text-align:center;"><?=$arOrder["Сумма"]?>&nbsp;&#8381;</td>
                <td style="text-align:center;"><?=$arOrder["З/П Клинера"]?>&nbsp;&#8381;</td>
                <td><?=$arOrder["Ваше имя"]?></td>
                <td><?=$arOrder["Адрес"]?></td>
                <td style="text-align:center;"><?=$arOrder["Город"]?></td>
                <td><a href="mailto:<?=$arOrder["Email покупателя"]?>"><?=$arOrder["Email покупателя"]?></a></td>
                <td style="text-align:center;"><?=$arOrder["Телефон"]?></td>
                <td>
                    <form action="" method="post" class="sendSmsForm id<?=$arOrder["ID"]?>">
                        <input type="hidden" name="phone" value="<?=$arOrder["Телефон"]?>">
                        <textarea name="text" placeholder="текст сообщения"></textarea>
                        <input type="submit" name="submit" value="Отправить смс">
                    </form>
                </td>
            </tr>

        <?}?>
    </table>

    <?
}
?>

    <div>
        <h2>Получить заказы за период:</h2>
        <form method="get">
            <input type="hidden" name="getAnaliticsContacts" value="1"/>
            <label>От <input class="date" name="from"/></label>
            <!--            <label>До <input class="date" name="to"/></label>-->
            <input type="submit" value="Получить"/>
        </form>
    </div>
    <script>
        $(".date").datepicker({
            dateFormat : "dd.mm.yy"
        });
    </script>
    </body>
    </html>


    <?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>