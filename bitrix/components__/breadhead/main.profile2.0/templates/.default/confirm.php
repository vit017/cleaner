<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 02.06.14
 * Time: 11:42
 */
?>
<form class="settings-form js-form-validate" method="post"  name="form1" action="<?=$arResult["FORM_TARGET"]?>" enctype="multipart/form-data">
    <h2 class="settings-form__title">Подтверждение номера телефона</h2>
    <p>
        На ваш номер телефона <strong><?=$_SESSION['PHONE_CONFIRM_NUMBER']?></strong> (<strong class="link" data-step="32" onclick="window.form1.BACK_PHONE.value='Y';  window.form1.submit();">изменить</strong>) было выслано смс с кодом подтверждения. <br/>
        Пожалуйста, введите код в поле ниже.
    </p>
    <?=$arResult["BX_SESSION_CHECK"]?>
    <input type="hidden" name="lang" value="<?=LANG?>" />
    <input type="hidden" name="ID" value=<?=$arResult["ID"]?> />
    <input type="hidden" name="LOGIN" value="<? echo $arResult["arUser"]["LOGIN"]?>" />
	<input type="hidden" name="EMAIL" value="<? echo $arResult["arUser"]["EMAIL"]?>" />
    <input type="hidden" name="save" value="Y" />
	<input type="hidden" name="BACK_PHONE" value="" />
    <input type="hidden" name="PERSONAL_PHONE" value="<?=$_REQUEST["PERSONAL_PHONE"]?$_REQUEST["PERSONAL_PHONE"]:$arResult["arUser"]["PERSONAL_PHONE"]?>" />
    <div class="sms-confirm settings-form__input">
        <label class="sms-confirm__input input-txt <? if(strlen($arResult["ERROR_MESSAGE"])>0) { ?>input-txt_state_error<? } ?>" data-placeholder="Код подтверждения">
            <input class="input-txt__field" name="confirm_code" type="text" placeholder="Код подтверждения">
            <? if(strlen($arResult["ERROR_MESSAGE"])>0) { ?>
                <span class="input-txt__error"><? echo $arResult["ERROR_MESSAGE"]; ?></span>
            <? } ?>
        </label>
	    <?if($arResult['CHECK_NUMBER'] == 'Y'){?>
	        <span class="sms-confirm__resend js-sms-resend">
	            <input class="sms-confirm__resend-input" type="checkbox" name="RESEND"/>
	            <span class="sms-confirm__link">Получить смс повторно</span>
	        </span>
	    <?}?>
    </div>
    <div class="settings-form__controls">
        <button class="order-form__next btn btn_with_icons btn_responsive_true">Сохранить</button>
    </div>
</form>
<!--<div class="container">

    <h1 class="page-title">Заказ уборки</h1>

    <section class="order">
        <header class="order__header">

        </header>
        <div class="page-block order__content">
            <form method="post" class="order-form" name="form1" action="<?/*=$arResult["FORM_TARGET"]*/?>" enctype="multipart/form-data">
                <?/*=$arResult["BX_SESSION_CHECK"]*/?>
                <input type="hidden" name="lang" value="<?/*=LANG*/?>" />
                <input type="hidden" name="ID" value=<?/*=$arResult["ID"]*/?> />
                <input type="hidden" name="LOGIN" value="<?/* echo $arResult["arUser"]["LOGIN"]*/?>" />
                <input type="hidden" name="save" value="Y" />
                <fieldset class="order-form__fieldset">
                    <h2 class="order-form__fieldset-title">Подтверждение номера телефона</h2>
                    <p>
                        На ваш номер телефона было выслано смс с кодом подтверждения. Пожалуйста, введите код в поле ниже.
                    </p>
                    <?/*if(strlen($arResult["ERROR_MESSAGE"])>0) echo $arResult["ERROR_MESSAGE"];*/?>
                    <input type="hidden" name="PERSONAL_PHONE" value="<?/*=$arResult["arUser"]["PERSONAL_PHONE"]*/?>" />

                    <div class="sms-confirm">
                        <label class="sms-confirm__input input-txt" data-placeholder="Код подтверждения">
                            <input class="input-txt__field" name="confirm_code" type="text" placeholder="Код подтверждения">
                        </label>
                        <span class="sms-confirm__link">
                            <input type="submit" name="RESEND" value="Получить смс повторно">
                        </span>
                    </div>
                    <div class="order-form__fieldset-tip">
                        <h3>Приватность</h3>
                        <p>
                            Все введенные вами данные абсолютно конфиденциальны
                            и необходимы только для работы сервиса.
                        </p>
                    </div>
                </fieldset>
                <button class="order-form__next btn btn_with_icons btn_responsive_true">
                    <span class="btn__icon btn__icon_type_save"></span>Сохранить<span class="btn__icon btn__icon_right btn__icon_type_forward"></span>
                </button>
            </form>
        </div>
    </section>
</div>-->