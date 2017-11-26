<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 15.05.14
 * Time: 12:11
 */
define('NEED_AUTH', true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog.php");
if (!CModule::IncludeModule("iblock"))
{
    ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALL"));
    return;
}

$dateTime = new DateTime('01.10.2015');

//$dateTime = new DateTime('tomorrow');
$today = new DateTime('05.01.2015');
//$today = $today->modify('');
$date = array();
while($today<$dateTime){
    $date[] = $today->format('d.m.Y');
    $month_code[] = $today->format('nY');
    $today = $today->modify('tomorrow');
}
$months = bhTools::months();
$today = new DateTime();
$month = array();
while($today < $dateTime){

    $name = $months[$today->format('n')].' '.$today->format('Y');
    $code = $today->format('n').$today->format('Y');
    $month[] = array('NAME'=>$name, 'CODE'=>$code);
    $today = $today->modify('next month');

}

$codes = array();
$bs = new CIBlockSection;
foreach($month as $m){
    $arFields = $m;
    $arFields["IBLOCK_ID"] = 3;
    $db_section = CIBlockSection::GetList(array(), $arFields);
    if($sect = $db_section->Fetch()){
        $codes[$arFields['CODE']] = $sect['ID'];
        continue;
        $bs->Update($sect['ID'], $arFields);
    }else{
        $bs->Add($arFields);
    }
}

$db_el = CIBlockProperty::GetList(Array(),array('IBLOCK_ID' => 3));
$el = new CIBlockElement();
$time = array();
while($arProps = $db_el->Fetch()){
    if($arProps['PROPERTY_TYPE'] == 'L'){
        $db_list = CIBlockProperty::GetPropertyEnum($arProps['ID'], array('SORT'=>'asc'));
        while($arList = $db_list->Fetch()){
            $time[] = $arList;
        }
    }
}
//$codes['52015'] = 28;
$j = 0;
foreach($date as $i=>$d){
    foreach($time as $t){
        $j++;
        $arFields = array('NAME'=>$d.' '.$t['VALUE'].':00',
            'IBLOCK_ID' => bhSettings::$IBlock_calendar,
            'CODE'=>$d.'_'.$t['VALUE'], 'IBLOCK_ID' => 3,
            'PROPERTY_VALUES'=>array('DATE'=>$d, 'TIME'=>$t['ID']));

        $res = CIBlockElement::GetList(array(), array('CODE'=>$d.'_'.$t['VALUE'], 'IBLOCK_ID'=>3));
        $code = $month_code[$i];
        $arFields['IBLOCK_SECTION_ID'] = $codes[$code];
        $arFields['SORT'] = $j;
        if($ar_res = $res->GetNext()){
            /*if($ar_res['IBLOCK_SECTION_ID']>0)
                continue;
            else*/
                $el->Update($ar_res['ID'], $arFields);
        }else{
            $el->Add($arFields);
        }

    }
}
