<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 04.08.14
 * Time: 13:47
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Заказ уборки - ');
if($_REQUEST['ID'] <= 0) return false;
CModule::IncludeModule('sale');
$orderID = intVal($_REQUEST['ID']);
$arOrder = CSaleOrder::GetByID($orderID);

    if(!empty($arOrder)){
        if (intval($arOrder["PAY_SYSTEM_ID"]))
            $arOrder["PAY_SYSTEM"] = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"], $arOrder["PERSON_TYPE_ID"]);

        if ($arOrder["PAYED"] != "Y" && $arOrder["CANCELED"] != "Y")
        {
            if (intval($arOrder["PAY_SYSTEM_ID"]))
            {
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

                if ($arPaySysAction = $dbPaySysAction->Fetch())
                {
                    if (strlen($arPaySysAction["ACTION_FILE"]))
                    {
                        $arOrder["CAN_REPAY"] = "Y";
                        if ($arPaySysAction["NEW_WINDOW"] == "Y")
                        {
                            $arOrder["PAY_SYSTEM"]["PSA_ACTION_FILE"] = htmlspecialcharsbx($arOrder["PATH_TO_PAYMENT"]).'?ORDER_ID='.urlencode(urlencode($arOrder["ACCOUNT_NUMBER"]));
                        }
                        else
                        {
                            CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"], $arPaySysAction["PARAMS"]);

                            $pathToAction = $_SERVER["DOCUMENT_ROOT"].$arPaySysAction["ACTION_FILE"];
                            $pathToAction = str_replace("\\", "/", $pathToAction);
                            while (substr($pathToAction, strlen($pathToAction) - 1, 1) == "/")
                                $pathToAction = substr($pathToAction, 0, strlen($pathToAction) - 1);
                            if (file_exists($pathToAction))
                            {
                                if (is_dir($pathToAction) && file_exists($pathToAction."/payment.php"))
                                    $pathToAction .= "/payment.php";

                                $arOrder["PAY_SYSTEM"]["PSA_ACTION_FILE"] = $pathToAction;
                            }
                            ?>
                            <div id="js-basket_form">
                            <div class="container">

                            <h1 class="page-title">Заказ уборки</h1>tt
                            <? echo $arOrder["PAY_SYSTEM"]["PSA_ACTION_FILE"].BX_ROOT;?>
                            <section class="order">
                                <header class="order__header clearfix">
                                </header>
                                <div class="page-block order__content">
                                <?include($arOrder["PAY_SYSTEM"]["PSA_ACTION_FILE"]);?>
                                </div>
                            </section>

                            </div></div>
                            <?

                        }
                    }
                }
            }
        }


};
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");