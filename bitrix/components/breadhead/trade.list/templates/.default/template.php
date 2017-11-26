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
        <div class="order-detail__header clearfix btn_type_second js-show-more" style="width:100%; text-align: center; cursor:pointer; display:none">
            <span>Новые заказы<span>
        </div>
        <?// print_r($arResult);?>
        <?if ( $arResult['WISH'] =='Y' ){?>
            <div class="order-detail__header clearfix">
                <h2 class="order-detail__title">Тебя хотят</h2>
            </div>
            <?foreach($arResult['WISH_ORDERS'] as $order){?>
                <?$props = $order['PROPS_FORMATED'];?>
                <div class="order-detail__content ">
                    <div class="order-item js-show_detail">
                        <h4 class="order-item__title cleaner-title ">Заказ №<?=$order['ID']?></h4>
                        <p class="order-item__param"><?=$props['DATE']['VALUE_FORMATED']?></p>
                        <p class="order-item__param"><?=$props['TIME']['VALUE_FORMATED']?> (<?=$props['DURATION']['VALUE_FORMATED']?>)</p>
                        <p class="order-item__param"><span class="order-item__param-name">Адрес:</span> <?=bhTools::cutAddress($props['PERSONAL_STREET']['VALUE_FORMATED'])?></p>
                        <p class="order-item__param"></p>
                        <div class="order-detail js-order_detail" style="display: none">
                            <?if ( $order['ACTION']['TAKE'] == 'Y' ) {?>
                                <button class="btn js-cleaner-action" data-action="add" data-order="<?=$order['ID']?>" data-cleaner="<?=$arResult['CLEANER_ID']?>">
                                    Взять
                                </button>
                            <?}?>
                        </div>
                    </div>
                </div>
            <?}?>
        <?}?>
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
                        <p class="order-item__param"><span class="order-item__param-name">Адрес:</span> <?=bhTools::cutAddress($props['PERSONAL_STREET']['VALUE_FORMATED'])?></p>
                        <p class="order-item__param"></p>
                        <div class="order-detail js-order_detail" style="display: none">
                            <button class="btn js-cleaner-action" data-action="add" data-order="<?=$order['ID']?>" data-cleaner="<?=$arResult['CLEANER_ID']?>">
                                Взять
                            </button>
                        </div>
                    </div>
                </div>
            <?}?>
        <?}?>
    </div>
</div>