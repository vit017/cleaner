<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

/*
 * При успешной отправке заказа через  клиент - запишем в БД битрикса этот
 * заказ через АПИ и отправим на email
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
    $map = array(
        'toCity'      => 'toCity',
        'fromCity'    => 'fromCity',
        'fromStreet'  => 'from_street',
        'toStreet'    => 'to_street',
        'priorTime'   => 'time',
        'fromHouse'   => 'from_house',
        'toHouse'     => 'to_house',
        'fromHousing' => 'from_housing',
        'toHousing'   => 'to_housing',
        'fromPorch'   => 'from_porch',
        'toPorch'     => 'to_porch',
        'clientName'  => 'fio',
        'phone'       => 'phone',
        'customCar'   => 'type_avto',
        'comment'     => 'comment',
    );
    $inParams = array(
        'dop' => '',
    );
    foreach ($map as $paramInCommand => $paramInBitrix) {
        $inParams[$paramInBitrix] = isset($_GET[$paramInCommand]) ? trim($_GET[$paramInCommand]) : null;
        $value = $inParams[$paramInBitrix];
        $value = urldecode($value);
        $inParams[$paramInCommand] = $value;
    }
    foreach ($_GET as $key => $value) {
        $inParams[$key] = urldecode($value);
    }

    if (CModule::IncludeModule("iblock")) {

        $order = new CIBlockElement();

        $dop = array();
        if ($inParams['dop']) {
            $array_dop = explode('-', $inParams['dop']);
            $db_prop_xml = CIBlockPropertyEnum::GetList(false, array('CODE' => 'DOP'));
            foreach ($array_dop as $value) {
                $db_prop_xml = CIBlockPropertyEnum::GetList(false, array('CODE' => 'DOP', 'XML_ID' => $value));
                if ($prop_xml = $db_prop_xml->GetNext()) {
                    $dop[] = array('VALUE' => $prop_xml['ID']);
                }
            }
        }

        $bitrixProperties = array(
            'FROM'         => $inParams['from_street'] ? urldecode($inParams['from_street']) : '---',
            'TO'           => $inParams['to_street'] ? urldecode($inParams['to_street']) : '---',
            'DATA'         => urldecode($inParams['time']),
            'DATE'         => urldecode($inParams['time']),
            'FROM_HOUSE'   => $inParams['from_house'] ? urldecode($inParams['from_house']) : '',
            'TO_HOUSE'     => $inParams['to_house'] ? urldecode($inParams['to_house']) : '',
            'FROM_HOUSING' => urldecode($inParams['from_housing']),
            'TO_HOUSING'   => urldecode($inParams['to_housing']),
            'FROM_PORCH'   => urldecode($inParams['from_porch']),
            'TO_PORCH'     => urldecode($inParams['to_porch']),
            'FIO'          => urldecode($inParams['fio']),
            'TEL'          => preg_replace('~\D~', '', $inParams['phone']) ? preg_replace('~\D~', '', $inParams['phone']) : '---',
            'PHONE'        => preg_replace('~\D~', '', $inParams['phone']) ? preg_replace('~\D~', '', $inParams['phone']) : '---',
            'TIP'          => urldecode($inParams['type_avto']),
            'TYPE_AUTO'    => urldecode($inParams['type_avto']),
            'COMM'         => urldecode($inParams['comment']),
            'COMMENT'      => urldecode($inParams['comment']),
            'DOP'          => urldecode($dop),
            'CITY_OTKUDA'  => urldecode($inParams['fromCity']),
            'OTKUDA'       => urldecode($inParams['fromStreet']) . ' ' . urldecode($inParams['from_house']),
            'CITY_KUDA'    => urldecode($inParams['toCity']),
            'KUDA'         => urldecode($inParams['toStreet']) . ' ' . urldecode($inParams['to_house']),
        );

        $res = CIBlock::GetList(
                        Array(), Array(
                    'SITE_ID'    => SITE_ID,
                    'ACTIVE'     => 'Y',
                    "CNT_ACTIVE" => "Y",
                    "CODE"       => 'orders',
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
                'NAME'            => 'Заказ через мобильное приложение',
                'ACTIVE'          => 'Y',
                'ACTIVE_FROM'     => date("d.m.Y H:i:s"),
                'IBLOCK_ID'       => $iBlockId,
                'IBLOCK_CODE'     => 'orders',
                'PROPERTY_VALUES' => $bitrixProperties
            ));
        }

        $db_user = CUser::GetList(($by = 'email'), ($order = 'asc'), array('ID' => 1), array('FIELDS' => array('EMAIL')));
        if ($user = $db_user->Fetch()) {
            $arEventFields = array(
                'EMAIL'              => $user['EMAIL'],
                'DEFAULT_EMAIL_FROM' => $user['EMAIL']
            );

            $adminEmail = COption::GetOptionString('main', 'email_from', 'default@admin.email');

            $arEventFields['EMAIL'] = $adminEmail;
            $arEventFields['DEFAULT_EMAIL_FROM'] = $adminEmail;
            $arEventFields['SITE_NAME'] = $site['SITE_NAME'];
            $arEventFields['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
            $arEventFields = array_merge($arEventFields, $bitrixProperties);
            try {
                CEvent::Send("NEW_ORDER_TAXI_MOBILE", SITE_ID, $arEventFields);
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

