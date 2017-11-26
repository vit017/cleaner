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
    $requiredParams = array(
        'ID' => 'ID',
        'DATE_INSERT' => 'Дата создания',
        'DATE_UPDATE' => 'Дата',

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

        'USER_LOGIN' => 'Email покупателя',
        'USER_ID' => 'Покупатель',

        'PERSONAL_CITY' => 'Город',
//    '???' => 'Оценка по Клинеру',
//    '???' => 'Отзыв по клинеру ',
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

//        $orderID = '9467';
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
                    $ORDER[$rParamVal] = $user['NAME'];
                }
            } else if (in_array($rParamKey, array('DURATION', 'PERSONAL_PHONE', 'PERSONAL_STREET', 'TIME'))) {
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
//    echo phpinfo();die;
    if (ini_get('mbstring.func_overload') != 2) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel.php';
        $objPHPExcel = new PHPExcel();
        $title = 'Заказы';
        $title .= $FROM ? ' от ' . $FROM : '';
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
        $fileName .= $FROM ? '_' . $FROM : '';
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
<?