<?php
/**
 * Created by PhpStorm.
 * User: slame
 * Date: 18.10.2016
 * Time: 13:50
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


function setRow($objPHPExcel, $rowIndex, $dataRow){
    $colNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    $cellIndex = 0;
    foreach ($dataRow as $cellVal) {
        $cell = $colNames[$cellIndex] . $rowIndex;
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell, $cellVal);
        $cellIndex++;
    }
}
CModule::IncludeModule("sale");
CModule::IncludeModule("iblock");
if (isset($_GET['getAnaliticsContacts'])) {
    ob_start();
    $FROM = $_GET['from'];
    $TO = $_GET['to'];
    $STATUS=$_GET['status'];

    $CITY = $_GET['city'];
    $PAYSYSTEM = $_GET['pay_system'];
    $requiredParams = array(
        'ID' => 'ID',
        'DATE_INSERT' => 'Дата создания',
        'DATE' => 'Дата',
        'TIME' => 'Время',
        'PERSONAL_CITY' => 'Город',
        'STATUS_ID' => 'Статус',
        'Cleaner' => 'Клинер',
        'DURATION' => 'Продолжительность',
        'PRICE' => 'Сумма',
        'DISCOUNT' => 'Скидка на заказ',
        'DISCOUNT_PERIOD' => 'Скидка по подписке',
        'func(%Продолжительность% * 350)' => 'З/П Клинера',
        'func(%Сумма%/100*17 - %Скидка на заказ% - %Скидка по подписке%)' => 'Платформа',
        'func(%Сумма% - %З/П Клинера% - %Сумма%/100*17)' => 'УК',
        'PAY_SYSTEM_ID' => 'Платежная система',
        'PAYED' => 'Оплачен',
        //'USER_NAME' => 'Ваше имя',
        'USER_ID' => 'Покупатель',
        'PERSONAL_PHONE' => 'Телефон',
        'USER_LOGIN' => 'Email покупателя',
        'PERSONAL_STREET' => 'Адрес',
        //'???' => 'Оценка по Клинеру',
        //'???' => 'Отзыв по клинеру ',
    );
    $ORDERS = array();
    $arFilter = array();
    if ($FROM) $arFilter['>=DATE_INSERT'] = $FROM;
    if ($TO) $arFilter['<=DATE_INSERT'] = $TO;
    if ($STATUS) $arFilter['STATUS_ID'] = $STATUS;
    if ($PAYSYSTEM) $arFilter['PAY_SYSTEM_ID'] = $PAYSYSTEM;
    if ($CITY) $arFilter['PROPERTY_VAL_BY_CODE_PERSONAL_CITY'] = $CITY;


    $db_sales = CSaleOrder::GetList(array("DATE_INSERT" => "DESC"), $arFilter, false, false, array());
    while ($arSales = $db_sales->Fetch()) {

        $totalPrice=$arSales["PRICE"]+$arSales["DISCOUNT_VALUE"];
        unset($discount);
        unset($discount_period);
        switch ($arSales["PERSON_TYPE_ID"]) {
            case "1":
                $discount = 0;
                break;
            case "2":
                $discount = 350;
                break;
            case "3":
                $discount = 700;
                break;                    
            case "4":
                $discount = 1050;
                break;                    
            case "5":
                $discount = 1400;
                break;            
            case "6":
                $discount = 1750;
                break;            
            case "7":
                $discount = $totalPrice;
                break;
            case "8":
                $discount = $totalPrice*0.05;
                break;
            case "9":
                $discount = $totalPrice*0.1;
                break;                    
            case "10":
                $discount = $totalPrice*0.08;
                break;                    
            case "11":
                $discount = $totalPrice*0.99;
                break;            
            case "12":
                $discount = $totalPrice*0.5;
                break;            
            case "13":
                $discount = $totalPrice*0.15;
                break;
            case "14":
                $discount = 500;
                break;
            case "15":
                $discount = $totalPrice*0.2;
                break;                    
            case "16":
                $discount = $totalPrice*0.3;
                break;                    
            case "17":
                $discount = $totalPrice*0.03;
                break;            
            case "18":
                $discount = $totalPrice*0.01;
                break;
        }

        if ($arSales["DISCOUNT_VALUE"]-$discount>0)
            $discount_period=$arSales["DISCOUNT_VALUE"]-$discount;
        else
            $discount_period=0;


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

        $orderCommentAr = CIblockElement::GetList(array('ID' => 'ASC'), array('IBLOCK_ID' => 5, 'PROPERTY_ORDER_VALUE' => $orderID), false, false, array('ID', 'PREVIEW_TEXT'))->fetch();
        $orCommentMarkAr = CIBlockElement::GetProperty(5, $orderID, array("sort" => "asc"), Array("CODE" => "MARK"))->fetch();

        $ORDER = array();
        $funcParams = array();

        foreach ($requiredParams as $rParamKey => $rParamVal) {
            if ($rParamKey == 'PERSONAL_CITY') {
                $cityID = isset($orderProps[$rParamKey]) ? (int)$orderProps[$rParamKey]['VALUE'] : '';
                if (Cities::$codes[$cityID]=="spb")
                    $ORDER[$rParamVal] = "СПб";
                elseif (Cities::$codes[$cityID]=="msk")
                    $ORDER[$rParamVal] = "Москва";
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
                    $ORDER[$rParamVal] = $user['NAME'];
                }
            } else if (in_array($rParamKey, array('DURATION', 'PERSONAL_PHONE', 'PERSONAL_STREET', 'TIME', 'DATE'))) {
                $ORDER[$rParamVal] = isset($orderProps[$rParamKey]) ? $orderProps[$rParamKey]['VALUE'] : '';
            } else if (in_array($rParamKey, array('STATUS_ID'))) {
                switch ($arSales[$rParamKey]) {
                    case "N":
                        $ORDER[$rParamVal] = "Новый";
                        break;
                    case "A":
                        $ORDER[$rParamVal] = "Подтвержден";
                        break;
                    case "C":
                        $ORDER[$rParamVal] = "Отменен пользователем";
                        break;                    
                    case "F":
                        $ORDER[$rParamVal] = "Выполнен";
                        break;                    
                    case "M":
                        $ORDER[$rParamVal] = "Отменен менеджером";
                        break;
                }
            } else if (mb_ereg('^func\(([^\)]+?)\)', $rParamKey, $matchFunc)) {
                $ORDER[$rParamVal] = $matchFunc[1];
                $funcParams[] = $rParamVal;
            } elseif (in_array($rParamKey, array('PAY_SYSTEM_ID'))) {
                if ($arSales[$rParamKey]==1)
                    $ORDER[$rParamVal] = "Наличными";
                elseif ($arSales[$rParamKey]==2)
                    $ORDER[$rParamVal] = "Картой";                
                elseif ($arSales[$rParamKey]==37)
                    $ORDER[$rParamVal] = "Внутренний счёт";
            }elseif (in_array($rParamKey, array('DISCOUNT'))) {
                $ORDER[$rParamVal] = $discount;
            }elseif (in_array($rParamKey, array('DISCOUNT_PERIOD'))) {
                $ORDER[$rParamVal] = $discount_period;
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
//    echo phpinfo();die;
    if (ini_get('mbstring.func_overload') != 2) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel.php';
        $objPHPExcel = new PHPExcel();
        $title = 'Заказы';
        $title .= $FROM ? ' от ' . $FROM : '';
        $title .= $TO ? ' до ' . $TO : '';
        $objPHPExcel->getProperties()->setCreator("")
            ->setLastModifiedBy("")
            ->setTitle($title)
            ->setSubject($title)
            ->setDescription("")
            ->setKeywords("")
            ->setCategory("");

        $keys = array_keys($ORDERS[0]);
        $objPHPExcel->setActiveSheetIndex(0);
        $row = 1;
        setRow($objPHPExcel, $row, $keys);
        $row++;
        foreach ($ORDERS as $order) {
            setRow($objPHPExcel, $row, $order);
            $row++;
        }

        $fileName = 'orders';
        $fileName .= $FROM ? ' от ' . $FROM : '';
        $fileName .= $TO ? ' до ' . $TO : '';
        $fileName .= '.xls';
        ob_get_clean();
// Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
//    header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    } else {
        ?>

        <html>
        <head>
            <style>
                table {
                    border-collapse: collapse;
                }
                td {
                    border: 1px solid #000000;
                    padding: 0px 3px;
                }
                thead {
                    background-color: #72808d;
                }
                tr:hover {
                    background-color: silver;
                }
            </style>
        </head>
        <body>
            <table>
                <thead>
                 <? foreach (array_keys($ORDERS[0]) as $colName) { ?>
                     <td><?=$colName;?></td>
                 <? } ?>

                 </thead>
                <tbody>
        <?
        foreach ($ORDERS as $order) :
            ?> <tr> <?
            foreach ($order as $kprop => $propValue) :
                ?>
                <td><?=$propValue;?></td>

        <?  endforeach;
            ?> </tr> <?
        endforeach;
        ?>
                </tbody>
            </table>
        <?
    }
    die;
}
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
            <input type="hidden" name="getAnaliticsContacts" value="1"/>
            <label>От <input class="date" name="from"/></label>
            <label>До <input class="date" name="to"/></label>
            <br><br>
            <label>
                Статус заказа 
                <select name="status[]" multiple="">
                    <option value="">Не выбран</option>
                    <option value="N">Новый</option>
                    <option value="A">Подтвержден</option>
                    <option value="F">Выполнен</option>
                    <option value="C">Отменен пользователем</option>
                    <option value="M">Отменен менеджером</option>
                </select>
            </label>
            <br><br>            
            <label>
                Город 
                <select name="city">
                    <option value="">Не выбран</option>
                    <option value="618">Москва</option>
                    <option value="617">Санкт-Петербург</option>
                </select>
            </label>
            <br><br>            
            <label>
                Платежная система 
                <select name="pay_system">
                    <option value="">Не выбрана</option>
                    <option value="1">Наличными</option>
                    <option value="2">Картой</option>
                    <option value="37">Внутренний счёт</option>
                </select>
            </label>
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