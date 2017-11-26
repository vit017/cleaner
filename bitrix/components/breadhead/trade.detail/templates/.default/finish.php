<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 13.04.15
 * Time: 12:54
 */
?>
<div class="page-blocks__item page-blocks__item_type_main">
    <div class="page-blocks__item-controls">
        <a class="btn btn_type_third btn_size_small" href="/cleaners/my/"><span class="btn__icon btn__icon_type_back"></span>Все уборки</a>
    </div>
    <form class="settings-form" method="post" name="flat"  enctype="multipart/form-data">
        <input type="hidden" name="order_update" value="Y">
        <input type="hidden" name="submit" value="Y">
        <input type="hidden" name="ORDER_ID" value="<?=$arResult['ORDER_ID']?>">
        <input type="hidden" name="SAVED_VERSION" value='<?=$arResult['SAVED_VERSION']?>'>
        <h2 class="settings-form__title">Подтверди параметры уборки</h2>
        <h3 class="settings-form__title">Площадь квартиры</h3>
        <div class="single-input clearfix">
            <label class="single-input__control">
                <select name="PRODUCT[]" class="select select_width_full js-custom-select js-update-basketForm select_flat select_flat_nomargin">
                    <?foreach($arResult['ITEMS']['MAIN'] as $item){
                        if ( $item['PROPERTIES']['SERVICE']['VALUE'] > 0 )
                            continue?>
                        <option value="<?=$item['ID']?>" <?=$item['QUANTITY']>0?'selected':''?>><?=$item['NAME_FORMATED']?>м&#178;  (<?=$item['PROPERTIES']['DURATION']['VALUE']/60?>ч)  </option>
                    <?}?>
                </select>
            </label>
        </div>
        <h3 class="order-form__fieldset-title">Дополнительно</h3>
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
                            <input type="hidden"  class="js-input-qnt js-update-basketForm"  data-step="<?=strlen($item['PROPERTIES']['STEP']['VALUE'])>0?$item['PROPERTIES']['STEP']['VALUE']:1?>" name="QUANTITY_<?=$item['ID']?>"  value="<?=$item['QUANTITY']>0?$item['QUANTITY']:$item['PROPERTIES']['DEFAULT']['VALUE']?>">
                            <span class="qnt-button js-plus"></span>
                        </div>
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
        <?/*?><div class="time-input clearfix">
            <h3 class="order-form__fieldset-title">Дополнительное время</h3>
            <label class="time-input__control">
                <select class="select select_width_full select_search_false js-custom-select js-update-basketForm" name="PRODUCT[]">
                    <?foreach($arResult['ITEMS']['SERVICES'] as $item){?>
                        <option value="0" >0 минут</option>
                        <option value="<?=$item['ID']?>" <?=$item['QUANTITY']>0?'selected':''?>><?=$item['NAME_FORMATED']?></option>
                    <?}?>
                </select>
            </label>
        </div>
        <?*/?>
        <?$props = $arResult['PROPS_FORMATED'];?>
        <div class="total cleaner-total">
            <p class="total__item clearfix">
                <span class="pull-left">Уборка <span style="color:#898989;">(<?=$props['DURATION']['VALUE_FORMATED']?>)</span></span><span class="pull-right"><?=$arResult['SUMMARY']['BASKET_PRICE_FORMATED']?> <span class="rouble">Р</span></span>
            </p>
            <?if ( $arResult['SUMMARY']['DISCOUNT_PRICE'] > 0 ){?>
                <p class="total__item clearfix">
                    <span class="pull-left">Скидка</span><span class="pull-right"><?=$arResult['SUMMARY']['DISCOUNT_PRICE_FORMATED']?> <span class="rouble">Р</span></span>
                </p>
            <?}?>
            <?if ( $arResult['SUMMARY']['SUM_PAID'] > 0 ){?>
                <p class="total__item clearfix">
                    <span class="pull-left">Уже оплачено</span><span class="pull-right"><?=$arResult['SUMMARY']['SUM_PAID_FORMATED']?> <span class="rouble">Р</span></span>
                </p>
            <?}?>
            <?if ( $arResult['SUMMARY']['REWARD'] > 0 ){?>
                <p class="total__item clearfix">
                    <span class="pull-left">Вознаграждение</span><span class="pull-right"><?=$arResult['SUMMARY']['REWARD_FORMATED']?> <span class="rouble">Р</span></span>
                </p>
            <?}?>
            <p class="total__item total__item_summary clearfix">
                <span class="pull-left">Итого</span><span class="pull-right"><?=$arResult['SUMMARY']['NEED_TO_PAY_FORMATED']?> <span class="rouble">Р</span></span>
            </p>
            <span class="grey pull-right" style="text-transform: lowercase;"><?=$arResult['SUMMARY']['PAYMENT']?></span>

        </div>
    </form>
        <span class="btn js-cleaner-action" data-action="finish"  data-order="<?=$arResult['ID']?>" data-cleaner="<?=$arResult['CLEANER_ID']?>">
            Выполнен
        </span>

</div>