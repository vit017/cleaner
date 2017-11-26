<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 13.02.15
 * Time: 15:52
 */
?>
<div class="row">

    <div class="col-lg-10 visible-md visible-sm visible-xs order_steps">

        <?$total_price = isset($arResult['ORDER_PRICE'])?$arResult['ORDER_PRICE_FORMATED']:$arResult['BASKET_PRICE_FORMATED'];?>
        <div class="topprice" data-spy="affix" data-offset-top="50" style="
            /*top: 75px;*/
            z-index: 10;
            width: 100%;
            background: #f3f0e9;
        ">
            <script>
                jQuery(function(){
                    var w = parseInt(window.innerWidth);
                    var top = 50;
                    if (w > 768) {
                        top = 70;
                    }
                    document.querySelector('.topprice').style.top = top + 'px';
                });
            </script>
            <!--            <h3 class="inline">Стоимость уборки <br><small>(3 часа)</small><br><span>3400</span>&nbsp;₽</h3>-->
            <h3 class="inline">Стоимость уборки <small>(~<?=$arResult[$arParams['PROPERTY_DURATION']]['SUMM']?> ч)</small>&nbsp;<span><?=$total_price?></span> &#8381;</h3>
        </div>
        <br />
<!--        <a data-toggle="modal" data-target="#places" class="faq">Где мы наводим чистоту</a><br>-->
<!--        <a data-toggle="modal" data-target="#tools" class="faq">Чем мы наводим чистоту</a><br>-->
    </div>
    <div class="col-lg-10 order_steps">
        <ul class="nav nav-tabs nav-justified hidden-xs">
            <li class="params active"><a><span data-step="1"></span>Параметры помещения</a></li>
            <li class="date_time"><a><span data-step="2"></span>Дата и время</a></li>
            <li class="your_data"><a><span data-step="3"></span>Ваши данные</a></li>
            <li class="order_check"><a><span data-step="4"></span>Проверка заказа</a></li>
            <li class="paying"><a><span data-step="5"></span>Оплата</a></li>
        </ul>

        <div class="tab-content">
            <div id="params" class="tab-pane fade in active">
                <form class="form-inline order-form js-basket-form" role="form" name="BASKET_CATALOG" method="POST">
                    <div class="row">
                        <div class="col-sm-12">
                            <h3 class="inline">Площадь помещения </h3>
                            <select style="border-color: #1D1D3F;color: #1D1D3F;  width: 225px;height: 30px; " class="form-control js-update-basketForm" id="square" name="PRODUCT[]">
                                <?foreach($arResult['ITEMS']['MAIN'] as $item){
                                    if($item['PROPERTIES']['SERVICE']['VALUE']>0)
                                        continue?>
                                    <?=$item['ID']?>
                                    <option value="<?=$item['ID']?>" <?=$item['QUANTITY'] > 0 ?'selected':''?>><?=$item['NAME_FORMATED']?>м&#178;</option>
                                <?}?>
                            </select>
                        </div>
                        <? if ($arResult['WISH_CLEANER']) : ?>
                            <div class="col-sm-12">
                                <h3 class="inline">Предпочтительный клинер</h3>
                                <select id="wishCleaner" class="js-update-basketForm" name="wishCleaner">
                                    <? foreach ($arResult['WISH_CLEANER'] as $cleaner) : ?>
                                        <option value="<?=$cleaner['id'];?>" <?=$arResult['chosen_wish_cleaner'];?> <?=$arResult['chosen_wish_cleaner'] == $cleaner['id'] ? 'selected' : '';?>><?=$cleaner['name'];?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <script>
//                                $('#wishCleaner').selectpicker({
//                                    width: '225px'
//                                });
                            </script>
                        <? endif; ?>
                        <div style="display: none"  class="col-sm-12"><h3>Как часто?</h3></div>
                        <div style="display: none"   class="col-sm-4 col-xs-6 col-xxs-12 text-center">
                            <input checked type="radio" name="period" value="once" id="once">
                            <label for="once" class="radio_label big"><p><span>&nbsp;</span>Один раз</p></label>
                        </div>
                        <div style="display: none" class="col-sm-4 col-xs-6 col-xxs-12 text-center" style="opacity: 0.0">
                            <input disabled type="radio" name="period" value="once_per_week" id="once_per_week">
                            <label for="once_per_week" class="radio_label big"><p><span>-15%</span>Раз в неделю</p></label>
                        </div>
                        <div style="display: none"  class="col-sm-4 col-xs-6 col-xxs-12 text-center" style="opacity: 0.0">
                            <input disabled type="radio" type="radio" name="period" value="twice_per_week " id="twice_per_week ">
                            <label for="twice_per_week " class="radio_label big"><p><span>-10%</span>Раз в 2 недели</p></label>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-sm-12"><h3>Дополнительно:</h3></div>

                        <script>
                            $('.minus').click(function () {
                                var $input = $(this).parent().find('input');
                                var count = parseInt($input.val()) - 1;
                                // убираем выбор чекбокса и меняем текст лейбла, если менее одного окна
                                if(count<1){
                                    $('#clean_window').removeAttr('checked');
                                    $('label[for=clean_window] p').text('Окна');
                                }
                                count = count < 1 ? 1 : count;
                                $input.val(count);
                                $input.change();
                                return false;
                            });
                            $('.plus').click(function () {
                                var $input = $(this).parent().find('input');
                                $input.val(parseInt($input.val()) + 1);
                                $input.change();
                                return false;
                            });
                        </script>

                        <?foreach($arResult['ITEMS']['ADDITIONAL'] as $item){

                           // echo "<pre>"; print_r($item); echo "</pre>";

                            ?>
                            <?if($item['ID']==4){?>
                                <div class="col-sm-4 col-xs-6 text-center">
                                    <input type="checkbox" name="PRODUCT[]" data-name="<?=$item["CODE"]?>" value="4" id="clean_fridge " class="js-update-basketForm" <?=$item['QUANTITY']>0?'checked':''?>>
                                    <label for="clean_fridge " class="checkbox_label"><div class="pic p_fridge"></div><p>Внутри холодильника</p></label>
                                </div>
                            <?}?>
                            <?if($item['ID']==5){?>
                                <div class="col-sm-4 col-xs-6 text-center">
                                    <input type="checkbox" name="PRODUCT[]" data-name="<?=$item["CODE"]?>" value="5" id="clean_oven" class="js-update-basketForm" <?=$item['QUANTITY']>0?'checked':''?>>
                                    <label for="clean_oven" class="checkbox_label"><div class="pic p_oven"></div><p>Внутри духовки</p></label>
                                </div>
                            <?}?>

                            <?if($item['ID']==8475){?>
                                <div class="col-sm-4 col-xs-6 text-center">
                                    <input type="checkbox" name="PRODUCT[]" data-name="<?=$item["CODE"]?>" value="8475" id="clean_microwave" class="js-update-basketForm" <?=$item['QUANTITY']>0?'checked':''?>>
                                    <label for="clean_microwave" class="checkbox_label"><div class="pic p_microwave"></div><p>Внутри микроволновки</p></label>
                                </div>
                            <?}?>

                            <?if($item['ID']==6){?>
                                <div class="col-sm-4 col-xs-6 text-center">
                                    <input type="checkbox"name="PRODUCT[]" data-name="<?=$item["CODE"]?>" value="6" id="clean_cupboard" class="js-update-basketForm" <?=$item['QUANTITY']>0?'checked':''?>>
                                    <label for="clean_cupboard" class="checkbox_label"><div class="pic p_cupboard"></div><p>Внутри кухонных шкафов</p></label>
                                </div>
                            <?}?>

                            <?if($item['ID']==3768){?>
                                <div class="col-sm-4 col-xs-6 text-center">
                                    <input type="checkbox" class="js-update-basketForm" name="PRODUCT[]" data-name="<?=$item["CODE"]?>"  value="3768" id="clean_window" <?=$item['QUANTITY']>0?'checked':''?>>
                                    <label for="clean_window" class="checkbox_label"><div class="pic p_window"></div><p><? if($item['QUANTITY']>0){?>Количество окон<?}  else {?>Окна<?}?></p>
                                        <div class="number">
                                            <span class="minus ">-</span>
                                            <input name="QUANTITY_3768" maxlength="3" data-step="<?=strlen($item['PROPERTIES']['STEP']['VALUE'])>0?$item['PROPERTIES']['STEP']['VALUE']:2?>"  value="<?=$item['QUANTITY']>0?$item['QUANTITY']:$item['PROPERTIES']['DEFAULT']['VALUE']?>" class="js-update-basketForm" type="text" >
                                            <span class="plus ">+</span>
                                        </div>
                                    </label>
                                </div>
                            <?}?>

                            <?if($item['ID']==3889){
//                                debdie($item);
                                ?>
                                <div class="col-sm-4 col-xs-6 text-center">
                                    <input disabled type="checkbox" name="clean" value="vacuum" id="clean_vacuum">
                                    <label for="clean_vacuum" class="checkbox_label">
                                        <div class="pic p_vacuum"></div><p class="hidden-xs">Пылесос</p>
                                        <input type="radio" name="PRODUCT[]" value="no_vac" id="vac_y" <?if ($item["QUANTITY"] == 0) echo "checked";?>>
                                        <label for="vac_y" class="radio_label hidden-xs">Да, можно воспользоваться</label>
                                        <input type="radio" name="PRODUCT[]" value="3889" id="vac_n" <?if ($item["QUANTITY"] > 0) echo "checked";?>>
                                        <label for="vac_n" class="radio_label hidden-xs">У меня нет пылесоса</label>
                                        <input type="checkbox" value="no_vac" id="no_vac" >
                                        <label for="no_vac" class="checkbox_label visible-xs">У меня нет пылесоса</label>
                                        <script>
                                            if (typeof jQuery != 'undefined') {//костыль, чтобы было либо value 3889, либо no_vac
                                                jQuery(function($){
                                                    if ($('#vac_n').prop('checked')) {
                                                        $('#no_vac').prop('checked', true);
                                                    }
                                                    $('#no_vac').change(function(){
                                                        if ($(this).prop('checked')) {
                                                            $('#vac_n').prop('checked', true);
                                                        } else {
                                                            $('#vac_y').prop('checked', true);
                                                        }
                                                    });
                                                });
                                            }
                                        </script>
                                    </label>
                                </div>
                            <?}?>
                        <?}?>
                    </div>
                    <?//$total_price = isset($arResult['ORDER_PRICE'])?$arResult['ORDER_PRICE_FORMATED']:$arResult['BASKET_PRICE_FORMATED'];?>
                    <div class="row">
                        <div class="col-sm-12">
<!--                            <h3 class="inline">Стоимость уборки <small>(~--><?//=$arResult[$arParams['PROPERTY_DURATION']]['SUMM']?><!-- часа(ов))</small><br><span>--><?//=$total_price?><!--</span> &#8381;</h3>-->
                            <button onclick="yaCounter38469730.reachGoal('datebutton'); return true;" type="submit"  class="btn btn-default ">
                                <i class="icon calendar"></i>Выбрать дату >
                            </button>
                        </div>
                    </div>
                    <? if ($arResult['ERROR_MESSAGE']) { ?>
                        <span class="input-error" style="margin: 0 0 30px 0;">
                                <span class="input-error__item">
                                    <?=$arResult['ERROR_MESSAGE']?>
                                </span>
                                 </span>
                    <? } ?>
                    <!--------------------------------------->
                    <div style="display: none">
                        <?if(!empty($arResult['HIDDEN'])){?>
                            <?foreach($arResult['HIDDEN'] as $hidden){?>
                                <input type="hidden" name="<?=$hidden['NAME']?>" value="<?=$hidden['VALUE']?>">
                            <?}?>
                        <?}?>
                        <fieldset class="order-form__fieldset" >
                            <h2 class="order-form__fieldset-title">Площадь квартиры</h2>
                            <div class="single-input clearfix">
                                <label class="single-input__control">

                                </label>
                                <span class="single-input__tip">Уборка займет не меньше <strong><?=$arResult['FLAT_TIME']?></strong> часов</span>
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
<!--                            <input type="hidden" name="wishCleaner" value="--><?//=$arResult['chosen_wish_cleaner']?><!--">-->
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

                        <!--------------------------------------->
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-2 visible-lg order_steps">

        <?$total_price = isset($arResult['ORDER_PRICE'])?$arResult['ORDER_PRICE_FORMATED']:$arResult['BASKET_PRICE_FORMATED'];?>
        <div data-spy="affix" data-offset-top="100" style="width: 195px; top:90px;">
<!--            <h3 class="inline">Стоимость уборки <br><small>(3 часа)</small><br><span>3400</span>&nbsp;₽</h3>-->
            <h3 class="inline">Стоимость уборки <br><small>(~<?=$arResult[$arParams['PROPERTY_DURATION']]['SUMM']?> ч)</small><br><span><?=$total_price?></span> &#8381;</h3>
        </div>
        <br />
        <a data-toggle="modal" data-target="#places" class="faq">Где мы наводим чистоту</a><br>
        <a data-toggle="modal" data-target="#tools" class="faq">Чем мы наводим чистоту</a><br>
    </div>
</div>



<!-- ВСПЛЫВАШКА "Где мы наводим чистоту" -->
<div id="places" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <div class="modal-body">
                <h2>Что входит в уборку</h2>
                <div class="clean-content">
                    <h3 class="clean-content_title">Комната</h3>
                    <div class="clearfix">
                        <img class="clean-content_pic hidden-sm hidden-xs" src="/img/pic_bedroom_pointed.jpg">
                        <ol class="clean-content_list">
                            <li>Протираем поверхности и плинтус</li>
                            <li>Убираем мусор</li>
                            <li>Заправляем постель</li>
                            <li>Убираем пыль со светильников</li>
                            <li>Поправляем небольшие вещи</li>
                            <li>Пылесосим, моем пол</li>
                            <li>Чистим зеркала и стеклянные поверхности</li>
                        </ol>
                    </div>
                </div>
                <div class="clean-content">
                    <h3 class="clean-content_title">Кухня</h3>
                    <div class="clearfix">
                        <img class="clean-content_pic hidden-sm hidden-xs" src="/img/pic_kitchen_pointed.jpg">
                        <ol class="clean-content_list">
                            <li>Протираем стол и столешницу</li>
                            <li>Моем снаружи холодильник, духовой шкаф и плиту</li>
                            <li>Пылесосим, моем пол</li>
                            <li>Чистим раковину</li>
                            <li>Выносим мусор</li>
                            <li>Протираем поверхности и плинтус</li>
                        </ol>
                    </div>
                </div>
                <div class="clean-content">
                    <h3 class="clean-content_title">Ванная</h3>
                    <div class="clearfix">
                        <img class="clean-content_pic hidden-sm hidden-xs" src="/img/pic_bathroom_pointed.jpg">
                        <ol class="clean-content_list">
                            <li>Чистим зеркала и стеклянные поверхности</li>
                            <li>Выносим мусор</li>
                            <li>Пылесосим, моем пол</li>
                            <li>Моем и дезинфицируем раковину, ванну, душевую кабину, унитаз</li>
                            <li>Протираем поверхности</li>
                            <li>Убираем разводы снаружи шкафа и стен</li>
                        </ol>
                    </div>
                </div>
                <div class="clean-content">
                    <h3 class="clean-content_title">Коридор</h3>
                    <div class="clearfix">
                        <img class="clean-content_pic hidden-sm hidden-xs" src="/img/pic_corridor_pointed.jpg">
                        <ol class="clean-content_list">
                            <li>Убирамем пыль со светильников</li>
                            <li>Пылесосим, моем пол</li>
                            <li>Протираем поверхности и плинтус</li>
                            <li>Чистим зеркала и стеклянные поверхности</li>
                            <li>Расставляем обувь</li>
                            <li>Выносим мусор</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ВСПЛЫВАШКА "Чем мы наводим чистоту" -->
<div id="tools" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <div class="modal-body">
                <h2>Чем мы убираем</h2>
                <p>
                    Вы получите лучшую уборку в жизни. <br>
                    Взгляните на список того, что принесут с собой профессиональные клинеры.
                </p>
                <h3>Что мы принесем с собой</h3>
                <div class="tools-item">
                    <div class="tools-item_pic pic_1 hidden-xs"></div>
                    <div class="tools-item_content">
                        <h4 class="tools-item_title">Экологические средства KIEHL</h4>
                        <ul class="list-unstyled">
                            <li>— Средство для пола</li>
                            <li>— Спрей для ванных комнат</li>
                            <li>— Спрей для зеркал</li>
                            <li>— Универсальный спрей для поверхностей</li>
                        </ul>
                    </div>
                </div>
                <div class="tools-item">
                    <div class="tools-item_pic pic_2 hidden-xs"></div>
                    <div class="tools-item_content">
                        <h4 class="tools-item_title">Одноразовые расходные материалы</h4>
                        <ul class="list-unstyled">
                            <li>— Салфетки для пола</li>
                            <li>— Губки</li>
                            <li>— Салфетки из микрофибры</li>
                            <li>— Перчатки</li>
                            <li>— Мешки для мусора</li>
                        </ul>
                    </div>
                </div>
                <div class="tools-item">
                    <div class="tools-item_pic pic_3 hidden-xs"></div>
                    <div class="tools-item_content">
                        <h4 class="tools-item_title">Инвентарь</h4>
                        <ul class="list-unstyled">
                            <li>— Швабра для пола</li>
                            <li>— Щетка для мытья окон</li>
                            <li>— Чистящий ролик</li>
                        </ul>
                    </div>
                </div>
                <h3>Что вам необходимо иметь</h3>
                <div class="tools-item">
                    <div class="tools-item_pic pic_4 hidden-xs"></div>
                    <div class="tools-item_content">
                        <ul class="list-unstyled">
                            <li>— Пылесос</li>
                            <li>— Утюг</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?
if ((!isset($_REQUEST["AJAX_CALL"]) || $_REQUEST["AJAX_CALL"] != 'Y') && isset($_GET["sqpic"])) : ?>
    <script>
        $(function(){
            <?
            foreach ($arResult['ITEMS']['MAIN'] as $item) {
                if ($item['ID'] == $_GET["sqpic"]) {
                    ?>
                        $('#square').val(<?=$_GET["sqpic"];?>);
                    <?
                    break;
                }
            }
            ?>
            setTimeout(function(){$('#square').change()}, 50);
        });
    </script>
<? endif; ?>
<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/modal-clean-tools.twig') ?>
<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/modal-what-we-clean.twig') ?>


