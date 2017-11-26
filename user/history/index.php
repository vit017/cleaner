<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 13.05.14
 * Time: 13:43
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Все уборки');
?>
    <div class="container">
        <h1 class="page-title"><?$APPLICATION->ShowTitle()?></h1>
        <section class="page-blocks clearfix">
            <div class="page-blocks__item page-blocks__item_type_main">
                <?
                if($_REQUEST["CHANGE"]){
                    //$APPLICATION->IncludeComponent('breadhead:order.calendar', 'change', array(array(5), 'ORDER_ID'=>$_REQUEST['ID']));
                }
                else{
                    $_REQUEST['view'] = 'button';
                    $_REQUEST["show_all"]= 'Y';
                    $APPLICATION->IncludeComponent("bitrix:sale.personal.order", ".default", array(
                        "PROP_1" => array(
                        ),
                        "ACTIVE_DATE_FORMAT" => "d.m.Y",
                        "SEF_MODE" => "N",
                        "SEF_FOLDER" => "/user/history/",
                        "CACHE_TYPE" => "A",
                        "CACHE_TIME" => "10",
                        "CACHE_GROUPS" => "Y",
                        "ORDERS_PER_PAGE" => "20",
                        "PATH_TO_PAYMENT" => "/order/payment.php",
                        "PATH_TO_BASKET" => "/order/basket/",
                        "SET_TITLE" => "Y",
                        "SAVE_IN_SESSION" => "Y",
                        "NAV_TEMPLATE" => "",
                        "CUSTOM_SELECT_PROPS" => array(
                        ),
                        "HISTORIC_STATUSES" => array(
                            0 => "F",
                        ),
                        "STATUS_COLOR_A" => "gray",
                        "STATUS_COLOR_N" => "green",
                        "STATUS_COLOR_C" => "gray",
                        "STATUS_COLOR_F" => "gray",
                        "STATUS_COLOR_M" => "gray",
                        "STATUS_COLOR_PSEUDO_CANCELLED" => "red"
                    ),
                        false
                    );
                }
                ?>



                <?
                $friends=$bonus=0;
                $db = CUser::GetList($b = "ID", $o = "DESC", array("ID"=>$USER->GetID()), array('SELECT'=>array('UF_FRIENDS', 'UF_BONUS')));
                while($sr = $db->Fetch()){
                    if(strlen($sr['UF_FRIENDS']))
                        $friends = $sr['UF_FRIENDS'];
                    if(strlen($sr['UF_BONUS']))
                        $bonus = $sr['UF_BONUS'];
                }
                ?>
                <br>
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
            <?include($_SERVER["DOCUMENT_ROOT"]."/include/user_aside.php");?>
        </section>

    </div>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>