<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 13.02.15
 * Time: 15:52
 */?>
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
            <form class="order-form js-basket-form" name="BASKET_CATALOG" method="POST">
                <?if(!empty($arResult['HIDDEN'])){?>
                    <?foreach($arResult['HIDDEN'] as $hidden){?>
                        <input type="hidden" name="<?=$hidden['NAME']?>" value="<?=$hidden['VALUE']?>">
                    <?}?>
                <?}?>
                <fieldset class="order-form__fieldset">
                    <h2 class="order-form__fieldset-title">Площадь квартиры</h2>
                    <div class="single-input clearfix">
                        <label class="single-input__control">
                            <select name="PRODUCT[]" class="select select_width_full js-custom-select js-update-basketForm select_flat select_flat_nomargin">
                                <?foreach($arResult['ITEMS']['MAIN'] as $item){
                                    if($item['PROPERTIES']['SERVICE']['VALUE']>0)
                                        continue?>
                                    <option value="<?=$item['ID']?>" <?=$item['QUANTITY']>0?'selected':''?>><?=$item['NAME_FORMATED']?>м&#178;</option>
                                <?}?>
                            </select>
                        </label>
                        <!--<span class="single-input__tip">Уборка займет не меньше <strong><?=$arResult['FLAT_TIME']?></strong> часов</span> -->
                    </div>

                    <div class="order-form__fieldset-tip order-form__fieldset-tip_mobile-visible">
                        <h3>Что входит в уборку</h3>
                        <p>
                            Узнайте подробнее об эко-уборке по технологии Clean and Away, разработанной нами.
                        </p>
                        <p class="">
                            <span class="link" data-target="#what-we-clean" data-toggle="modal"><strong>Что входит в уборку</strong></span> <br/>
                            <span class="link" data-target="#clean-tools" data-toggle="modal"><strong>Чем мы убираем</strong></span>
                        </p>
                        <h3>100% гарантия</h3>
                        <p>
                            Если вам не понравится уборка, мы&nbsp;вернёмся и уберём заново!
                        </p>
                    </div>
                </fieldset>

                <fieldset class="order-form__fieldset">
                    <h2 class="order-form__fieldset-title">Дополнительно</h2>
                    <div class="additional-control">
                        <?foreach($arResult['ITEMS']['ADDITIONAL'] as $item){?>
                            <?if ( strlen($item['PROPERTIES']['SET_QUANTITY']['VALUE']) > 0 ){?>
                                <span class="additional-control__item additional-control__item-with-params <?=$item['QUANTITY']>0?'checked':''?>">
                                    <label class="additional-control__item-content">
                                        <input class="additional-control__item-input js-set_qnt js-update-basketForm" name="PRODUCT[]" data-name="<?=$item["CODE"]?>" value="<?=$item['ID']?>" type="checkbox" <?=$item['QUANTITY']>0?'checked':''?>>
                                        <span class="additional-control__item-bg"></span>
                                        <span class="additional-control__item-title "><img src="<?=$item['PROPERTIES']['ICON']['VALUE']?>"><?=$item['NAME_FORMATED']?></span>
                                        <span class="additional-control__item-title__description"><?=$item['PROPERTIES']['SET_NAME']['VALUE']?></span>
                                    </label>
                                    <div class="controls">
                                        <span class="qnt-button js-minus"></span>
                                        <label class="js-quantity"><?=$item['QUANTITY']>0?$item['QUANTITY']:$item['PROPERTIES']['DEFAULT']['VALUE']?></label>
                                        <input type="hidden" class="js-input-qnt js-update-basketForm"  data-step="<?=strlen($item['PROPERTIES']['STEP']['VALUE'])>0?$item['PROPERTIES']['STEP']['VALUE']:1?>" name="QUANTITY_<?=$item['ID']?>"  value="<?=$item['QUANTITY']>0?$item['QUANTITY']:$item['PROPERTIES']['DEFAULT']['VALUE']?>">
                                        <span class="qnt-button js-plus"></span></div>
                                </span>
                            <?} else {?>
                                <span class="additional-control__item">
                                    <label class="additional-control__item-content">
                                        <input class="additional-control__item-input js-update-basketForm" name="PRODUCT[]" data-name="<?=$item["CODE"]?>" value="<?=$item['ID']?>" type="checkbox" <?=$item['QUANTITY']>0?'checked':''?>>
                                        <span class="additional-control__item-bg"></span>
                                        <span class="additional-control__item-title"><img src="<?=$item['PROPERTIES']['ICON']['VALUE']?>"><?=$item['NAME_FORMATED']?></span>
                                    </label>
                                </span>
                            <?}?>
                        <?}?>
                    </div>
                </fieldset>

                <fieldset class="order-form__fieldset">
                    <? if ($arResult['ERROR_MESSAGE']) { ?>
                        <span class="input-error" style="margin: 0 0 30px 0;">
                        <span class="input-error__item">
                            <?=$arResult['ERROR_MESSAGE']?>
                        </span>
                    </span>
                    <? } ?>
                    <div class="total">
                        <?$total_price = isset($arResult['ORDER_PRICE'])?$arResult['ORDER_PRICE_FORMATED']:$arResult['BASKET_PRICE_FORMATED'];
                        ?>
                        <?if ( $arResult['DISCOUNT_PRICE'] > 0 ){?>

                            <p class="total__item clearfix">
                                <span class="pull-left">Уборка <span style="color:#898989;">(&#126;<?=$arResult[$arParams['PROPERTY_DURATION']]['SUMM']?> ч)</span></span><span class="pull-right"><?=$arResult['BASKET_PRICE_FORMATED']?> <span class="rouble">Р</span></span>
                            </p>

                            <p class="total__item clearfix">
                                <span class="pull-left">Скидка</span><span class="pull-right">- <?=$arResult['DISCOUNT_PRICE_FORMATED']?> <span class="rouble">Р</span></span>
                            </p>
                            <p class="total__item total__item_summary clearfix">
                                <span class="pull-left">Итого</span><span class="pull-right"><?=$total_price?> <span class="rouble">Р</span></span>
                            </p>

                        <?} else { ?>
                            <p class="total__item total__item_summary clearfix">
                                <span class="pull-left">Уборка <span style="color:#898989;">(&#126;<?=$arResult[$arParams['PROPERTY_DURATION']]['SUMM']?> ч)</span></span><span class="pull-right"><?=$total_price?> <span class="rouble">Р</span></span>
                            </p>
                        <?}?>
                </fieldset>
                <?if ( $arResult['WISH_CLEANER'] ){?>
                    <input type="hidden" class="js-cleaner-option" data-val='<?=json_encode($arResult['WISH_CLEANER'])?>'>
                    <input type="hidden" name="wishCleaner" value="<?=$arResult['chosen_wish_cleaner']?>">
                    <div class="time-input clearfix">
                        <h3 class="order-form__fieldset-title">Предпочтительный клинер</h3>
                        <label class="time-input__control">
                            <select class="select select_width_full js-select_cleaner select_cleaner" name="WISH_CLEANER" placeholder="Не важно">
                            </select>
                            <div id="result"></div>
                        </label>
                        <span class="time-input__tip" style="line-height: inherit;  float: inherit;">Мы постараемся учесть ваши пожелания, но не гарантируем, что именно выбранный клинер приедет на уборку</span>
                    </div>
                <?}?>
                <button class="order-form__next btn btn_with_icons btn_responsive_true js-basket-submit" id="select_date">
                    <span class="btn__icon btn__icon_type_date"></span><?=$arParams['SUBMIT_TITLE']?><span class="btn__icon btn__icon_right btn__icon_type_forward"></span>
                </button>

            </form>
        </div>
    </section>
</div>
<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/modal-clean-tools.twig') ?>
<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/modal-what-we-clean.twig') ?>


