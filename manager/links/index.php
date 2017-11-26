<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 23.01.15
 * Time: 15:24
 */
define('NEED_AUTH', 'Y');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle('Страница создания реферальных ссылок)');
CModule::IncludeModule('sale');
CModule::IncludeModule('catalog');?>
<?$arResult = array();
$urlAdvert = false;
if(isset($_POST['set'])){
    if(strlen($_POST['checkField'])>0){
        $arResult['ERROR']['checkField'] = true;
    }elseif(strlen(trim($_POST['MESSAGE']))<=0){
        $arResult['ERROR']['MESSAGE'] = true;
        $arResult['ERROR_MESSAGE'] = "Не введен текст сообщения пользователю";
    }else{
        if($_POST['DISCOUNT']>0){
            $coupon = $_POST['COUPON_'.$_POST['DISCOUNT']];
            $arCoupon = CCatalogDiscountCoupon::GetByID($coupon);
            if(!empty($arCoupon)){
                $urlAdvert = 'http://'.$_SERVER['HTTP_HOST'].'/?utm_Advert='.$arCoupon['COUPON'];
                CCatalogDiscountCoupon::Update($arCoupon['ID'], array('DESCRIPTION'=>trim($_POST['MESSAGE'])));
            }
        }
    }
}
?>


<?

$arCouponsByDiscount = array();
$arDiscounts = array();
$db = CCatalogDiscountCoupon::getList();
while($arCoupon = $db->fetch()){
    if(!isset($arDiscounts[$arCoupon['DISCOUNT_ID']])){
        $arDiscounts[$arCoupon['DISCOUNT_ID']] = $arCoupon['DISCOUNT_NAME'];
    }
    $arCouponsByDiscount[$arCoupon['DISCOUNT_ID']][$arCoupon['ID']] = array('COUPON'=>$arCoupon['COUPON'], 'DESCRIPTION'=>$arCoupon['DESCRIPTION'], 'ACTIVE'=>$arCoupon['ACTIVE']);
}
//xmp($arDiscounts);
?>
<form class="form-section" method="POST">
    <div class="form-section__content">
        <div class="time-input clearfix">
            <?if($urlAdvert){?>
                <label class="form-section__input input-txt input-txt_bg_grey shape" data-placeholder="Ссылка">
                    <input type="text" value="<?=$urlAdvert?>" class="input-txt__field" placeholder="Ссылка">
                </label>
            <?}?>
            <label class="time-input__control shape">
                <select name="DISCOUNT" class="select select_width_full select_search_false js-discount">
                    <?foreach($arDiscounts as $id=>$name){?>
                        <option value="<?=$id?>" <?=$id==$_POST['DISCOUNT']?'selected':''?>><?=$name?></option>
                    <?}?>
                </select>
            </label>
            <?$first = true;?>
            <label class="time-input__control shape">
                <?foreach($arCouponsByDiscount as $idD=>$coupons){?>
                    <select name="COUPON_<?=$idD?>" id="<?=$idD?>" class="select select_width_full select_search_false js-coupons <?=(isset($_POST['DISCOUNT']) && $idD==$_POST['DISCOUNT'])|| (!isset($_POST['DISCOUNT']) && $first)?'':'hidden'?>" >
                        <?foreach($coupons as $idC=>$coupon){?>
                            <option data-text="<?=$coupon['DESCRIPTION']?>" value="<?=$idC?>" <?=$idD==$_POST['DISCOUNT']&&$idC==$_POST['COUPON_'.$idD]?'selected':''?>><?=$coupon['COUPON']?> (<?=$coupon['ACTIVE']?>)</option>
                        <?}?>
                    </select>
                    <?$first = false;?>
                <?}?>
            </label>
        </div>
        <label class="form-section__input input-txt input-txt_bg_grey shape" data-placeholder="Сообщение пользователю">
            <input type="text" name="MESSAGE" value="<?=$_POST['MESSAGE']?>" class="input-txt__field" placeholder="Сообщение пользователю">
            <?if(isset($arResult['ERROR']['MESSAGE'])){
                echo $arResult['ERROR_MESSAGE'];
            }?>
        </label>
        <input type="text" name="checkField" value=""  style="display: none;">
        <input type="submit" name="set" class='btn btn_size_big' value="Создать">
    </div>
</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
<style>
    .hidden{
        display:none!important;
    }
    .shape{
        width:100%;
        margin-left:50px;
    }
</style>
<script>
    $(document).ready(function() {
        $('body').on('change', '.js-discount', function () {
            var discount = $(this).val();
            $('.js-coupons').addClass('hidden');
            $('#' + discount).removeClass('hidden');
            var text = $('#' + discount).find('option:first').data('text');
            $('input[name=MESSAGE]').val(text);
        });
        $('body').on('change', '.js-coupons', function () {
            var text = $(this).data('text');
            $('input[name=MESSAGE]').val(text);
        });
    });
</script>
