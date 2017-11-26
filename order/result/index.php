<?
if(!$_REQUEST['iframe'] && $_REQUEST['ID']){
    //require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    ?>
    <script>
        window.top.location = window.location.href+'&iframe=Y'
    </script>
    <?
}elseif(/*$_REQUEST['ID'] &&*/ $_REQUEST['result']){
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    $APPLICATION->SetTitle('Спасибо!');

    CModule::IncludeModule('sale');
    $orderID = intVal($_REQUEST['ID']);
    $arOrder = CSaleOrder::GetByID($orderID);

    $cardID = false;
    if ( $_REQUEST['result'] == 'True' ){
        if ( $arOrder['CANCELED'] != 'Y' && $arOrder['PAYED'] != 'Y' ){
            $cardID = bhPayture::getCardID($orderID);
            if ( strlen($cardID) > 0 && $cardID != 'ERROR' ){
                if(bhOrder::setProp($orderID, 'CardId', $cardID, $arOrder["PERSON_TYPE_ID"])){
                    file_put_contents($_SERVER["DOCUMENT_ROOT"].'/cron/orders_done.txt', $orderID."\n", FILE_APPEND);
                    bhOrder::setStatusA($arOrder, array());
                }
            }
        }
        ?>
        <div class="container">
            <div class="order-final">
                <h1 class="order-final__title">Спасибо!</h1>
                <div class="order-final__msg">
                    <h2 class="order-final__msg-title">Ваш заказ принят</h2>
                    <p class="order-final__msg-text">
                        Вы всегда можете просмотреть
                        детали заказа
                        в вашем личном кабинете.
                    </p>
                </div>
                <a href="/user/" class="order-form__next btn btn_with_icons btn_responsive_true">
                    <span class="btn__icon btn__icon_type_user"></span>В личный кабинет<span class="btn__icon btn__icon_right btn__icon_type_forward"></span>
                </a>
            </div>

        </div>
    <?}else{
        ?>
        <div class="container">
            <div class="order-final">
                <h1 class="order-final__title order-final__title_error">Ошибка!</h1>
                <div class="order-final__msg">
                    <h2 class="order-final__msg-title">Проблема с картой</h2>
                    <p class="order-final__msg-text">
                        В ходе проверки вашей карты возникли проблемы: возможно,  на ней сейчас недостаточно средств или она недействительна. Пожалуйста, попробуйте ввести данные карты еще раз или свяжитесь с нашими специалистами по телефону:<br/><strong><?=$_SESSION['PHONE']?></strong>
                    </p>
                </div>
                <?

                if(!empty($arOrder)){
                    if (intval($arOrder["PAY_SYSTEM_ID"]))
                        $arOrder["PAY_SYSTEM"] = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"], $arOrder["PERSON_TYPE_ID"]);
                    $arOrder["PAY_SYSTEM"]["PSA_ACTION_FILE"] = $pathToAction;

                    if ($arOrder["PAYED"] != "Y" && $arOrder["CANCELED"] != "Y" && !$cardID){
                        if (intval($arOrder["PAY_SYSTEM_ID"])){
                            $dbPaySysAction = CSalePaySystemAction::GetList(
                                array(),
                                array(
                                    "PAY_SYSTEM_ID" => $arOrder["PAY_SYSTEM_ID"],
                                    "PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"]
                                ),
                                false,
                                false,
                                array("NAME", "ACTION_FILE", "NEW_WINDOW", "PARAMS", "ENCODING")
                            );

                            if ($arPaySysAction = $dbPaySysAction->Fetch()){
                                if (strlen($arPaySysAction["ACTION_FILE"])){
                                    $arOrder["CAN_REPAY"] = "Y";

                                    if ($arPaySysAction["NEW_WINDOW"] == "Y"){
                                        $arOrder["PAY_SYSTEM"]["PSA_ACTION_FILE"] = htmlspecialcharsbx($arOrder["PATH_TO_PAYMENT"]).'?ORDER_ID='.urlencode(urlencode($arOrder["ACCOUNT_NUMBER"]));
                                    }else{
                                        CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"], $arPaySysAction["PARAMS"]);

                                        $pathToAction = $_SERVER["DOCUMENT_ROOT"].$arPaySysAction["ACTION_FILE"];
                                        $pathToAction = str_replace("\\", "/", $pathToAction);
                                        while (substr($pathToAction, strlen($pathToAction) - 1, 1) == "/")
                                            $pathToAction = substr($pathToAction, 0, strlen($pathToAction) - 1);
                                        if (file_exists($pathToAction)){
                                            if (is_dir($pathToAction) && file_exists($pathToAction."/payment.php")) $pathToAction .= "/payment.php";
                                            $arOrder["PAY_SYSTEM"]["PSA_ACTION_FILE"] = $pathToAction;
                                        }
                                        $_REQUEST['view'] = 'button';
                                        include($arOrder["PAY_SYSTEM"]["PSA_ACTION_FILE"]);
                                    }
                                }
                            }
                        }
                    }
                }?>
            </div>
        </div>
    <?
    };
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
}else{
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    //require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    $APPLICATION->SetTitle('Спасибо!');

    CModule::IncludeModule('sale');
    if (isset($_REQUEST['ID'])) {
        $orderID = intVal($_REQUEST['ID']);
        $arOrder = CSaleOrder::GetByID($orderID);
    }
    $cardID = false;
        ?>
    <div class="container">
                <div class="order-final">
                    <h1 class="order-final__title order-final__title_error">Ошибка!</h1>
                    <div class="order-final__msg">
                        <h2 class="order-final__msg-title">Проблема с картой</h2>
                        <p class="order-final__msg-text">
        В ходе проверки вашей карты возникли проблемы: возможно,  на ней сейчас недостаточно средств или она недействительна. Пожалуйста, попробуйте ввести данные карты еще раз или свяжитесь с нашими специалистами по телефону:<br/><strong><?=TOLLFREENUMBER;?></strong>
        </p>
        </div>
        <?

        if(!empty($arOrder)){
            if (intval($arOrder["PAY_SYSTEM_ID"]))
                $arOrder["PAY_SYSTEM"] = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"], $arOrder["PERSON_TYPE_ID"]);
            $arOrder["PAY_SYSTEM"]["PSA_ACTION_FILE"] = $pathToAction;

            if ($arOrder["PAYED"] != "Y" && $arOrder["CANCELED"] != "Y" && !$cardID){
                if (intval($arOrder["PAY_SYSTEM_ID"])){
                    $dbPaySysAction = CSalePaySystemAction::GetList(
                        array(),
                        array(
                            "PAY_SYSTEM_ID" => $arOrder["PAY_SYSTEM_ID"],
                            "PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"]
                        ),
                        false,
                        false,
                        array("NAME", "ACTION_FILE", "NEW_WINDOW", "PARAMS", "ENCODING")
                    );

                    if ($arPaySysAction = $dbPaySysAction->Fetch()){
                        if (strlen($arPaySysAction["ACTION_FILE"])){
                            $arOrder["CAN_REPAY"] = "Y";

                            if ($arPaySysAction["NEW_WINDOW"] == "Y"){
                                $arOrder["PAY_SYSTEM"]["PSA_ACTION_FILE"] = htmlspecialcharsbx($arOrder["PATH_TO_PAYMENT"]).'?ORDER_ID='.urlencode(urlencode($arOrder["ACCOUNT_NUMBER"]));
                            }else{
                                CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"], $arPaySysAction["PARAMS"]);

                                $pathToAction = $_SERVER["DOCUMENT_ROOT"].$arPaySysAction["ACTION_FILE"];
                                $pathToAction = str_replace("\\", "/", $pathToAction);
                                while (substr($pathToAction, strlen($pathToAction) - 1, 1) == "/")
                                    $pathToAction = substr($pathToAction, 0, strlen($pathToAction) - 1);
                                if (file_exists($pathToAction)){
                                    if (is_dir($pathToAction) && file_exists($pathToAction."/payment.php")) $pathToAction .= "/payment.php";
                                    $arOrder["PAY_SYSTEM"]["PSA_ACTION_FILE"] = $pathToAction;
                                }
                                $_REQUEST['view'] = 'button';
                                include($arOrder["PAY_SYSTEM"]["PSA_ACTION_FILE"]);
                            }
                        }
                    }
                }
            }
        }?>
        </div>
        </div>
<?
}
