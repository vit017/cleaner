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

<div id="js-basket">
    <div class="container">
        <h1 class="page-title">Заказ уборки</h1>

        <section class="order">
            <div class="page-block order__content">
                <div class="basketBlock">
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
                            </div>

                            <div class="order-form__fieldset-tip">
                                <div class="total">
                                    <p class="total__item total__item_summary clearfix aside_price">
                                        <span>
                                            Стоимость уборки<br />
                                            <span>(&#126;<?=$arResult[$arParams['PROPERTY_DURATION']]['SUMM']?> ч)</span>
                                        </span>
                                        <span class="price_span"><?=$arResult["BASKET_PRICE_FORMATED"];?> <span class="rouble">Р</span></span>
                                    </p>
                                    <p class="total__item clearfix">
                                        <span class="pull-left">Регулярность</span><span class="pull-right"><?=$arResult['periodName'];?></span>
                                    </p>
                                    <?if ($arResult['DISCOUNT_PRICE']){?>
                                        <p class="total__item clearfix">
                                            <span class="pull-left">Скидка</span><span class="pull-right">- <?=$arResult['DISCOUNT_PRICE_FORMATED'];?> <span class="rouble">Р</span></span>
                                        </p>
                                    <?}?>
                                    <?if ($arResult['periodName']!="Один раз" && !$arResult['DISCOUNT_PRICE']){?>
                                        <p class="total__item clearfix">
                                            <span class="pull-left">Подписка (<?=$arResult['periodDiscountPercent'];?>%)</span><span class="pull-right">- <?=$arResult['periodDiscount'];?> <span class="rouble">Р</span></span>
                                        </p>
                                    <?}?>
                                    <p class="total__item total__item_summary clearfix">
                                        <span class="pull-left">Итого</span><span class="pull-right"><?=$arResult['TOTAL_PRICE_FORMATED'];?>  <span class="rouble">Р</span></span>
                                    </p>
                                    <!--p class="total__item clearfix mnogorup">
                                        <img src="/layout/assets/images/mnogo/mnogoru_logo_small.png" style="height:30px;"> <span class="mnogoru_info">+<?=$arResult["MNOGORU_PRICE_FORMATED"];?> <?=$arResult["MNOGORU_NAME_FORMATED"];?></span>
                                    </p-->
                                </div>


                                <h3>Промокод</h3>
                                <div class="promo_input_holder">
                                    <?if (isset($arResult['VALIDATION_COUPON_RESULT']) && $arResult['VALIDATION_COUPON_RESULT']) { ?>
                                        <p style="color:green;font-weight:bold;">Купон применен: <?=$arResult['COUPON'];?></p>
                                        <input type="hidden" name="coupon" value="<?=$arResult['COUPON'];?>">
                                    <?}else{
                                        if (isset($arResult['VALIDATION_COUPON_RESULT'])){?>
                                            <p style="color:red;">Купон не действителен или введен не верно</p>
                                        <?}?>
                                        <input type="text" placeholder="Промокод" name="coupon" value="<?=$arResult['COUPON'];?>">
                                        <input type="button" class="btn js-update-coupon" value="Ok">
                                    <?}?>
                                </div>
                                <br>
                                <!--h3>Номер карты Много.ру</h3>
                                <div class="promo_input_holder">
                                    <?if ($arResult["MNOGORU_VAL"])
                                        echo $arResult["MNOGORU_VAL"];
                                    ?>
                                    <input type="text" placeholder="0000 0000" name="mnogoru" class="mnogoru" value="<?=$arResult['MNOGORU'];?>">
                                    <input type="button" class="btn js-update-coupon" value="Ok">
                                </div-->
                                <br>
                                <h3>Что входит в уборку</h3>
                                <p>Узнайте подробнее об эко-уборке с использованием средств KIEHL.</p>
                                <p>
                                    <span class="link" data-target="#what-we-clean" data-toggle="modal"><strong>Что входит в уборку</strong></span> <br/>
                                    <span class="link" data-target="#clean-tools" data-toggle="modal"><strong>Чем мы убираем</strong></span>
                                </p>
                                <br>
                                <h3>100% гарантия</h3>
                                <p>Если вам не понравится уборка, мы вернёмся и уберём заново!</p>
                            </div>
                        </fieldset>

                        <fieldset class="order-form__fieldset" id="params">
                            <h2 class="order-form__fieldset-title clearfix">Как часто у вас убираться?
                                <span class="link" data-toggle="modal" data-target="#regular_cleaning"> Подробнее о регулярной уборке</span>
                            </h2>

                            <div class="additional-control">
                                <span class="additional-control__item">
                                    <label class="additional-control__item-content">
                                        <input class="additional-control__item-input js-update-basketForm" name="period" data-name="" value="once" type="radio"<?=$arResult['periodName']=='Один раз'?'checked':''?>>
                                        <span class="additional-control__item-bg"></span>
                                        <span class="additional-control__item-title"><p style="margin-top:35px;"><span></span>Один раз</p></span>
                                    </label>
                                </span>
                                <span class="additional-control__item">
                                    <label class="additional-control__item-content">
                                        <input class="additional-control__item-input js-update-basketForm" name="period" data-name="" value="once_per_mounth" type="radio" <?=$arResult['periodName']=='Раз в месяц'?'checked':''?>>
                                        <span class="additional-control__item-bg"></span>
                                        <span class="additional-control__item-title"><p><span>-10%</span>Раз в месяц</p></span>
                                    </label>
                                </span>
                                <span class="additional-control__item">
                                    <label class="additional-control__item-content">
                                        <input class="additional-control__item-input js-update-basketForm" name="period" data-name="" value="twice_per_week" type="radio" <?=$arResult['periodName']=='Раз в 2 недели'?'checked':''?>>
                                        <span class="additional-control__item-bg"></span>
                                        <span class="additional-control__item-title"><p><span>-15%</span>Раз в 2 недели</p></span>
                                    </label>
                                </span>                          
                                <span class="additional-control__item">
                                    <label class="additional-control__item-content">
                                        <input class="additional-control__item-input js-update-basketForm" name="period" data-name="" value="once_per_week" type="radio" <?=$arResult['periodName']=='Раз в неделю'?'checked':''?>>
                                        <span class="additional-control__item-bg"></span>
                                        <span class="additional-control__item-title"><p><span>-20%</span>Раз в неделю</p></span>
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
                                        4       =>  'Внутри холодильника',
                                        5       =>  'Внутри духовки',
                                        8475    =>  'Внутри микроволновки',
                                        6       =>  'Внутри кухонных шкафов',
                                        3889    =>  'Можно использовать Ваш пылесос'
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
                            <div class="total2 forMobile">
                                <h3 class="order-form__fieldset-title">Получить скидку по промокоду</h3>
                                <div class="promo_input_holder">
                                    <?if (isset($arResult['VALIDATION_COUPON_RESULT']) && $arResult['VALIDATION_COUPON_RESULT']) { ?>
                                        <p style="color:green;font-weight:bold;">Купон применен: <?=$arResult['COUPON'];?></p>
                                        <input type="hidden" name="coupon_mob" value="<?=$arResult['COUPON'];?>">
                                    <?}else{
                                        if (isset($arResult['VALIDATION_COUPON_RESULT'])){?>
                                            <p style="color:red;">Купон не действителен или введен не верно</p>
                                        <?}?>
                                        <div><input type="text" placeholder="Промокод" name="coupon_mob" value="<?=$arResult['COUPON'];?>"></div>
                                        <input type="button" class="btn js-update-coupon" value="Ok">
                                    <?}?>
                                </div>
                                <!--h3 class="order-form__fieldset-title">Номер карты Много.ру</h3>
                                <div class="promo_input_holder">
                                    <div><input type="text" placeholder="0000 0000" name="mnogoru_mob" class="mnogoru" value="<?=$arResult['MNOGORU'];?>"></div>
                                    <input type="button" class="btn js-update-coupon" value="Ok">
                                </div-->

                                <div class="clearfix"></div>

                                <p class="total__item total__item_summary clearfix ttl_prc">
                                    <span>
                                        Стоимость уборки
                                        <span style="color:#898989;">(&#126;<?=$arResult[$arParams['PROPERTY_DURATION']]['SUMM']?> ч)</span>
                                    </span>
                                    <span class="pull-right"><?=$arResult["BASKET_PRICE_FORMATED"];?> <span class="rouble">Р</span></span>
                                </p>
                                <p class="total__item clearfix">
                                    <span class="pull-left">Регулярность</span><span class="pull-right"><?=$arResult['periodName'];?></span>
                                </p>
                                <?if ($arResult['DISCOUNT_PRICE']){?>
                                    <p class="total__item clearfix">
                                        <span class="pull-left">Скидка</span><span class="pull-right">- <?=$arResult['DISCOUNT_PRICE_FORMATED'];?> <span class="rouble">Р</span></span>
                                    </p>
                                <?}?>
                                <?if ($arResult['periodName']!="Один раз" && !$arResult['DISCOUNT_PRICE']){?>
                                    <p class="total__item clearfix">
                                        <span class="pull-left">Подписка (<?=$arResult['periodDiscountPercent'];?>%)</span><span class="pull-right">- <?=$arResult['periodDiscount'];?> <span class="rouble">Р</span></span>
                                    </p>
                                <?}?>
                                <p class="total__item total__item_summary clearfix">
                                    <span class="pull-left">Итого</span><span class="pull-right"><?=$arResult['TOTAL_PRICE_FORMATED'];?>  <span class="rouble">Р</span></span>
                                </p>

                                <!--p class="total__item clearfix mnogorup">
                                    <img src="/layout/assets/images/mnogo/mnogoru_logo_small.png" style="height:30px;"> <span class="mnogoru_info">+<?=$arResult["MNOGORU_PRICE_FORMATED"];?> <?=$arResult["MNOGORU_NAME_FORMATED"];?></span>
                                </p-->
                            </div>
                        </fieldset>

                        <fieldset class="order-form__fieldset">
                            <?if ($arResult['ERROR_MESSAGE']){?>
                                <span class="input-error" style="margin:0 0 30px 0;">
                                    <span class="input-error__item">
                                        <?=$arResult['ERROR_MESSAGE']?>
                                    </span>
                                </span>
                            <?}?>
                        </fieldset>
                    </form>
                </div>



                <?
                //Оформление заказа!
                $_SESSION["DURATION"]=$arResult[$arParams['PROPERTY_DURATION']]['SUMM'];

                if (isset($_POST["ORDER_COMMENT"])) {
                    $_SESSION['ORDER_COMMENT'] = $_POST["ORDER_COMMENT"];
                };

                if (isset($_POST["ORDER_PROP_NAME"])) {
                    $_SESSION['ORDER_NAME'] = $_POST["ORDER_PROP_NAME"];
                };
                if (isset($_POST["street"])) {
                    $_SESSION['street'] = $_POST["street"];
                };
                if (isset($_POST["house"])) {
                    $_SESSION['house'] = $_POST["house"];
                };
                if (isset($_POST["corpse"])) {
                    $_SESSION['corpse'] = $_POST["corpse"];
                };
                if (isset($_POST["flat"])) {
                    $_SESSION['flat'] = $_POST["flat"];
                };
                if (isset($_POST["speaker"])) {
                    $_SESSION['speaker'] = $_POST["speaker"];
                };
                if (isset($_POST["company_name"])) {
                    $_SESSION['company_name'] = $_POST["company_name"];
                };
                if (isset($_POST["company_inn"])) {
                    $_SESSION['company_inn'] = $_POST["company_inn"];
                };
                if (isset($_POST["company_y"])) {
                    $_SESSION['company_y'] = $_POST["company_y"];
                };
                if (isset($_POST["company_n"])) {
                    $_SESSION['company_n'] = $_POST["company_n"];
                };

                 ?>
                <script>
                   var rf_sponsor="<?=$_SESSION['street'];?>";
                   var house="<?=$_SESSION['house'];?>";
                   var corpse="<?=$_SESSION['corpse'];?>";
                   var flat="<?=$_SESSION['flat'];?>";
                   var speaker="<?=$_SESSION['speaker'];?>";
                   var company_name="<?=$_SESSION['company_name'];?>";
                   var company_inn="<?=$_SESSION['company_inn'];?>";
                   var company_y="<?=$_SESSION['company_y'];?>";
                   var company_n="<?=$_SESSION['company_n'];?>";
                   var CLIENT_PARAMS = {};
                   CLIENT_PARAMS.ORDER_PROP_NAME = '<?=$_SESSION['ORDER_NAME'];?>';
                   CLIENT_PARAMS.ORDER_COMMENT = '<?=$_SESSION['ORDER_COMMENT'];?>';
                </script>


                <?
                $APPLICATION->IncludeComponent("breadhead:sale.order2.0", "salemcNew", array(
                    "IBLOCK_TYPE" => "settings",
                    "IBLOCK_ID" => "3",
                    "NEWS_COUNT" => "217",
                    "USE_SEARCH" => "N",
                    "USE_RSS" => "N",
                    "USE_RATING" => "N",
                    "USE_CATEGORIES" => "N",
                    "USE_REVIEW" => "N",
                    "USE_FILTER" => "Y",
                    "FILTER_NAME" => "",
                    "FILTER_FIELD_CODE" => array(
                        0 => "",
                        1 => "",
                    ),
                    "FILTER_PROPERTY_CODE" => array(
                        0 => "DATE",
                        1 => "TIME",
                        2 => "",
                    ),
                    "SORT_BY1" => "SORT",
                    "SORT_ORDER1" => "ASC",
                    "SORT_BY2" => "SORT",
                    "SORT_ORDER2" => "ASC",
                    "CHECK_DATES" => "Y",
                    "SEF_MODE" => "N",
                    "SEF_FOLDER" => "/order/basket/",
                    "AJAX_MODE" => "N",
                    "AJAX_OPTION_JUMP" => "N",
                    "AJAX_OPTION_STYLE" => "Y",
                    "AJAX_OPTION_HISTORY" => "N",
                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "36000000",
                    "CACHE_FILTER" => "Y",
                    "CACHE_GROUPS" => "Y",
                    "SET_STATUS_404" => "N",
                    "SET_TITLE" => "N",
                    "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                    "ADD_SECTIONS_CHAIN" => "N",
                    "ADD_ELEMENT_CHAIN" => "N",
                    "USE_PERMISSIONS" => "N",
                    "PREVIEW_TRUNCATE_LEN" => "",
                    "LIST_ACTIVE_DATE_FORMAT" => "d.m.Y",
                    "LIST_FIELD_CODE" => array(
                        0 => "",
                        1 => "",
                    ),
                    "LIST_PROPERTY_CODE" => array(
                        0 => "",
                        1 => "",
                    ),
                    "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                    "DISPLAY_NAME" => "Y",
                    "META_KEYWORDS" => "-",
                    "META_DESCRIPTION" => "-",
                    "BROWSER_TITLE" => "-",
                    "DETAIL_ACTIVE_DATE_FORMAT" => "d.m.Y",
                    "DETAIL_FIELD_CODE" => array(
                        0 => "",
                        1 => "",
                    ),
                    "DETAIL_PROPERTY_CODE" => array(
                        0 => "",
                        1 => "",
                    ),
                    "PATH_TO_BASKET"=>"/order/basket/",
                    "PATH_TO_PERSONAL" => "/user",
                    "DETAIL_DISPLAY_TOP_PAGER" => "N",
                    "DETAIL_DISPLAY_BOTTOM_PAGER" => "N",
                    "DETAIL_PAGER_TITLE" => "Страница",
                    "DETAIL_PAGER_TEMPLATE" => "",
                    "DETAIL_PAGER_SHOW_ALL" => "N",
                    "PAGER_TEMPLATE" => ".default",
                    "DISPLAY_TOP_PAGER" => "N",
                    "DISPLAY_BOTTOM_PAGER" => "N",
                    "PAGER_TITLE" => "Новости",
                    "PAGER_SHOW_ALWAYS" => "N",
                    "PAGER_DESC_NUMBERING" => "N",
                    "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                    "PAGER_SHOW_ALL" => "N",
                    "DISPLAY_DATE" => "N",
                    "DISPLAY_PICTURE" => "N",
                    "DISPLAY_PREVIEW_TEXT" => "N",
                    "USE_SHARE" => "N",
                    "PAY_FROM_ACCOUNT"=>"Y",
                    "AJAX_OPTION_ADDITIONAL" => "",
                    "VARIABLE_ALIASES" => array(
                        "SECTION_ID" => "SECTION_ID",
                        "ELEMENT_ID" => "ELEMENT_ID",
                    )
                    ),
                    false
                );?>
            </div>
        </section>
    </div>
</div>


<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/modal-clean-tools.twig') ?>
<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/modal-what-we-clean.twig') ?>
<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/regular_cleaning.twig') ?>