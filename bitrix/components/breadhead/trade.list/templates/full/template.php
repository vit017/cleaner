<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 13.04.15
 * Time: 12:54
 */
?>
<div class="page-block order__content cleaner-block page-blocks__item_type_main">
    <div class="order-detail">
        <?foreach($arResult['ORDERS'] as $day){?>
            <div class="order-detail__header clearfix">
                <h2 class="order-detail__title"><?=$day['NAME']?></h2>
            </div>
            <?foreach($day['ORDERS'] as $order){?>
                <?$props = $order['PROPS_FORMATED'];?>
                <div class="order-detail__content ">
                    <div class="order-item js-show_detail">
                        <h4 class="order-item__title cleaner-title ">Заказ №<?=$order['ID']?></h4>
                        <p class="order-item__param"><?=$props['DATE']['VALUE_FORMATED']?></p>
                        <p class="order-item__param"><?=$props['TIME']['VALUE_FORMATED']?> (<?=$props['DURATION']['VALUE_FORMATED']?>)</p>
                        <p class="order-item__param"><span class="order-item__param-name">Адрес:</span> <?=$props['PERSONAL_STREET']['VALUE_FORMATED']?></p>
                        <p class="order-item__param"></p>
                        <p class="order-item__param"></p>
                        <div class="order-detail js-order_detail" style="display: none">
                            <h4 class="order-item__title order-item__title_params">Параметры квартиры</h4>
                            <p class="order-item__param">
                                <?foreach ($order['BASKET']['MAIN'] as $service){?>
                                    <?=$service['NAME_FORMATED'].'м&#178;'?>
                                <?}?>
                            </p>

                            <?$additional_line = bhTools::makeAddLine($order['BASKET']['ADDITIONAL']);
                            if ( strlen($additional_line) > 0 ){?>
                                <p class="order-item__param"><span class="order-item__param-name">Дополнительно:</span>
                                    <?=$additional_line?>
                                </p>
                            <?}?>
                            <p class="order-item__param"></p>
                            <h4 class="order-item__title order-item__title_contacts">Контактные данные</h4>
                            <p class="order-item__param"><span class="order-item__param-name">Телефон: </span><?=$props['PERSONAL_PHONE']['VALUE_FORMATED']?></p>
                            <p class="order-item__param"><span class="order-item__param-name">Имя: </span><?=$props['NAME']['VALUE_FORMATED']?></p>
                            <?if ( strlen($order['USER_DESCRIPTION']) > 0 ){?>
                                <p class="order-item__param"><span class="order-item__param-name">Комментарий: </span><?=$order['USER_DESCRIPTION']?></p>
                            <?}?>
                            <p class="order-item__param"></p>
                            <h4 class="order-item__title order-item__title_price">Стоимость</h4>
                            <p class="order-item__param"> <?=$order['SUMMARY']['BASKET_PRICE_FORMATED']?> <span class="rouble">Р</span> за <?=$props['DURATION']['VALUE_FORMATED']?><?if ( $order['SUMMARY']['ORDER_PRICE'] == $order['SUMMARY']['BASKET_PRICE'] ){?> <span class="grey" style="text-transform: lowercase;"><?=$order['SUMMARY']['PAYMENT']?><?}?></p>
                            <?if ( $order['SUMMARY']['DISCOUNT_PRICE'] > 0 ){?>
                                <p class="order-item__param">Скидка: <?=$order['SUMMARY']['DISCOUNT_PRICE_FORMATED']?> <span class="rouble">Р</span></p>
                            <?}?>
                            <?if ( $order['SUMMARY']['SUM_PAID'] > 0 ){?>
                                <p class="order-item__param">Уже оплачено: <?=$order['SUMMARY']['SUM_PAID_FORMATED']?> <span class="rouble">Р</span></p>
                            <?}?>
                            <?if ( $order['SUMMARY']['ORDER_PRICE'] <> $order['SUMMARY']['BASKET_PRICE'] ){?>
                                <p class="order-item__param">Итого: <?=$order['SUMMARY']['NEED_TO_PAY_FORMATED']?> <span class="rouble">Р</span>
                                    <span class="grey" style="text-transform: lowercase;"><?=$order['SUMMARY']['PAYMENT']?></span>
                                </p>
                            <?}?>

                            <?if ( $order['SUMMARY']['REWARD'] > 0 ){?>
                                <p class="order-item__param">Вознаграждение: <?=number_format($props['DURATION']['VALUE_FORMATED']*350, 0, ',', ' ');?> <span class="rouble">Р</span></p>
                            <?}?>
                            <?if ( $order['ACTION']['CANCEL'] == 'Y' ) {?>
                                <p class="order-item__param"></p>
                                <p class="order-item__param"></p>
                                <button class="btn js-cleaner-action" data-action="remove" data-propid="<?=$order['PROPS']['Cleaner']['ID']?>" data-order="<?=$order['ID']?>" data-cleaner="<?=$arResult['CLEANER_ID']?>">
                                    Отказаться
                                </button>
                            <?} elseif( $order['ACTION']['FINISH'] == 'Y' ){?>
                                <p class="order-item__param"></p>
                                <p class="order-item__param"></p>
                                <a class="btn" href="/cleaners/my/detail.php?ID=<?=$order['ID']?>" >Выполнен</a>
                            <?}?>
                        </div>
                    </div>
                </div>
            <?}?>
        <?}?>
    </div>
</div>