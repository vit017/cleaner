<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

$count = 0;
if (CModule::IncludeModule("iblock") && !empty($_GET['ID'])) {
    $arSort = Array("name" => "ASC");
    $arSelect = Array("ID", "IBLOCK_ID", "PROPERTY_ORDER_ID", "PROPERTY_STATUS_ORDER");
    $arFilter = Array("IBLOCK_CODE" => 'orders', 'PROPERTY_ORDER_ID' => $_GET['ID']);
    $res = CIBlockElement :: GetList($arSort, $arFilter, false, false, $arSelect);
    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $bx_order_id = $arFields['ID'];
        $bx_iblock_id = $arFields['IBLOCK_ID'];
        $status_order = $arFields['PROPERTY_STATUS_ORDER_VALUE'];
    }

    if ($status_order != $_GET['STATUS_ORDER']) {
        $needToUpdate = array(
            'DRIVER',
            'CAR',
            'PORCH_TIME',
            'TOTAL_PRICE',
            'STATUS_ORDER',
            'STATUS_LABEL',
            'ORDER_INFO'
        );


        foreach ($needToUpdate as $ind => $key) {
            if (!empty($_GET[$key])) {
                if ($key != 'ORDER_INFO') {
                    $count++;
                    CIBlockElement::SetPropertyValues($bx_order_id, $bx_iblock_id, $_GET[$key], $key);
                } else {
                    $el = new CIBlockElement;
                    $el->Update($bx_order_id, array('PREVIEW_TEXT' => $_GET[$key]));
                    $count++;
                }
            }
        }
    }
}
echo $count;
ob_flush();
exit();
