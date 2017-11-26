<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 13.05.14
 * Time: 11:47
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Личный кабинет");

if(strlen($_REQUEST['backurl'])>0 && $_REQUEST['backurl'] != $APPLICATION->getCurPage()){
    localRedirect($_REQUEST['backurl']);
}
CModule::IncludeModule("sale");
$today = new DateTime();
$todayF = $today->format('Y-m-d');
$arFilter = Array(
    "USER_ID" => $USER->GetID(),
    'CANCELED' => 'N',
    '!STATUS_ID' => array('F'),
);

//current account budget
$db_sales = CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter, false, false, array());
$countOrders = 0;
$closestOrder = false;
while ($ar_sales = $db_sales->Fetch())
{
    if (in_array($ar_sales['STATUS_ID'], array('A', 'N'))) {

        $db_vals = CSaleOrderPropsValue::GetList(
            array("VALUE" => "ASC"),
            array(
                "ORDER_ID" => $ar_sales['ID'],
                "CODE" => 'DATE'
            )
        );

        while ($arVals = $db_vals->Fetch()) {
            $date = new DateTime($arVals['VALUE']);
            $dateF = $date->format('Y-m-d');
            if ($dateF >= $todayF) {
                if ($closestOrder > $date || !$closestOrder) {
                    $closestOrder = $date;
                    $order = $ar_sales['ID'];
                }
            }
        }
    } elseif ($ar_sales['STATUS_ID']=='F'){
        $countOrders++;
    }
}
$todayF = $today->format('d.m.Y');
$dbUserAccount = CSaleUserAccount::GetList(array(), array("USER_ID" => $USER->GetID()));
if ($arUserAccount = $dbUserAccount->GetNext()) {
    if ($arUserAccount["CURRENT_BUDGET"] > 0)
        $budget = round($arUserAccount["CURRENT_BUDGET"]);
}
// friends by repost
$friends=$bonus=0;
$db = CUser::GetList($b = "ID", $o = "DESC", array("ID"=>$USER->GetID()), array('SELECT'=>array('UF_FRIENDS', 'UF_BONUS')));
while($sr = $db->Fetch()){
    if(strlen($sr['UF_FRIENDS']))
        $friends = $sr['UF_FRIENDS'];
    if(strlen($sr['UF_BONUS']))
        $bonus = $sr['UF_BONUS'];
}
?>

    <div class="container">
        <h1 class="page-title">Личный кабинет</h1>
        <section class="page-blocks clearfix">
            <div class="page-blocks__item page-blocks__item_type_main">
                <div class="cabinet">
                    <? if ($countOrders>0) { ?>
                        <div class="cabinet__header clearfix">
                            <h2 class="cabinet__title">У вас <? echo(($countOrders == 1) ? 'была' : 'было') ?> <a href="/user/history/"><?=$countOrders.' '.bhTools::words($countOrders, array('уборка','уборки','уборок'))?></a></h2>
                        </div>
                    <? } ?>
                    <div class="cabinet__content">
                        <?if ($order) {
                            $_REQUEST['view'] = 'button';
                            $APPLICATION->IncludeComponent("breadhead:order.detail", "main", array(
                                "ACTIVE_DATE_FORMAT" => "d.m.Y",
                                "SEF_MODE" => "N",
                                "SEF_FOLDER" => "/user/history/",
                                "PATH_TO_PAYMENT" => "/order/payment.php",
                                "PATH_TO_BASKET" => "/order/basket/",
                                "ID" => $order,

                            ),
                                false
                            );
                        }?>
                        <div class="bonuses">
                            <h3 class="bonuses__title">Ваши бонусы</h3>
                            <p class="bonuses__item">
                                <span class="bonuses__item-name">Бонусы:</span> <?=$bonus;?> <span class="rouble">Р</span>
                            </p>
                            <p class="bonuses__item">
                                <span class="bonuses__item-name">Приглашённых друзей:</span> <?=$friends;?>
                            </p>
                            <div class="bonuses__invite">
                                <p class="bonuses__invite-txt">Пригласите друзей заказать уборку и получите Вместе с ними по 500 рублей скидки на заказ!</p>
                                <input type="hidden" name="USER_ID" value="<?=$USER->GetID()?>">
                                <div class="bonuses__invite-btns clearfix">
                                    <?
                                    $urlVk = FULL_SERVER_NAME . '/order/basket/?ref_inviter='.$USER->GetID();
                                    $urlFb = FULL_SERVER_NAME . '/fb/?ref_inviter='.$USER->GetID();
                                    $urlVk = urlencode($urlVk);
                                    $urlFb = urlencode($urlFb);
                                    ?>

                                    <a target="_blank" href="http://vk.com/share.php?url=<?=$urlVk?>&title=<?=SHARE_TITLE;?>&description=<?=SHARE_DESCRIPTION;?>&image=<?=FULL_SERVER_NAME;?>/layout/assets/images/sm-image1.jpg" class="btn btn_with_icons" style="background: transparent;">
                                        <!-- <span class="btn__icon btn__icon_type_vk"></span>Вконтакте -->
                                        <img src="/layout/assets/images/icon_sprite_social_04.png">
                                    </a>
                                    <a target="_blank" href="http://www.facebook.com/sharer/sharer.php?u=<?=$urlFb?>" class="btn btn_with_icons" style="background: transparent;">
                                        <!-- <span class="btn__icon btn__icon_type_fb"></span>Фейсбук -->
                                        <img src="/layout/assets/images/icon_sprite_social_02.png">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?include($_SERVER["DOCUMENT_ROOT"]."/include/user_aside.php");?>
        </section>
    </div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>