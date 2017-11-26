<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 13.02.15
 * Time: 15:52
 */?>


<?//echo "<pre>"; print_r($_SESSION); echo "</pre>";?>


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
            <style>
                .forMobile {
                    display: none;
                }
                @media screen and (max-width: 1199px) {
                    .forMobile {
                        display: block;
                    }
                }
            </style>


            <?
            if ($_SESSION['periodName']){
                $periodDiscountPercent=$_SESSION['periodDiscountPercent'];
                $periodName=$_SESSION['periodName'];
                $periodDiscount=$_SESSION['periodDiscount'];
                $periodTotalPrice=$_SESSION['periodTotalPrice'];
            }else{
                $total_price_num = $arResult['BASKET_PRICE'];
                $periodDiscountPercent=15;
                $periodName="Раз в 2 недели";
                $periodDiscount=round(($total_price_num*$periodDiscountPercent)/100,0);
                $periodTotalPrice=$total_price_num-$periodDiscount;
            }
            $couponDiscount = isset($arResult['DISCOUNT_PRICE']) ? $arResult['DISCOUNT_PRICE'] : 0;
            //$periodTotalPrice -= $couponDiscount;
            $periodTotalPrice = number_format($periodTotalPrice, 0, '.', '&nbsp;');
            $periodDiscount = number_format($periodDiscount, 0, '.', '&nbsp;');
            ?>

            <div class="total2 forMobile">
                <?
//              BASKET_PRICE - это чистая цена, а ORDER_PRICE - это уже итоговая, посчитанная с учетом скидки по купону и т.д.
//              $total_price = isset($arResult['ORDER_PRICE'])?$arResult['ORDER_PRICE_FORMATED']:$arResult['BASKET_PRICE_FORMATED'];
                $total_price = $arResult['BASKET_PRICE_FORMATED'];
                ?>
                <?if ( $arResult['DISCOUNT_PRICE'] > 0 ){?>
                    <p class="total__item total__item_summary clearfix ttl_prc">
                        <span>Стоимость уборки<span style="color:#898989;"> (&#126;<?=$arResult[$arParams['PROPERTY_DURATION']]['SUMM']?> ч) </span></span><span class="pull-right"><?=$total_price?><span class="rouble">Р</span></span>
                    </p>

                    <p class="total__item clearfix">
                        <span class="pull-left">Скидка</span><span class="pull-right">- <?=$arResult['DISCOUNT_PRICE_FORMATED']?> <span class="rouble">Р</span></span>
                    </p>

                    <p class="total__item clearfix">
                        <span class="pull-left">Регулярность</span><span class="pull-right"><?=$periodName?></span>
                    </p>
                    <?if ($periodName!="Один раз"){?>
                        <p class="total__item clearfix">
                            <span class="pull-left">Подписка (<?=$periodDiscountPercent;?>%)</span><span class="pull-right">- <?=$periodDiscount?> <span class="rouble">Р</span></span>
                        </p>
                    <?}?>
                    <p class="total__item total__item_summary clearfix">
                        <span class="pull-left">Итого</span><span class="pull-right itogo"><?=$periodTotalPrice?> <span class="rouble">Р</span></span>
                    </p>

                <?} else { ?>
                    <p class="total__item total__item_summary clearfix ttl_prc">
                        <span>Стоимость уборки<span style="color:#898989;"> (&#126;<?=$arResult[$arParams['PROPERTY_DURATION']]['SUMM']?> ч) </span></span><span class="pull-right"><?=$total_price?><span class="rouble">Р</span></span>
                    </p>
                    <p class="total__item clearfix">
                        <span class="pull-left">Регулярность</span><span class="pull-right"><?=$periodName?></span>
                    </p>
                    <?if ($periodName!="Один раз"){?>
                        <p class="total__item clearfix">
                            <span class="pull-left">Подписка (<?=$periodDiscountPercent;?>%)</span><span class="pull-right">- <?=$periodDiscount?> <span class="rouble">Р</span></span>
                        </p>
                    <?}?>
                    <p class="total__item total__item_summary clearfix">
                        <span class="pull-left">Итого</span><span class="pull-right itogo"><?=$periodTotalPrice?> <span class="rouble">Р</span></span>
                    </p>
                <?}?>
            </div>
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
                        <div class="total">
                            <?
//                            $total_price = isset($arResult['ORDER_PRICE'])?$arResult['ORDER_PRICE_FORMATED']:$arResult['BASKET_PRICE_FORMATED'];
                            ?>

                            <?if ( $arResult['DISCOUNT_PRICE'] > 0 ){?>
                                <p class="total__item total__item_summary clearfix aside_price">
                                    <span>
                                        Стоимость уборки<br />
                                        <span>(&#126;<?=$arResult[$arParams['PROPERTY_DURATION']]['SUMM']?> ч)</span>
                                    </span>
                                    <span class="price_span"><?=$total_price?><span class="rouble">Р</span></span>
                                </p>

                                <p class="total__item clearfix">
                                    <span class="pull-left">Скидка</span><span class="pull-right">- <?=$arResult['DISCOUNT_PRICE_FORMATED']?> <span class="rouble">Р</span></span>
                                </p>
                                <p class="total__item clearfix">
                                    <span class="pull-left">Регулярность</span><span class="pull-right"><?=$periodName?></span>
                                </p>
                                <?if ($periodName!="Один раз"){?>
                                    <p class="total__item clearfix">
                                        <span class="pull-left">Подписка (<?=$periodDiscountPercent;?>%)</span><span class="pull-right">- <?=$periodDiscount?> <span class="rouble">Р</span></span>
                                    </p>
                                <?}?>
                                <p class="total__item total__item_summary clearfix">
                                    <span class="pull-left">Итого</span><span class="pull-right"><?=$periodTotalPrice?> <span class="rouble">Р</span></span>
                                </p>

                            <?} else { ?>
                                <p class="total__item total__item_summary clearfix aside_price">
                                    <span>
                                        Стоимость уборки<br />
                                        <span>(&#126;<?=$arResult[$arParams['PROPERTY_DURATION']]['SUMM']?> ч)</span>
                                    </span>
                                    <span class="price_span"><?=$total_price?><span class="rouble">Р</span></span>
                                </p>
                                <p class="total__item clearfix">
                                    <span class="pull-left">Регулярность</span><span class="pull-right"><?=$periodName?></span>
                                </p>
                                <?if ($periodName!="Один раз"){?>
                                    <p class="total__item clearfix">
                                        <span class="pull-left">Подписка (<?=$periodDiscountPercent;?>%)</span><span class="pull-right">- <?=$periodDiscount?> <span class="rouble">Р</span></span>
                                    </p>
                                <?}?>
                                <p class="total__item total__item_summary clearfix">
                                    <span class="pull-left">Итого</span><span class="pull-right"><?=$periodTotalPrice?> <span class="rouble">Р</span></span>
                                </p>
                            <?}?>
                        </div>
                        <h3>Что входит в уборку</h3>
                        <p>
                            Узнайте подробнее об эко-уборке с использованием средств KIEHL.
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

                <fieldset class="order-form__fieldset" id="params">
                    <h2 class="order-form__fieldset-title clearfix">Как часто у вас убираться?
                        <span class="link" data-toggle="modal" data-target="#regular_cleaning"> Подробнее о регулярной уборке</span>
                    </h2>

                    <div class="additional-control">
                        <span class="additional-control__item">
                            <label class="additional-control__item-content">
                                <input class="additional-control__item-input js-update-basketForm" name="period" data-name="" value="once_per_week" type="radio" <?=$periodName=='Раз в неделю'?'checked':''?>>
                                <span class="additional-control__item-bg"></span>
                                <span class="additional-control__item-title"><p><span>-20%</span>Раз в неделю</p></span>
                            </label>
                        </span>
                        <span class="additional-control__item">
                            <label class="additional-control__item-content">
                                <input class="additional-control__item-input js-update-basketForm" name="period" data-name="" value="twice_per_week" type="radio" <?=$periodName=='Раз в 2 недели'?'checked':''?>>
                                <span class="additional-control__item-bg"></span>
                                <span class="additional-control__item-title"><p><span>-15%</span>Раз в 2 недели</p></span>
                            </label>
                        </span>
                        <span class="additional-control__item">
                            <label class="additional-control__item-content">
                                <input class="additional-control__item-input js-update-basketForm" name="period" data-name="" value="once_per_mounth" type="radio" <?=$periodName=='Раз в месяц'?'checked':''?>>
                                <span class="additional-control__item-bg"></span>
                                <span class="additional-control__item-title"><p><span>-10%</span>Раз в месяц</p></span>
                            </label>
                        </span>
                        <span class="additional-control__item">
                            <label class="additional-control__item-content">
                                <input class="additional-control__item-input js-update-basketForm" name="period" data-name="" value="once" type="radio"<?=$periodName=='Один раз'?'checked':''?>>
                                <span class="additional-control__item-bg"></span>
                                <span class="additional-control__item-title"><p><span>-0%</span>Один раз</p></span>
                            </label>
                        </span>
                    </div>
                </fieldset>

                <fieldset class="order-form__fieldset">
                    <h2 class="order-form__fieldset-title clearfix">Дополнительно
                        <span class="link" data-target="#what-we-clean" data-toggle="modal"> &nbsp; Что входит в уборку?</span> <br/>
                        <span class="link" data-target="#clean-tools" data-toggle="modal">Чем мы убираем?</span>
                    </h2>
                    <div class="additional-control">
                        <?foreach($arResult['ITEMS']['ADDITIONAL'] as $item){
                            $idNames = array(
                                4 => 'Внутри холодильника',
                                5 => 'Внутри духовки',
                                8475 => 'Внутри микроволновки',
                                6 => 'Внутри кухонных шкафов',
                                3889 => 'Можно использовать Ваш пылесос'
                            );
                            if (isset($idNames[$item['ID']])) {
                                $item['NAME_FORMATED'] = $idNames[$item['ID']];
                            }
                            ?>
                            <?if ( strlen($item['PROPERTIES']['SET_QUANTITY']['VALUE']) > 0 ){ ?>
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
                    <h2 class="order-form__fieldset-title">Получить скидку на Первый заказ</h2>
                    <?
                    if (isset($arResult['VALIDATION_COUPON_RESULT']) && $arResult['VALIDATION_COUPON_RESULT']) { ?>
                        <span>Купон применен <?=$arResult['COUPON'];?></span>
                        <input type="hidden" name="coupon" value="<?=$arResult['COUPON'];?>">
                    <?
                    } else {
                        if (isset($arResult['VALIDATION_COUPON_RESULT'])) :?>
                            <span>Купон не действителен или введен не верно.</span>
                        <? endif; ?>
                        <div class="promo_input_holder">
                            <input type="text" class="" placeholder="Введите Промокод" name="coupon" <?=$arResult['COUPON'];?>>
                            <input type="button" class="btn js-update-coupon" value="Ok">
                        </div>
                    <?
                    }
                    ?>
                </fieldset>

                <fieldset class="order-form__fieldset">
                    <h2 class="order-form__fieldset-title">Карта скидок Много.ру</h2>
                    <div class="promo_input_holder">
                        <input type="text" placeholder="Введите номер карты Много.ру" name="mnogoru" class="mnogoru" pattern="[0-9]{8,8}" value="<?=$_SESSION['mnogoru'];?>">
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
                <button onclick="yaCounter38469730.reachGoal('choose_date_1step');" class="order-form__next btn btn_with_icons btn_responsive_true js-basket-submit" id="select_date">
                    <span class="btn__icon btn__icon_type_date"></span><?=$arParams['SUBMIT_TITLE']?><span class="btn__icon btn__icon_right btn__icon_type_forward"></span>
                </button>

            </form>
        </div>
    </section>
</div>



<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/modal-clean-tools.twig') ?>
<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/modal-what-we-clean.twig') ?>
<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/regular_cleaning.twig') ?>

