<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 24.01.15
 * Time: 14:54
 */
define('NEED_AUTH', 'Y');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Страница снятия оплаты (для менеджера)');
CModule::IncludeModule('sale');

$arResult = array();

if(isset($_POST['payment'])){
    if(strlen($_POST['checkField'])>0){
        $arResult['ERROR']['checkField'] = true;
    }elseif(strlen($_POST['ORDER'])==0 || intVal($_POST['ORDER'])<=0){
        $arResult['ERROR']['ORDER'] = true;
    }else{
        $ID = trim($_POST['ORDER']);

    }

    if(empty($arResult['ERROR']) || !isset($arResult['ERROR'])){
        if ($ID>0) {
            $arOrder = CSaleOrder::getById($ID);

            $db_vals = CSaleOrderPropsValue::GetList(
                array("SORT" => "ASC"),
                array(
                    "ORDER_ID" => $arOrder['ID'],
                    'CODE' => array('CardId', 'PAYTURE_ATTEMP')
                )
            );
            $attemp = 0;
            while ($arVals = $db_vals->Fetch()) {
                if ( $arVals['CODE'] == 'CardId' ){
                    $cardID = $arVals['VALUE'];
                } elseif ( $arVals['CODE'] == 'PAYTURE_ATTEMP' ){
                    $attemp = intVal($arVals['VALUE']);
                }
            }

            if ($cardID) {
                if (strlen($cardID) > 0) {
                    //unblock 1RUB
                    $blockAmount = bhPayture::getStatus($arOrder['ID']);
                    if ($blockAmount > 0) {
                        $unblock = bhPayture::getUnblock($arOrder['ID'], $blockAmount);
                        file($unblock);
                    }

                    $new_amount = round($arOrder["PRICE"] - $arOrder['SUM_PAID'], -1);
                    $payStatus = bhPayture::getPay($arOrder['ID'], $cardID, $new_amount, $attemp);
                    $attemp++;
                    bhOrder::setProp($arOrder['ID'], 'PAYTURE_ATTEMP', $attemp, $arOrder["PERSON_TYPE_ID"]);

                    if ($payStatus) {
                        CSaleOrder::Update($arOrder['ID'], array('PS_STATUS' => 'Y', 'PS_SUM' => $new_amount));
                        CSaleOrder::PayOrder($arOrder['ID'], 'Y', false);
                    }
                    else {
                        CSaleOrder::PayOrder($arOrder['ID'], 'N', false);
                        CSaleOrder::Update($arOrder['ID'], array('PS_STATUS' => '', 'PS_SUM' => ''));
                    }
                }
            }
        }
    }
}

$dbOrder = CSaleOrder::getList(array('ID'=>'desc'), array('PAYED'=>'N', 'STATUS_ID'=> 'F', 'CANCELED'=>'N', 'PAY_SYSTEM_ID'=>2), false,false,array('ID', 'PRICE', 'DISCOUNT_VALUE', 'USER_LOGIN'));

while($arOrder = $dbOrder->fetch()){
    $arResult['ORDERS'][] = $arOrder;
}
?>
<form class="form-section" method="POST">
    <div class="form-section__content">

    <table border="1" cellspacing="2" cellpadding="5" style="width:690px; text-align: center;">
        <tr>
            <th colspan="5" style="line-height: 30px">
                Заказы для снятия оплаты
            </th>
        </tr>
        <tr style="font-weight: bold">
            <td>Выбрать</td>
            <td width="20%">ID</td>
            <td width="15%">К оплате</td>
            <!--<td width="15%">Размер<br/>скидки</td>-->
            <td>E-mail</td>
        </tr>
        <?
        foreach($arResult['ORDERS'] as $order){
          ?>
            <tr>
                <td style="background-color: #07b19a"><input type="radio" name="ORDER" value="<?=$order['ID']?>"></td>
                <td style="font-weight: bold"><a target="_blank" href="<?=$_SERVER['HTTP_HOST']?>/bitrix/admin/sale_order_detail.php?ID=<?=$order['ID']?>&filter=Y&set_filter=Y&lang=ru"><?=$order['ID']?></td>
                <td><?=round($order['PRICE'], -1)?></td>
                <!--<td><?=$order['DISCOUNT_VALUE']?></td>-->
                <td><?=$order['USER_LOGIN']?></td>
            </tr>
        <?
        }?>
    </table>
    <br/>
    <input type="text" name="checkField" value=""  style="display: none;">
    <input type="submit" name="payment" class='btn btn_size_big' value="Снять оплату">
    </div>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");