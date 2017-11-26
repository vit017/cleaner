<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 07.04.14
 * Time: 15:41
 */
if (!CModule::IncludeModule("iblock"))
{
    ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALL"));
    return;
}
$arResult = array();
$arResult['SEGMENT'] = $arParams['SEGMENT']?$arParams['SEGMENT']:'SUBSCRIBE';
$arResult['EMAIL'] = $arParams['EMAIL']?$arParams['EMAIL']:'';
$arResult['NAME'] = $arResult['EMAIL'];
if(isset($arParams['MAILCHIMP_LIST_ID']) && $arParams['MAILCHIMP_LIST_ID']!=''){
    $mcListID = trim($arParams['MAILCHIMP_LIST_ID']);
}else
    $mcListID = bhSettings::$mailChimpNewsList;

if ( $_REQUEST['subscribe'] ){
    if(!strlen($arResult['EMAIL'])){
        $arResult['EMAIL'] = $_REQUEST['EMAIL'];
    }

    if ( check_email($arResult['EMAIL']) ){
        $iblock = intVal(bhSettings::$IBlock_subscribe);
        $res = CIBlockElement::getList(array(),array('IBLOCK_ID'=>$iblock,'NAME'=>$arResult['EMAIL'], 'SECTION_CODE'=>$mcListID),false,false,array());
        if ( !$res->fetch() ){
            $mailChimp = new bhMailChimp( bhSettings::$mailChimpKey );
            if ( $mailChimp->subscribe( array(
                'email'=>$arResult['EMAIL'],
                'name'=>$arResult['NAME'],
                'list'=>$mcListID) ) ){
                $arrSections = array();
                $db_list = CIBlockSection::GetList(Array($by=>$order), array('IBLOCK_ID'=>$iblock, 'ACTIVE'=>'Y'), false, array('ID', 'CODE'));
                while($arSect = $db_list->fetch()){
                    $arrSections[$arSect['CODE']] = $arSect['ID'];
                }
                $add = new CIBlockElement();
                $fields = array(
                    'IBLOCK_ID' => $iblock,
                    'NAME' => $arResult['EMAIL'],
                    'DETAIL_TEXT' => $arResult['NAME'],
                    'IBLOCK_SECTION_ID' => $arrSections[$mcListID]
                );
                if ( $add->add($fields) ){
                    $arResult['msg'] = 'Вы подписаны на рассылку';
                }else{
                    $arResult['ERROR']['FORM']['msg'] = $add->LAST_ERROR;//'Внутренная ошибка, попробуйте позже';
                }
            }else{
                $arResult['ERROR']['FORM']['msg'] = 'Внутренная ошибка, попробуйте позже';
            }
        }else{
            $arResult['ERROR']['FORM']['msg'] = 'Ваш email мы уже записали. Спасибо!';
        }
    }else{

        $arResult['ERROR']['FORM']['msg'] = 'Это не похоже на email. Попробуйте еще раз';
    }

    if(count($arResult['ERROR'])<=0){
        $arResult['OK'] = 'Y';
    }
}
/*
if ( $result ){
    echo json_encode($result);
}*/
$arResult['TARGET'] = $arParams['TARGET']?$arParams['TARGET']:'#subscribe';

$this->IncludeComponentTemplate();