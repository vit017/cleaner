
<div class="start-section" style="padding-top: 10px;">

    <h2 class="start-section__title">Свежий взгляд на уборку</h2>
    <h3 class="start-section__text">Надежно, доступно и с вниманием к деталям</h3>
    <h2 class="mnogoru_title">АКЦИЯ!</h2>
    <h3 class="mnogoru_text">Скидка 20% на все заказы по промокоду Mnogoru</h3>

    <form class="start-form" name="BASKET_CATALOG" method="POST">
        <?if(!empty($arResult['HIDDEN'])){?>
            <?foreach($arResult['HIDDEN'] as $hidden){?>
                <input type="hidden" name="<?=$hidden['NAME']?>" value="<?=$hidden['VALUE']?>">
            <?}?>
        <?}?>

        <label class="start-form__control" >
            <select name="PRODUCT[]" class=" select js-custom-select select_flat">
                <option value="">Укажите площадь</option>
                <?foreach($arResult['ITEMS']['MAIN'] as $item){
                    if($item['PROPERTIES']['SERVICE']['VALUE']>0)
                        continue?>
                    <option value="<?=$item['ID']?>" <?=$item['QUANTITY']>0 && $item['BASKET_ID'] > 0?'selected':''?>><?=$item['NAME_FORMATED']?>м&#178;</option>
                <?}?>
            </select>
            <span class="input-error" style="margin: 0 0 30px 0;">
                <span class="input-error__item">
                   <?=$arResult['ERROR_MESSAGE']?>
                </span>
            </span>
        </label>
        <input onclick="yaCounter38469730.reachGoal('how_much_button');" class="start-form__submit btn btn_width_full" id="basket_main" type="submit" name="submit" value="<?=$arParams['SUBMIT_TITLE']?>">
    </form>
</div>




<style>
    body.main-bg{
        background-image: url("/layout/assets/images/header_bg2-blur-3.jpg");
    }
</style>