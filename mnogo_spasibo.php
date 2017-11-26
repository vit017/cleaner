<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>

    <script>
        var rf_sponsor="";
        var house="";
        var corpse="";
        var flat="";
        var speaker="";
        var company_name="";
        var company_inn="";
        var company_y="";
        var company_n="";
        var CLIENT_PARAMS = {};
        CLIENT_PARAMS.ORDER_PROP_PERSONAL_PHONE = '+79522006988';
        CLIENT_PARAMS.ORDER_PROP_NAME = 'test';
        CLIENT_PARAMS.ORDER_COMMENT = '';
        //   console.log(JSON.parse('//'));
    </script>

    <pre></pre>

    <div class="container">

        <div class="order-final">
            <h1 class="order-final__title">Спасибо!</h1>
            <div class="order-final__msg">
                <h2 class="order-final__msg-title">Ваш заказ принят</h2>
                <p class="order-final__msg-text">
                    Вы всегда можете просмотреть детали заказа в вашем личном кабинете.
                </p>
            </div>
            <a onclick="yaCounter38469730.reachGoal('private_office_button_step7');" href="/user/" class="order-form__next btn btn_with_icons btn_responsive_true">
                <span class="btn__icon btn__icon_type_user"></span>В личный кабинет<span class="btn__icon btn__icon_right btn__icon_type_forward"></span>
            </a>
            <div class="mnogo_get_card">
                <div class="pic_wrapper">
                    <img src="layout/assets/images/mnogo/card_pic.png">
                </div>
                <div class="txt_wrapper">
                    <p>Вам подарок - карта Много.ру</p>
                    <span>С ней можно получать призы за&nbsp;покупки у&nbsp;нас.</span>
                </div>
                <a href="/mnogo.php" class="btn">Получить подарок</a>
            </div>
        </div>

    </div>






<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>