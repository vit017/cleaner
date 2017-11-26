<?
/**
 * @global CMain $APPLICATION
 * @param array $arParams
 * @param array $arResult
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
?>

<form class="settings-form" method="post" name="form1" action="<?=$arResult['FORM_TARGET']?>" enctype="multipart/form-data">
    <?=$arResult["BX_SESSION_CHECK"]?>
    <input type="hidden" name="lang" value="<?=LANG?>" />
    <input type="hidden" name="ID" value=<?=$arResult["ID"]?> />
    <input type="hidden" name="EMAIL" value="<?=$arResult['arUser']['EMAIL']?>" />
    <input type="hidden" name="LOGIN" value="<?=$arResult['arUser']['LOGIN']?>" />
    <input type="hidden" name="FLAT_ID" value="<?=$arResult['FLAT']['ID']?>" />
    <h2 class="settings-form__title">Адрес</h2>
    <label class="settings-form__input">
        <select class="select select_width_full select_search_false js-city-select_personal" name="PERSONAL_CITY">
            <?foreach($arResult["arUser"]['PERSONAL_CITY']['VARIANTS'] as $key=>$value){?>
                <option <?=$arResult['arUser']['PERSONAL_CITY']['VALUE']==$key?'selected':''?> value="<?=$key?>"><?=$value?></option>
            <?}?>
        </select>
    </label>
    <label class="settings-form__input input-txt" data-placeholder="Улица, дом, корпус, номер квартиры">
        <input class="input-txt__field" name="PERSONAL_STREET" type="text" placeholder="Улица, дом, корпус, номер квартиры" value="<?=$arResult['arUser']['PERSONAL_STREET']?>" />
    </label>
    <div class="settings-form__controls">
        <input type="submit" class="btn btn_type_second" name="save" value="Сохранить" />
    </div>
</form>

<!-- Состав заказа по умолчанию-->
<form class="settings-form" method="post" name="flat" action="<?=$arResult['FORM_TARGET ']?>" enctype="multipart/form-data">
    <?=$arResult["BX_SESSION_CHECK"]?>
    <input type="hidden" name="lang" value="<?=LANG?>" />
    <input type="hidden" name="ID" value=<?=$arResult["ID"]?> />
    <input type="hidden" name="EMAIL" value="<?=$arResult['arUser']['EMAIL']?>" />
    <input type="hidden" name="LOGIN" value="<?=$arResult['arUser']['LOGIN']?>" />
    <input type="hidden" name="FLAT_ID" value="<?=$arResult['FLAT']['ID']?>" />
    <input type="hidden" name="FLAT" value="Y" />
    <h2 class="settings-form__title">Параметры квартиры</h2>
    <h3 class="settings-form__title">Площадь квартиры</h3>
    <div class="single-input clearfix">
        <label class="single-input__control">
            <select name="FLAT_SIZE<?//=$arResult['FLAT']['flat']['ID']?>" class="select select_width_full js-custom-select js-update-basketForm select_flat select_flat_nomargin">
                <?foreach($arResult['FLAT']['flat']['VARIANTS'] as $item){?>
                    <option value="<?=$item['ID']?>" <?=$item['VALUE']>0?'selected':''?>><?=$item['NAME_FORMATED']?></option>
                <?}?>
            </select>
        </label>
    </div>
    <h3 class="order-form__fieldset-title">Дополнительно</h3>
    <div class="additional-control">
        <?foreach($arResult['FLAT']['services']['SERVICES'] as $item){?>
            <?if ( strlen($item['PROPERTIES']['SET_QUANTITY']['VALUE']) > 0 ){?>
                <span class="additional-control__item additional-control__item-with-params <?=$item['VALUE']>0?'checked':''?>">
                        <label class="additional-control__item-content">
                            <input class="additional-control__item-input js-set_qnt js-update-basketForm" name="SERVICES[<?=$item['ID']?>][<?=$arParams['PRODUCT_QUANTITY_VARIABLE']?>]" data-name="<?=$item["CODE"]?>" value="<?=$item['ID']?>" type="checkbox" <?=$item['VALUE']>0?'checked':''?>>
                            <span class="additional-control__item-bg"></span>
                            <span class="additional-control__item-title "><img src="<?=$item['PROPERTIES']['ICON']['VALUE']?>"><?=$item['NAME_FORMATED']?></span>
                            <span class="additional-control__item-title__description"><?=$item['PROPERTIES']['SET_NAME']['VALUE']?></span>
                        </label>
                        <div class="controls">
                            <span class="qnt-button js-minus"></span>
                            <label class="js-quantity"><?=$item['VALUE']>0?$item['VALUE']:$item['PROPERTIES']['DEFAULT']['VALUE']?></label>
                            <input type="hidden" class="js-input-qnt js-update-basketForm"  data-step="<?=strlen($item['PROPERTIES']['STEP']['VALUE'])>0?$item['PROPERTIES']['STEP']['VALUE']:1?>" name="QUANTITY_<?=$item['ID']?>" value="<?=$item['VALUE']>0?$item['VALUE']:$item['PROPERTIES']['DEFAULT']['VALUE']?>">
                            <span class="qnt-button js-plus"></span>
                        </div>
                    </span>
            <?} else {?>
                <span class="additional-control__item">
                        <label class="additional-control__item-content">
                            <input class="additional-control__item-input js-update-basketForm" name="SERVICES[<?=$item['ID']?>][<?=$arParams['PRODUCT_QUANTITY_VARIABLE']?>]" data-name="<?=$item["CODE"]?>" value="<?=$item['ID']?>" type="checkbox" <?=$item['VALUE']>0?'checked':''?>>
                            <span class="additional-control__item-bg"></span>
                            <span class="additional-control__item-title"><img src="<?=$item['PROPERTIES']['ICON']['VALUE']?>"><?=$item['NAME_FORMATED']?></span>
                        </label>
                    </span>
            <?}?>
        <?}?>
    </div>
    <?if ( $arResult['WISH_CLEANER'] ){?>
        <input type="hidden" class="js-cleaner-option" data-val='<?=json_encode($arResult['WISH_CLEANER'])?>'>
        <input type="hidden" name="wishCleaner" value="<?=$arResult['chosen_wish_cleaner']?>">
        <div class="time-input clearfix">
            <h3 class="order-form__fieldset-title">Предпочтительный клинер</h3>
            <label class="time-input__control">
                <select class="select select_width_full js-select_cleaner gselect_cleaner" name="WISH_CLEANER" placeholder="Не важно">
                </select>
                <div id="result"></div>
            </label>
        </div>
    <?}?>
    <div class="settings-form__controls">
        <input type="submit" class="btn btn_type_second" name="save" value="Сохранить" />
    </div>
</form>
<!-- Редактирование личных данных -->
<form class="settings-form" method="post" name="flat" action="<?=$arResult['FORM_TARGET ']?>" enctype="multipart/form-data">
    <?=$arResult["BX_SESSION_CHECK"]?>
    <input type="hidden" name="lang" value="<?=LANG?>" />
    <input type="hidden" name="ID" value=<?=$arResult["ID"]?> />
    <input type="hidden" name="LOGIN" value="<?=$arResult['arUser']['LOGIN']?>" />
    <h2 class="settings-form__title">Личные данные</h2>
    <label class="settings-form__input input-txt" data-placeholder="Ваше имя">
        <input class="input-txt__field" type="text" name="NAME" placeholder="Ваше имя" value="<?=$arResult['arUser']['NAME']?>" required="required" data-parsley-error-class="input-txt_state_error" data-parsley-error-message="Поле не заполнено"/>
    </label>
    <label class="settings-form__input input-txt <?=$arResult['ERROR_MESSAGE']?'input-txt_state_error':''?>" data-placeholder="E-mail">
        <input class="input-txt__field" type="email" name="EMAIL" placeholder="E-mail" value="<?=$arResult['arUser']['EMAIL']?>" required="required" data-parsley-type="email" data-parsley-error-class="input-txt_state_error" data-parsley-error-message="Неправильно введен e-mail" />
        <?if($arResult['ERROR_MESSAGE']){?>
            <span class="input-txt__error"><?=$arResult['ERROR_MESSAGE']?></span>
        <?}?>
    </label>
    <label class="settings-form__input input-txt" data-placeholder="Телефон">
        <input class="input-txt__field js-phone-format" type="text" name="PERSONAL_PHONE" placeholder="Телефон" value="<?=$arResult['arUser']['PERSONAL_PHONE']?>" required="required" data-parsley-error-class="input-txt_state_error" data-parsley-error-message="Поле не заполнено"/>
    </label>
    <div class="settings-form__controls">
        <input type="submit" class="btn btn_type_second js-submit" name="save" value="Сохранить" />
    </div>
</form>

<!-- Смена пароля -->
<form class="settings-form" method="post" name="flat" action="<?=$arResult['FORM_TARGET ']?>" enctype="multipart/form-data">
    <?=$arResult["BX_SESSION_CHECK"]?>
    <input type="hidden" name="lang" value="<?=LANG?>" />
    <input type="hidden" name="ID" value=<?=$arResult["ID"]?> />
    <input type="hidden" name="EMAIL" value="<?=$arResult['arUser']['EMAIL']?>" />
    <input type="hidden" name="LOGIN" value="<?=$arResult['arUser']['LOGIN']?>" />
    <h2 class="settings-form__title">Смена пароля</h2>
    <?if($arResult['strProfileError']){?>
        <span><?=$arResult['strProfileError']?></span>
    <?}?>

    <label class="settings-form__input input-txt" data-placeholder="Новый пароль">
        <input class="input-txt__field" id="new-pass" name="NEW_PASSWORD" type="password" placeholder="Новой пароль" required="required" data-parsley-minlength="6" data-parsley-error-class="input-txt_state_error" data-parsley-error-message="Минимальная длина пароля - 6 символов"/>
    </label>
    <label class="settings-form__input input-txt" data-placeholder="Подтвердите новый пароль">
        <input class="input-txt__field" name="NEW_PASSWORD_CONFIRM" type="password" placeholder="Подтвердите новый пароль" required="required" data-parsley-minlength="6" data-parsley-equalto="#new-pass" data-parsley-error-class="input-txt_state_error" data-parsley-error-message="Пароли не совпадают"/>
    </label>
    <div class="settings-form__controls">
        <input type="submit" class="btn btn_type_second" name="save" value="Сохранить" />
    </div>
</form>