<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

/*
 * При успешной отправке отзыва через  клиент - запишем в БД битрикса этот
 * отзыв через АПИ и отправим на email
 */

function shit_sduwe38328dsj()
{
    /*
     * !!! ХО ХО ХО !!!
     *
     * если подключить битрикс - пролог файл так: и тут
     *
     * require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
     *
     * то битрикс не найдет собственные классы!! , а если выше, то выдаст кучу предупреждений, если уже был где-то подкчючен !
     * поэтому и этот костыль
     */







    if (CModule::IncludeModule("iblock")) {

        $order = new CIBlockElement();

        $bitrixProperties = array(
            'EMAIL' => $_GET['clientPhone'] ? urldecode($_GET['clientPhone']) : '--',
            'RATING' => $_GET['grade'] ? urldecode($_GET['grade']) : '--',
            'NAME' => $_GET['clientName']? urldecode($_GET['clientName']) . '(Отзыв через мобильное прложение)':'Новый отзыв через мобильное приложение ',
            'PREVIEW_TEXT' => $_GET['comment'] ? urldecode($_GET['comment']) : '--',

        );

        $emailProperties = array(
            'NAME' => $_GET['clientName']? urldecode($_GET['clientName']) . '(Отзыв через мобильное прложение)':'Новый отзыв через мобильное приложение ',
            'PHONE' =>  $_GET['clientPhone']? urldecode($_GET['clientPhone']) : '--',
            'TEXT' => $_GET['comment'] ? urldecode($_GET['comment']) : '--',
            'RATING' => $_GET['grade'] ? urldecode($_GET['grade']) : '--',

        );

        $res = CIBlock::GetList(
                     Array(), Array(
                    'SITE_ID'    => SITE_ID,
                    'ACTIVE'     => 'Y',
                    "CNT_ACTIVE" => "Y",
                    "CODE"       => 'reviews',
                        ), true
        );
        $iBlockId = false;
        $ar_res = null;
        while ($ar_res = $res->Fetch()) {
            $iBlockId = $ar_res['ID'];
        }

        $element_id = null;

        if ($iBlockId) {
            $element_id = $order->Add(array(
                'NAME' => $_GET['clientName']? urldecode($_GET['clientName']) . ' (Отзыв через мобильное прложение)':'Новый отзыв через мобильное приложение ',
                'ACTIVE'          => 'N',
                'ACTIVE_FROM'     => date("d.m.Y H:i:s"),
                'IBLOCK_ID'       => $iBlockId,
                'IBLOCK_CODE'     => 'reviews',
                'PROPERTY_VALUES' => $bitrixProperties,
                'PREVIEW_TEXT' => urldecode($_GET['comment']),
            ));
        }

        $db_user = CUser::GetList(($by = 'email'), ($order = 'asc'), array('ID' => 1), array('FIELDS' => array('EMAIL')));
        if ($user = $db_user->Fetch()) {
            $arEventFields = array(
                'EMAIL'              => $user['EMAIL'],
                'DEFAULT_EMAIL_FROM' => $user['EMAIL']
            );

            $adminEmail = COption::GetOptionString('main', 'email_from', 'default@admin.email');
            $arEventFields['SITE_NAME'] = $site['SITE_NAME'];
            $arEventFields['EMAIL'] = $adminEmail;
            $arEventFields['DEFAULT_EMAIL_FROM'] = $adminEmail;
            $arEventFields = array_merge($arEventFields, $emailProperties);
            $arEventFields['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
            try {
                CEvent::Send("REVIEW_POSTED", SITE_ID, $arEventFields);
            } catch (Exception $exc) {
                echo "0";
            }
        }
        echo $element_id;
    }
}
shit_sduwe38328dsj();
ob_flush();
exit();
