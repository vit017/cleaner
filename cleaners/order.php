<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 02.04.15
 * Time: 13:52
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Заказ уборки');
?>
<div class="container">

    <h1 class="page-title">Заказ уборки</h1>
    <section class="order">
        <header class="order__header clearfix">
            <span class="order__header-item order__header-item_current" data-step="1">Параметры квартиры</span>
            <span class="order__header-item" data-step="2">Дата и время</span>
            <span class="order__header-item" data-step="3">Ваши данные</span>
            <span class="order__header-item" data-step="4">Проверка заказа</span>
            <span class="order__header-item" data-step="5">Оплата</span>
        </header>
        <div class="page-block order__content">
            <form class="settings-form" method="post" name="flat"  enctype="multipart/form-data">
                <h2 class="settings-form__title">Параметры квартиры</h2>
                <h3 class="settings-form__title">Площадь квартиры</h3>
                <div class="single-input clearfix">
                    <label class="single-input__control">
                        <select name="FLAT_SIZE" class="select select_width_full js-custom-select js-update-basketForm select_flat select_flat_nomargin">
                            <option value="1">до 45м²  (2.5ч) </option>
                            <option selected value="2">до 60м² (3.5ч) </option>
                            <option value="3">до 85м²  (4.5ч) </option>
                        </select>
                    </label>
                </div>
                <h3 class="order-form__fieldset-title">Дополнительно</h3>
                <div class="additional-control">
                    <span class="additional-control__item">
                        <label class="additional-control__item-content">
                            <input class="additional-control__item-input" name="SERVICES[1][1]" type="checkbox">
                            <span class="additional-control__item-bg"></span>
                            <span class="additional-control__item-title"><img src="<?=$item['ICON']?>">погладить белье</span>
                        </label>
                    </span>
                    <span class="additional-control__item">
                        <label class="additional-control__item-content">
                            <input class="additional-control__item-input" name="SERVICES[1][1]" type="checkbox" checked>
                            <span class="additional-control__item-bg"></span>
                            <span class="additional-control__item-title"><img src="<?=$item['ICON']?>">помыть окна</span>
                        </label>
                    </span>
                    <span class="additional-control__item">
                        <label class="additional-control__item-content">
                            <input class="additional-control__item-input" name="SERVICES[1][1]" type="checkbox">
                            <span class="additional-control__item-bg"></span>
                            <span class="additional-control__item-title"><img src="<?=$item['ICON']?>">погладить белье</span>
                        </label>
                    </span>
                    <span class="additional-control__item">
                        <label class="additional-control__item-content">
                            <input class="additional-control__item-input" name="SERVICES[1][1]" type="checkbox" checked>
                            <span class="additional-control__item-bg"></span>
                            <span class="additional-control__item-title"><img src="<?=$item['ICON']?>">погладить белье</span>
                        </label>
                    </span>
                    <span class="additional-control__item">
                        <label class="additional-control__item-content">
                            <input class="additional-control__item-input" name="SERVICES[1][1]" type="checkbox">
                            <span class="additional-control__item-bg"></span>
                            <span class="additional-control__item-title"><img src="<?=$item['ICON']?>">погладить белье</span>
                        </label>
                    </span>
                </div>
                <div class="time-input clearfix">
                    <h3 class="order-form__fieldset-title">Предпочтительный клинер</h3>
                    <label class="time-input__control">
                        <select class="select select_width_full js-order-time select_cleaner" name="PRODUCT[2][2]" placeholder="Не важно">
                        </select>
                        <div id="result"></div>
                    </label>
                    <span class="time-input__tip" style="line-height: inherit;  float: inherit;">Мы постараемся учесть ваши пожелания, но не гарантируем, что именн выбранный клинер приедет на уборку</span>
                </div>
                <button class="order-form__next btn btn_with_icons btn_responsive_true js-basket-submit" id="select_date" style="margin-top:20px">
                    <span class="btn__icon btn__icon_type_date"></span>Выбрать дату<span class="btn__icon btn__icon_right btn__icon_type_forward"></span>
                </button>
            </form>


        </div>
    </section>
</div>
<style>
</style>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
<script>
    var CLEANERS = [
        { code:"0", name:"не важно"},
        { code:"1", name:"Наталья"},
        { code:"2", name:"Светлана"}];

    $('.js-order-time').selectize({
        maxItems: 1,
        options: CLEANERS,
        labelField: 'name',
        valueField: 'code',
        searchField: ['name', 'code'],
        //allowEmptyOption: true,
        preload: true,
        persist: false,
        render: {
            item: function(item, escape) {
                return "<div><img src='https://gettidy.ru/upload/main/82e/82e91347b81fe506309bb3399d98279e.jpg' class='cleaner-face' />" + escape(item.name) + "</div>";
            },
            option: function(item, escape) {
                return "<div><img src='https://gettidy.ru/upload/main/82e/82e91347b81fe506309bb3399d98279e.jpg' class='cleaner-face' /><span>" + escape(item.name) + "</span></div>";
            }
        },
        onChange: function(value) {
            if (!value.length) return;
            me.abort();
            var form = $('.js-basket-form');
            me.sendForm(form);
        }
    });
</script>