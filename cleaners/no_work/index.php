<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Не рабочие даты");

$dval='';

global $USER;
if($USER->GetID()>0) {
    $uid = $USER->GetID();
    $is_here = false;

    CModule::IncludeModule('iblock');
    //смотрим, есть ли уже запись
    $arSelect = Array("ID", "PROPERTY_DATES");
    $arFilter = Array("IBLOCK_ID" => 10, "ACTIVE" => "Y", "PROPERTY_CLEANER" => $uid);
    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
    if ($ob = $res->GetNext(false, false)) {
        $is_here = $ob;
        $dval=$ob['PROPERTY_DATES_VALUE'];
    }


    if (isset($_POST['set_dates']) && isset($_POST['dates'])) {
        $PROP = array();

        //смотрим, есть ли уже запись
        if ($is_here) {
            $ELEMENT_ID = $is_here['ID'];  // код элемента
            $PROPERTY_CODE = "DATES";  // код свойства
            $PROPERTY_VALUE = $_POST['dates'];  // значение свойства
            if (CIBlockElement::SetPropertyValues($ELEMENT_ID, 10, $PROPERTY_VALUE, $PROPERTY_CODE) == null) {
                echo "Запись обновлена";
                LocalRedirect($APPLICATION->GetCurUri());
            } else {
                echo "Запись НЕ обновлена";
            }
        } else {
            $el = new CIBlockElement;

            $PROP[61] = $uid;
            $PROP[62] = $_POST['dates'];

            $arLoadProductArray = Array(
                "MODIFIED_BY" => $uid, // элемент изменен текущим пользователем
                "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
                "IBLOCK_ID" => 10,
                "PROPERTY_VALUES" => $PROP,
                "NAME" => $USER->GetFullName(),
                "ACTIVE" => "Y"
            );

            if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                echo "Запись добавлена ID: " . $PRODUCT_ID;
                LocalRedirect($APPLICATION->GetCurUri());
            } else {
                echo "Ошибка: " . $el->LAST_ERROR;
            }
        }

    }
}
?>

<style>
    header.page-header{padding-bottom:0;margin:0;border:0}
</style>

<div class="container">
    <h1>В какие даты вы не можете работать?</h1>

    <section class="page-blocks clearfix">
        <div class="page-block order__content cleaner-block page-blocks__item_type_main">
            <form method="post">
                <input name="set_dates" type="hidden">
                <div class="span5" id="sandbox-container">
                    <div class="input-group date">
                        <input type="text" class="form-control" value="<?=$dval?>" name="dates" readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <input type="submit" class="btn btn-default" value="Сохранить">
            </form>
        </div>
        <div class="page-blocks__item page-blocks__item_type_aside">
            <?$APPLICATION->IncludeComponent("bitrix:menu", "aside", array(
                "ROOT_MENU_TYPE" => "left",
                "MENU_CACHE_TYPE" => "N",
                "MENU_CACHE_TIME" => "3600",
                "MENU_CACHE_USE_GROUPS" => "Y",
                "MENU_CACHE_GET_VARS" => array(
                ),
                "MAX_LEVEL" => "1",
                "CHILD_MENU_TYPE" => "",
                "USE_EXT" => "N",
                "DELAY" => "N",
                "ALLOW_MULTI_SELECT" => "N"
            ),
                false
            );?>
        </div>
    </section>
</div>

<script src="/include/bootstrap-datepicker-1.6.4-dist/js/jquery.min.js"></script>
<link id="bs-css" href="/include/bootstrap-datepicker-1.6.4-dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/include/bootstrap-datepicker-1.6.4-dist/css/bootstrap-datepicker3.css">
<script src="/include/bootstrap-datepicker-1.6.4-dist/js/bootstrap-datepicker.min.js"></script>
<script src="/include/bootstrap-datepicker-1.6.4-dist/locales/bootstrap-datepicker.ru.min.js" charset="UTF-8"></script>

<script>
    var date = new Date();
    date.setDate(date.getDate()+1);
    $(function() {
        $( "#sandbox-container .input-group.date" ).datepicker({
            clearBtn: true,
            todayHighlight: true,
            language: "ru",
            multidate: true,
            daysOfWeekHighlighted: "0,6",
            startDate: date
        });
    });
</script>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>