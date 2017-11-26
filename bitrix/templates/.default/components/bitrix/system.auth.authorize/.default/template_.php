<pre>
<?
//print_r($arResult);
?>
<?
//print_r($arParams);
?>
    </pre>
<div class="auth-block">
    <form class="auth-form js-form-validate" name="system_auth_form<?=$arResult["RND"]?>" method="post" target="_top" action="/auth/auth_phone.php">
        <? if($arResult['BACKURL']!=''){?>
        <input type="hidden" name="backurl" value="<?=$arResult['BACKURL']?>" />
        <?}?>
        <? foreach ( $arResult['POST'] as $key => $value){?>
        <input type="hidden" name="<?=$key?>" value="<?=$value?>" />
        <?}?>

        <input type="hidden" name="AUTH_FORM" value="Y" />
        <input type="hidden" name="TYPE" value="AUTH" />

        <h2 class="auth-form__title">Вход</h2>
        <input type="tel" class="input-txt__field js-phone-format" value="" name="ORDER_PROP_PERSONAL_PHONE" placeholder="Телефон" required="required" data-parsley-error-class="input-txt_state_error" data-parsley-error-message="Поле не заполнено" data-parsley-id="0061"><span class="input-error" id="parsley-id-0061"></span><span class="input-error" id="parsley-id-0087"></span>
        <br />
        <!--<label class="order-form__contacts-input input-txt one_half h_right" data-placeholder="Телефон">
        </label>-->
        <!--<label class="input-txt input-txt_width_full <? if ($arParams['AUTH_RESULT']['TYPE'] == 'ERROR')  {?>input-txt_state_error<?}?>" data-placeholder="E-mail">
            <input class="input-txt__field" type="email" name="USER_LOGIN" value="<?=$arResult['USER_LOGIN'] ?>" placeholder="E-mail" autofocus="autofocus" required="required" data-parsley-type="email" data-parsley-error-class="input-txt_state_error" data-parsley-error-message="Неправильно введен e-mail"/>

        </label>-->
        <? if ($arParams['AUTH_RESULT']['TYPE'] == 'ERROR'){?>
            <span class="input-txt__error"><?=$arParams['~AUTH_RESULT']['MESSAGE']?></span>
        <?}?>
        <label class="input-txt input-txt_width_full" data-placeholder="Пароль">
            <input class="input-txt__field" name="USER_PASSWORD" type="password" placeholder="Пароль"   required="required" data-parsley-minlength="6" data-parsley-error-class="input-txt_state_error" data-parsley-error-message="Минимальная длина пароля - 6 символов"/>
        </label>
      <span class="auth-form__forgot-pass">
        <a href="<?=$arResult['AUTH_FORGOT_PASSWORD_URL']?>" rel="nofollow">Забыли пароль?</a>
      </span>
        <button class="btn btn_responsive_true" name="Login"><span class="btn__icon btn__icon_type_login"></span>Войти<span class="btn__icon btn__icon_right btn__icon_type_forward"></span></button>
    </form>
    <br>
    <p>Еще не зарегистрированы?<br><a href="/order/basket/">Закажите первую уборку</a></p>
</div>
