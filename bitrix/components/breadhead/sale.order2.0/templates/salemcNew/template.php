<form class="order-form js-form-validate" name="order_form" method="post">
    <?if ($arResult["step"]=="ok"){?>
        <style>
            .basketBlock {
                display: none;
            }
        </style>

       <?if ($arResult["PAY_SYSTEM"]["ACTION_FILE"] && $arResult["PAY_SYSTEM"]["ID"]!=1){
           include $arResult["PAY_SYSTEM"]["PATH_TO_ACTION"];
        }else{?>
            <div class="order-final">
                <h1 class="order-final__title">Спасибо!</h1>
                <div class="order-final__msg">
                    <h2 class="order-final__msg-title">Ваш заказ принят</h2>
                    <p class="order-final__msg-text">Вы всегда можете просмотреть детали заказа в вашем личном кабинете.</p>
                </div>
                <a href="/user/" class="order-form__next btn btn_with_icons btn_responsive_true">
                    <span class="btn__icon btn__icon_type_user"></span>В личный кабинет<span class="btn__icon btn__icon_right btn__icon_type_forward"></span>
                </a>
                <br><br><br>
                <div class="bonuses">
                    <h3 class="bonuses__title">Ваши бонусы</h3>
                    <p class="bonuses__item">
                        <span class="bonuses__item-name">Бонусы:</span> 0 <span class="rouble">Р</span>
                    </p>
                    <p class="bonuses__item">
                        <span class="bonuses__item-name">Приглашённых друзей:</span> 0
                    </p>
                    <div class="bonuses__invite">
                        <p class="bonuses__invite-txt">Пригласите друзей заказать уборку и получите Вместе с ними по 500 рублей скидки на заказ!</p>
                        <input type="hidden" name="USER_ID" value="{{ result.user_id }}">
                        <div class="bonuses__invite-btns clearfix">
                            <a target="_blank" href="http://vk.com/share.php?url={{result.urlVk}}&title={{result.SHARE_TITLE}}&description={{result.SHARE_DESCRIPTION}}&image={{result.FULL_SERVER_NAME}}/layout/assets/images/sm-image1.jpg" class="btn btn_with_icons" style="background: transparent;">
                                <img src="/layout/assets/images/icon_sprite_social_04.png">
                            </a>
                            <a target="_blank" href="http://www.facebook.com/sharer/sharer.php?u={{result.urlFb}}" class="btn btn_with_icons" style="background: transparent;">
                                <img src="/layout/assets/images/icon_sprite_social_02.png">
                            </a>
                        </div>
                    </div>
                </div>
                <br><br><br>

                <div class="monogoru_thankyou_page">
                    <a href="http://mnogo.ru/anketa.html?range=1161" target="_blank"></a>
                    <img src="/layout/assets/images/mnogo/line_2 light.jpg">
                </div>


            </div>
        <?}?>
    <?}else{?>
        <?if ($arResult["step"]=="checkPhone"){?>
            <style>
                .basketBlock {
                    display: none;
                }
            </style>

            <fieldset class="order-form__fieldset">
                <h2 class="order-form__fieldset-title">Подтверждение номера телефона</h2>
                <?if ($arResult["errorText"])
                    echo "<p style='color:red;'>".$arResult["errorText"]."</p>";
                ?>
                <p>
                    На ваш номер телефона было выслано смс с кодом подтверждения. Пожалуйста, введите код в поле ниже.
                </p>
                <div class="sms-confirm">
                    <label class="sms-confirm__input input-txt" data-placeholder="Код подтверждения">
                        <input class="input-txt__field" type="text" name="confirm_code" placeholder="Код подтверждения" />
                    </label>
                </div>
                <div class="order-form__fieldset-tip">
                    <h3>Приватность</h3>
                    <p>
                        Все введенные вами данные абсолютно конфиденциальны
                        и необходимы только для работы сервиса.
                    </p>
                </div>
            </fieldset>
            <button  class="order-form__next btn btn_with_icons btn_responsive_true" name="con" id="save_number"  value="submit" name="submit">
                <span class="btn__icon btn__icon_type_save"></span>Продолжить<span class="btn__icon btn__icon_right btn__icon_type_forward"></span>
            </button>

            <input type="hidden" name="ORDER_PROP_DATE" value="<?=$arResult['ORDER_PROP_DATE'];?>">
            <input type="hidden" name="ORDER_PROP_TIME" value="<?=$arResult['ORDER_PROP_TIME'];?>">
            <input type="hidden" name="ORDER_PROP_PERSONAL_CITY" value="<?=$arResult['ORDER_PROP_PERSONAL_CITY'];?>">
            <input type="hidden" name="ORDER_PROP_PERSONAL_STREET" value="<?=$arResult['ORDER_PROP_PERSONAL_STREET'];?>">
            <input type="hidden" name="ORDER_PROP_NAME" value="<?=$arResult['ORDER_PROP_NAME'];?>">
            <input type="hidden" name="ORDER_PROP_PERSONAL_PHONE" value="<?=$arResult['ORDER_PROP_PERSONAL_PHONE'];?>">
            <input type="hidden" name="ORDER_PROP_USER_LOGIN" value="<?=$arResult['ORDER_PROP_USER_LOGIN'];?>">
            <input type="hidden" name="ORDER_PROP_USER_PASSWORD" value="<?=$arResult['ORDER_PROP_USER_PASSWORD'];?>">

        <?}elseif ($arResult["step"]=="payment"){?>
            <style>
                .basketBlock {
                    display: none;
                }
            </style>

            <input type="hidden" name="ORDER_PROP_DATE" value="<?=$arResult['ORDER_PROP_DATE'];?>">
            <input type="hidden" name="ORDER_PROP_TIME" value="<?=$arResult['ORDER_PROP_TIME'];?>">
            <input type="hidden" name="ORDER_PROP_PERSONAL_CITY" value="<?=$arResult['ORDER_PROP_PERSONAL_CITY'];?>">
            <input type="hidden" name="ORDER_PROP_PERSONAL_STREET" value="<?=$arResult['ORDER_PROP_PERSONAL_STREET'];?>">
            <input type="hidden" name="ORDER_PROP_NAME" value="<?=$arResult['ORDER_PROP_NAME'];?>">
            <input type="hidden" name="ORDER_PROP_PERSONAL_PHONE" value="<?=$arResult['ORDER_PROP_PERSONAL_PHONE'];?>">
            <input type="hidden" name="ORDER_PROP_USER_LOGIN" value="<?=$arResult['ORDER_PROP_USER_LOGIN'];?>">
            <input type="hidden" name="ORDER_PROP_USER_PASSWORD" value="<?=$arResult['ORDER_PROP_USER_PASSWORD'];?>">

            <fieldset class="order-form__fieldset">
                <h2 class="order-form__fieldset-title">Способ оплаты</h2>
                <div class="order-form__fieldset-tip">
                    <h3>Оплата наличными</h3>
                    <p>Мы принимаем оплату наличными по факту уборки, но совсем скоро добавим возможность оплаты банковскими картами.</p>
                </div>

                <?foreach ($arResult["PAY_SYSTEM"] as $arPay){?>
                    <label class="payment-type"  <?if ($arPay["ID"]==2){?> style="display: none" <?}?>>
                        <input class="payment-type__input" type="radio" value="<?=$arPay['ID'];?>" name="PAY_SYSTEM_ID" id="ID_PAY_SYSTEM_ID_<?=$arPay['ID'];?>" <?if ($arPay['CHECKED']) echo 'checked';?>>
                        <span class="payment-type__content">
                            <span class="payment-type__content-title"><?=$arPay["NAME"];?></span>
                            <span class="payment-type__content-desc">
                                <?=$arPay["DESCRIPTION"];?>
                            </span>
                            <?if ($arPay["ID"]==2){?>
                                <img src="/layout/assets/images/content/cards.png" alt="">
                            <?}?>
                        </span>
                    </label>
                <?}?>
            </fieldset>

            <button class="order-form__next btn btn_with_icons btn_responsive_true" id="save_contactinfo"  value="submit" name="submit">
                <span class="btn__icon btn__icon_type_place"></span>Оформить заказ<span class="btn__icon btn__icon_right btn__icon_type_forward"></span>
            </button>
        <?}else{
            if ($USER->IsAuthorized()){
                $rsUser = CUser::GetByID($USER->GetID());
                $arUser = $rsUser->Fetch();
                if ($arUser["PERSONAL_PHONE"])
                    $personalPhone=$arUser["PERSONAL_PHONE"];                
                if ($arUser["NAME"])
                    $personalName=$arUser["NAME"];                
                if ($arUser["LOGIN"])
                    $personalEmail=$arUser["LOGIN"];                
                if ($arUser["PERSONAL_STREET"])
                    $personalStreet=$arUser["PERSONAL_STREET"];                
                if ($arUser["PERSONAL_CITY"])
                    $personalCity=$arUser["PERSONAL_CITY"];
            }

            if ($_SESSION["ORDER_PROP_NAME"])
                $personalName=$_SESSION["ORDER_PROP_NAME"];             
            if ($_SESSION["ORDER_PROP_PERSONAL_PHONE"])
                $personalPhone=$_SESSION["ORDER_PROP_PERSONAL_PHONE"];             
            if ($_SESSION["ORDER_PROP_PERSONAL_STREET"])
                $personalStreet=$_SESSION["ORDER_PROP_PERSONAL_STREET"];             
            if ($_SESSION["ORDER_PROP_PERSONAL_CITY"])
                $personalCity=$_SESSION["ORDER_PROP_PERSONAL_CITY"];             
            if ($_SESSION["ORDER_PROP_USER_LOGIN"])
                $personalEmail=$_SESSION["ORDER_PROP_USER_LOGIN"];             

            if ($_SESSION["ORDER_PROP_DATE"])
                $personalDate=$_SESSION["ORDER_PROP_DATE"];             
            if ($_SESSION["ORDER_PROP_TIME"])
                $personalTime=$_SESSION["ORDER_PROP_TIME"];
            ?>



            <fieldset class="order-form__fieldset">
                <h2 class="order-form__fieldset-title">Выберите дату и время</h2>
                
                <div class="one_half h_left">
                    <div class="promo_input_holder">
                        <input type="text" id="datepicker1" placeholder="Выберете дату" readonly="readonly" name="ORDER_PROP_DATE" value="<?=$personalDate;?>">
                    </div>
                </div>

                <div class="time-picker clearfix one_half h_right">
                    <?if ($arResult["actualTime"]){?>
                    <label class="order-form__contacts-input">
                        <select class="select select_width_full select_search_false js-city-select-time" name="ORDER_PROP_TIME" id="" required="required" data-parsley-error-message="Выберите время">
                            <?foreach ($arResult["actualTime"] as $arTime){?>
                                <option value="<?=$arTime;?>"><?=$arTime;?></option>
                            <?}?>
                        </select>
                    </label>
                    <?}elseif ($personalDate){?>
                        <p style="color:red;">Нет доступного время на выбранную Вами дату.</p>
                    <?}?>
                </div>
            </fieldset>

        	<fieldset class="order-form__fieldset">
                <h2 class="order-form__fieldset-title">Ваши данные</h2>
                <div class="order-form__contacts">
                    <?if (!$USER->IsAuthorized()){?>
                        <div class="order-form__contacts-block">
                            <a href="/user/?backurl=/order/basket/">Войдите</a>, если вы уже зарегистрированы
                        </div>
                    <?}?>
                    <div class="order-form__contacts-block clearfix">
                        <h3 class="order-form__contacts-title">Адрес</h3>
        	            <label class="order-form__contacts-input one_half h_left">
        	                <select class="select select_width_full select_search_false js-city-select-city" name="ORDER_PROP_PERSONAL_CITY" id="" required="required" data-parsley-error-message="Укажите город">
        	                    <option value="Москва" <?if ($personalCity=="Москва") echo "selected";?>>Москва</option>
        	                    <option value="Санкт-Петербург" <?if ($personalCity=="Санкт-Петербург") echo "selected";?>>Санкт-Петербург</option>
        	                </select>
        	            </label>
                        <label class="order-form__contacts-input input-txt one_half h_right" data-placeholder="Улица, дом, корпус, номер квартиры">
                            <input class="input-txt__field" type="text" value="<?=$personalStreet;?>" name="ORDER_PROP_PERSONAL_STREET" placeholder="Улица, дом, корпус, номер квартиры" required="required" data-parsley-error-class="input-txt_state_error" data-parsley-error-message="Поле не заполнено" data-parsley-id="8992"><span class="input-error" id="parsley-id-8992"></span>
                        </label>
                    </div>
                    <div class="order-form__contacts-block clearfix">
                        <h3 class="order-form__contacts-title">Личные данные</h3>
                        <span class="order-form__contacts-tip">Мы пришлем вам напоминание в день уборки</span>
                        <label class="order-form__contacts-input input-txt one_half h_left" data-placeholder="Ваше имя">
                            <input type="text" class="input-txt__field " value="<?=$personalName;?>" name="ORDER_PROP_NAME" placeholder="Ваше имя" required="required" data-parsley-error-class="input-txt_state_error" data-parsley-error-message="Поле не заполнено" data-parsley-id="8220"><span class="input-error" id="parsley-id-8220"></span>
                        </label>
                        <label class="order-form__contacts-input input-txt one_half h_right" data-placeholder="Телефон">
                            <input type="tel" class="input-txt__field js-phone-format" value="<?=$personalPhone;?>" name="ORDER_PROP_PERSONAL_PHONE" placeholder="Телефон" required="required" data-parsley-error-class="input-txt_state_error" data-parsley-error-message="Поле не заполнено" data-parsley-id="0087"><span class="input-error" id="parsley-id-0087"></span>
                        </label>
                    </div>
                    <div class="order-form__contacts-block order-login clearfix">
                        <?if (!$USER->IsAuthorized()){?>
                            <h3 class="order-form__contacts-title">Email и пароль</h3>
                            <span class="order-form__contacts-tip">Укажите ваш email и желаемый пароль для регистрации</span>
                            <label class="order-login__input input-txt input-txt_placeholder_small one_half h_left" data-placeholder="E-mail">
                                <input class="input-txt__field" type="email" name="ORDER_PROP_USER_LOGIN" value="<?=$personalEmail;?>" placeholder="E-mail" required="required" data-parsley-type="email" data-parsley-error-class="input-txt_state_error" data-parsley-error-message="Неправильно введен e-mail" data-parsley-id="8882"><span class="input-error" id="parsley-id-8882"></span>
                            </label>
                            <label class="order-login__input input-txt input-txt_placeholder_small one_half h_right" data-placeholder="Пароль">
                                <input value="" class="input-txt__field" type="password" name="ORDER_PROP_USER_PASSWORD" placeholder="Пароль" required="required" data-parsley-minlength="6" data-parsley-error-class="input-txt_state_error" data-parsley-error-message="Минимальная длина пароля - 6 символов" data-parsley-id="3237"><span class="input-error" id="parsley-id-3237"></span>
                            </label>
                            <?if ($arResult["errorLogin"])
                                echo "<div style='clear:both;'></div><p style='color:red;'>".$arResult["errorLogin"]."</p>";
                            ?>
                        <?}else{?>
                            <h3 class="order-form__contacts-title">Email</h3>
                            <p>Вы вошли под логином <b><?=$personalEmail;?></b></p>
                            <input type="hidden" name="ORDER_PROP_USER_LOGIN" value="<?=$personalEmail;?>">
                            <input type="hidden" name="AUTH" value="Y">
                        <?}?>
                    </div>
                </div>
            </fieldset>

        	<button class="order-form__next btn btn_with_icons btn_responsive_true" id="save_contactinfo" value="submit" name="submit">
        	    <span class="btn__icon btn__icon_type_place"></span>Оформить заказ<span class="btn__icon btn__icon_right btn__icon_type_forward"></span>
        	</button>


            <div class="forMobile">
                <div class="fixed_block">
                    <p class="total__item total__item_summary clearfix ttl_prc">
                        <span>
                            Стоимость уборки
                            <span style="color:#898989;"> (&#126;<?=$_SESSION["DURATION"]?> ч) </span>
                        </span>
                        <span class="pull-right">
                            <?=number_format($_SESSION["periodTotalPrice"], 0, '.', '&nbsp;')?> <span class="rouble">Р</span>
                        </span>
                    </p>
                    <button class="order-form__next btn btn_with_icons btn_responsive_true" id="save_contactinfo" value="submit" name="submit">
                        <span class="btn__icon btn__icon_type_place"></span>Оформить заказ<span class="btn__icon btn__icon_right btn__icon_type_forward"></span>
                    </button>
                </div>
            </div>
        <?}?>
    <?}?>
</form>

<script>
    $('.js-city-select-time').selectize();
    $('.js-city-select-city').selectize();
</script>