<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");
CModule::IncludeModule("sale");
CModule::IncludeModule("catalog");
?>


<html>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
</head>
<body>
    <div>
        <h2>Получить заказы за период:</h2>
        <form method="get">
            <label>От <input class="date" name="from" value="<?=$_GET['from']?>"></label>
            <label>До <input class="date" name="to" value="<?=$_GET['to']?>"></label>
            <br><br>
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

<?

if ($_GET["from"] || $_GET["to"]){
    $arFilter=array();
    if ($_GET["from"])
        $arFilter[">=DATE_INSERT"]=$_GET["from"];

    if ($_GET["to"])
        $arFilter["<=DATE_INSERT"]=$_GET["to"];

    $res=array();
    $i=0;
    $db_sales = CSaleOrder::GetList(array("ID" => "ASC"), $arFilter,false, false, array("PROPERTY_VAL_BY_CODE_DATE", "ID", "USER_ID", "PRICE", "DATE_UPDATE", "DATE_INSERT", "STATUS_ID"));
    while ($ar_sales = $db_sales->Fetch()){
        $rsUser = CUser::GetByID($ar_sales["USER_ID"]);
        $arUser = $rsUser->Fetch();
        if ($arUser["UF_MNOGORU"]){
            $dateInsert = new DateTime($ar_sales["DATE_INSERT"]);
            $dateUpdate = new DateTime($ar_sales["DATE_UPDATE"]);
            $res[$i]["DATE_INSERT"]=$dateInsert->format('Y-m-d');
            $res[$i]["DATE_UPDATE"]=$dateUpdate->format('Y-m-d');
            $res[$i]["ID"]=$ar_sales["ID"];
            $res[$i]["MNOGORU"]=$arUser["UF_MNOGORU"];
            $res[$i]["PRICE"]=$ar_sales["PRICE"];
            if ($ar_sales["STATUS_ID"]=="A" || $ar_sales["STATUS_ID"]=="N"){
                $res[$i]["ACTION"]="create";
                $res[$i]["STATUS"]="waiting";
            }elseif ($ar_sales["STATUS_ID"]=="C" || $ar_sales["STATUS_ID"]=="M"){
                $res[$i]["ACTION"]="reject";
                $res[$i]["STATUS"]="rejected";     
            }elseif ($ar_sales["STATUS_ID"]=="F"){
                $res[$i]["ACTION"]="approve";
                $res[$i]["STATUS"]="approved";
            }
            $i++;
        }
    }

    $content.='<?xml version="1.0" encoding="windows-1251"?>
    ';
    $content.='<batch>
    ';
    $content.='<enterpriseGroup enterpriseId="2251">
    ';
    $content.='<accountGroup accountId="3037022">
    ';
    foreach ($res as $arResult){
        $content.='<receipt number="'.$arResult['ID'].'" date="'.$arResult['DATE_UPDATE'].'" card="'.$arResult['MNOGORU'].'" action="'.$arResult['ACTION'].'" status="'.$arResult['STATUS'].'">
        ';
        $content.='<entry name="orderDate" value="'.$arResult['DATE_INSERT'].'" />
        ';
        $content.='<entry name="sum" value="'.$arResult['PRICE'].'" />
        ';
        $content.='</receipt>
        ';
    }
    $content.='</accountGroup>
    ';
    $content.='</enterpriseGroup>
    ';
    $content.='</batch>';


    echo "<p>Генерация xml закончена!</p>";
    echo "<p>Что бы получить xml, перейдите по <a href='mnogoruperiod.xml' target='_blank'>ссылке</a></p>";
    file_put_contents('mnogoruperiod.xml', $content);
}
?>








<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>