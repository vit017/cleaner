
<form class="start-form" name="BASKET_CATALOG" method="POST" style="width:auto;">
    <?if(!empty($arResult['HIDDEN'])){?>
        <?foreach($arResult['HIDDEN'] as $hidden){?>
            <input type="hidden" name="<?=$hidden['NAME']?>" value="<?=$hidden['VALUE']?>">
        <?}?>
    <?}?>

    <input type="hidden" name="PRODUCT[]" value="<?=$arResult['ITEMS']['MAIN'][0]["ID"];?>">
    <input onclick="yaCounter38469730.reachGoal('how_much_button');" class="start-form__submit btn btn_width_full" id="basket_main" type="submit" name="submit" value="Заказать уборку" style="width:auto;padding:10px 20px;">
</form>
