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
    <div class="order-detail__content ">
        <div class="order-item">
            <?$props = $arResult['PROPS_FORMATED'];?>
            <h4 class="order-item__title cleaner-title ">Заказ №<?=$arResult['ID']?></h4>
            <p class="order-item__param"><?=$props['DATE']['VALUE_FORMATED']?></p>
            <p class="order-item__param"><?=$props['TIME']['VALUE_FORMATED']?> (<?=$props['DURATION']['VALUE_FORMATED']?>)</p>
            <p class="order-item__param"><span class="order-item__param-name">Адрес:</span> <?=$props['PERSONAL_STREET']['VALUE_FORMATED']?></p>
            <p class="order-item__param"></p>
            <p class="order-item__param"></p>
            <div class="order-detail">
                <h4 class="order-item__title order-item__title_params">Параметры квартиры</h4>
                <p class="order-item__param">
                    <?foreach ($arResult['ITEMS']['MAIN'] as $service){
                        if ( $service['QUANTITY'] > 0 ){?>
                            <?=$service['NAME_FORMATED'].'м&#178;'?>
                        <?}?>
                    <?}?>
                </p>

                <?$additional_line = bhTools::makeAddLine($arResult['ITEMS']['ADDITIONAL']);
                if ( strlen($additional_line) > 0 ){?>
                    <p class="order-item__param"><span class="order-item__param-name">Дополнительно:</span>
                        <?=$additional_line?>
                    </p>
                <?}?>
                <p class="order-item__param"></p>
                <h4 class="order-item__title order-item__title_contacts">Контактные данные</h4>
                <p class="order-item__param"><span class="order-item__param-name">Телефон: </span><?=$props['PERSONAL_PHONE']['VALUE_FORMATED']?></p>
                <p class="order-item__param"><span class="order-item__param-name">Имя: </span><?=$props['NAME']['VALUE_FORMATED']?></p>
                <?if ( strlen($arResult['USER_DESCRIPTION']) > 0 ){?>
                    <p class="order-item__param"><span class="order-item__param-name">Комментарий: </span><?=$arResult['USER_DESCRIPTION']?></p>
                <?}?>
                <p class="order-item__param"></p>

                <h4 class="order-item__title order-item__title_price">Стоимость</h4>
                <p class="order-item__param"> <?=$arResult['SUMMARY']['BASKET_PRICE_FORMATED']?> <span class="rouble">Р</span> за <?=$props['DURATION']['VALUE_FORMATED']?><?if ( $arResult['SUMMARY']['ORDER_PRICE'] == $arResult['SUMMARY']['BASKET_PRICE'] ){?> <span class="grey" style="text-transform: lowercase;"><?=$arResult['SUMMARY']['PAYMENT']?><?}?></p>
                <?if ( $arResult['SUMMARY']['DISCOUNT_PRICE'] > 0 ){?>
                    <p class="order-item__param">Скидка: <?=$arResult['SUMMARY']['DISCOUNT_PRICE_FORMATED']?> <span class="rouble">Р</span></p>
                <?}?>
                <?if ( $arResult['SUMMARY']['SUM_PAID'] > 0 ){?>
                    <p class="order-item__param">Уже оплачено: <?=$arResult['SUMMARY']['SUM_PAID_FORMATED']?> <span class="rouble">Р</span></p>
                <?}?>
                <?if ( $arResult['SUMMARY']['ORDER_PRICE'] <> $arResult['SUMMARY']['BASKET_PRICE'] ){?>
                    <p class="order-item__param">Итого: <?=$arResult['SUMMARY']['NEED_TO_PAY_FORMATED']?> <span class="rouble">Р</span>
                        <span class="grey" style="text-transform: lowercase;"><?=$arResult['SUMMARY']['PAYMENT']?></span>
                    </p>
                <?}?>

                <?if ( $arResult['SUMMARY']['REWARD'] > 0 ){?>
                    <p class="order-item__param">Вознаграждение: <?=$arResult['SUMMARY']['REWARD_FORMATED']?> <span class="rouble">Р</span></p>
                <?}?>
            </div>
        </div>
    </div>
</div>