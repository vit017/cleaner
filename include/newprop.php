<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 11.11.2016
 * Time: 16:42
 */
if ($_GET['pass'] != '234567892315dsFD') die;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
turnOnDebug();
CModule::IncludeModule('sale');
/*
PERSON_TYPE_ID - тип плательщика;
NAME - название свойства (тип плательщика зависит от сайта, а сайт - от языка; название должно быть на соответствующем языке);
TYPE - тип свойства. Допустимые значения:
CHECKBOX - флаг;
TEXT - строка текста;
SELECT - выпадающий список значений;
MULTISELECT - список со множественным выбором;
TEXTAREA - многострочный текст;
LOCATION - местоположение;
RADIO - переключатель.
REQUIED - флаг (Y/N) обязательное ли поле;
DEFAULT_VALUE - значение по умолчанию;
SORT - индекс сортировки;
USER_PROPS - флаг (Y/N) входит ли это свойство в профиль покупателя;
IS_LOCATION - флаг (Y/N) использовать ли значение свойства как местоположение покупателя для расчёта стоимости доставки (только для свойств типа LOCATION);
PROPS_GROUP_ID - код группы свойств;
SIZE1 - ширина поля (размер по горизонтали);
SIZE2 - высота поля (размер по вертикали);
DESCRIPTION - описание свойства;
IS_EMAIL - флаг (Y/N) использовать ли значение свойства как E-Mail покупателя;
IS_PROFILE_NAME - флаг (Y/N) использовать ли значение свойства как название профиля покупателя;
IS_PAYER - флаг (Y/N) использовать ли значение свойства как имя плательщика;
IS_LOCATION4TAX - флаг (Y/N) использовать ли значение свойства как местоположение покупателя для расчёта налогов (только для свойств типа LOCATION);
CODE - символьный код свойства.
IS_FILTERED - свойство доступно в фильтре по заказам. С версии 10.0.
IS_ZIP - использовать как почтовый индекс. С версии 10.0.
UTIL - позволяет использовать свойство только в административной части. С версии 11.0.
*/
$arFields = array(
    "NAME" => 'Касса',
    "CODE" => 'CASSA',
    "PERSON_TYPE_ID" => 2,
    "PROPS_GROUP_ID" => 1,
    "TYPE" => "RADIO",
    "VARIANTS" => array(
        'Да' => 'Y',
        'Нет' => 'N'
    ),
    "REQUIED" => "N",
    "DEFAULT_VALUE" => "",
    "SORT" => 100,
    "USER_PROPS" => "N",
    "IS_LOCATION" => "N",
    "IS_LOCATION4TAX" => "N",
    "SIZE1" => 0,
    "SIZE2" => 0,
    "DESCRIPTION" => "",
    "IS_EMAIL" => "N",
    "IS_PROFILE_NAME" => "N",
    "IS_PAYER" => "N",
    'UTIL' => "Y"
);

$db_props = CSaleOrderProps::GetList(
    array("SORT" => "ASC"),
    array(
        'NAME' => $arFields['NAME']
    ),
    false,
    false,
    array()
);
$props = array();
while($prop = $db_props->fetch()) {
    $props[] = $prop;
}
//foreach ($props as $prop) {
//    deb($prop['ID'], CSaleOrderProps::Delete($prop['ID']));
//}
if (isset($arFields['VARIANTS']) && $arFields['VARIANTS']) {
    $VARIANTS = array();
    $SORT = 100;
    foreach ($arFields['VARIANTS'] as $vName => $vVal) {
        $SORT++;
        $VARIANTS[] = array(
                "VALUE" => $vVal,
                "NAME" => $vName,
                "SORT" => $SORT,
                "DESCRIPTION" => ""
        );
    }
}
$addVariant = function($id, $var) {
    $var['ORDER_PROPS_ID'] = $id;
    deb('add variant', $var['NAME'], CSaleOrderPropsVariant::Add($var));
};
if (!count($props)) {
    $db_ptype = CSalePersonType::GetList(Array("SORT" => "ASC"), Array("LID"=>SITE_ID));
    $groupID = 0;
    while ($ptype = $db_ptype->fetch()) {
        $groupID++;
        $arFields['PERSON_TYPE_ID'] = $ptype['ID'];
        $arFields['PROPS_GROUP_ID'] = $groupID;
        $pID = CSaleOrderProps::Add($arFields);
        deb('new prop id', $pID);
        if (in_array($arFields['TYPE'], array('RADIO', 'SELECT', 'MULTYSELECT')) && isset($VARIANTS)) foreach ($VARIANTS as $var) {
            $addVariant($pID, $var);
        }

    }
} else {
    foreach ($props as $prop) {
        deb('result prop update', CSaleOrderProps::Update($prop['ID'], $arFields));
        CSaleOrderPropsVariant::DeleteAll($prop['ID']);
        if (in_array($arFields['TYPE'], array('RADIO', 'SELECT', 'MULTYSELECT')) && isset($VARIANTS)) foreach ($VARIANTS as $var) {
            $addVariant($prop['ID'], $var);
        }
    }
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");