<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $USER;

$now = new DateTime('-'.bhSettings::$timeout.' minutes');
$ids = array();
$arOrders = array();
$db = CSaleOrder::getList(array(), array('DATE_FROM' => $now->format('d.m.Y H:i:s'), 'ACTIVE' => 'Y', 'CANCEL' => 'N'));
while( $order = $db->fetch()){
    $arOrders[$order['ID']] = $order;
    $ids[] = $order['ID'];
}

$arProps = bhOrder::getProps($ids);

$inWeek = new DateTime('+ 7 days');
foreach($arProps as $id => $props ){
//    $arOrders[$id]['PROPS'] = $props;
    $date = new DateTime($props['DATE']['VALUE']);

    if ( $inWeek->getTimestamp() < $date->getTimestamp() && ((isset($props['wish_cleaner']) && $props['wish_cleaner']['VALUE'] != $USER->getID()) || !isset($props['wish_cleaner'])) ) {
        unset($arOrders[$id]);
        continue;
    }

    $arOrders[$id]['PROPS_FORMATED'] = bhOrder::formatProps($props);
    $arOrders[$id]['ACTION'] = bhOrder::getActions($arOrders[$id], $props, $USER->getID());

    if ( $arOrders[$id]['ACTION']['TAKE'] != 'Y' ) {
        unset($arOrders[$id]);
    }
}

$cOrders = count($arOrders);
if ( $cOrders > 0 ) {
    foreach($arOrders as $order){?>
        <?$props = $order['PROPS_FORMATED'];?>
        <div class="order-detail__content fresh-orders">
            <div class="order-item js-show_detail">
                <h4 class="order-item__title cleaner-title ">Заказ №<?=$order['ID']?></h4>
                <p class="order-item__param"><?=$props['DATE']['VALUE_FORMATED']?></p>
                <p class="order-item__param"><?=$props['TIME']['VALUE_FORMATED']?> (<?=$props['DURATION']['VALUE_FORMATED']?>)</p>
                <p class="order-item__param"><span class="order-item__param-name">Адрес:</span> <?=bhTools::cutAddress($props['PERSONAL_STREET']['VALUE_FORMATED'])?></p>
                <p class="order-item__param"></p>
                <div class="order-detail js-order_detail" style="display: none">
                    <?if ( $order['ACTION']['TAKE'] == 'Y' ) {?>
                        <button class="btn js-cleaner-action" data-action="add" data-order="<?=$order['ID']?>" data-cleaner="<?=$USER->getID()?>">
                            Взять
                        </button>
                    <?}?>
                </div>
            </div>
        </div>
    <?}
    $avail = bhTools::getAvailOrders();

    $cOrders = intval($avail) + intVal($cOrders);

    bhTools::setAvailOrders($cOrders);
    $avail = bhTools::getAvailOrders();
    $html = ob_get_contents();
    ob_clean();

    $arResult = array('STATUS' => 'OK', 'HTML' => $html, 'AVAIL' => $avail);
} else {
    $arResult = array('STATUS' => 'EMPTY');
}
echo json_encode($arResult);

return;
?>