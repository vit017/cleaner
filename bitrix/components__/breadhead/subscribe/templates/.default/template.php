<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 07.04.14
 * Time: 15:49
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if($arResult['OK']=='Y'){?>
	<form class="form-section">
		<h2 class="main-section__title">Спасибо!</h2>
		<div class="form-section__content">
			<p class="form-section__txt">
				Мы пришлем вам приглашение, как только откроется наш сервис.
			</p>
		</div>
	</form>
<?}else{?>
   <form class="form-section">
        <h2 class="main-section__title">Узнайте о запуске первым!</h2>
        <div class="form-section__content">
            <p class="form-section__txt">
                Оставьте свой e-mail и получите подарок - промокод на <span class="js-hour_price">500<?//=$_SESSION['HOUR_PRICE']/2?></span> рублей на вашу первую уборку!

                <?$error = false;
                if(isset($arResult['ERROR']['FORM'])){$error = true;}
                ?>
            </p>
            <label class="form-section__input input-txt input-txt_bg_grey <?=$error?'input-txt_state_error':''?>" data-placeholder="E-mail">
                <input type="email" name="EMAIL" value="<?=$arResult['NAME']?>" class="input-txt__field" type="text" placeholder="E-mail">
                <?if(isset($arResult['ERROR']['FORM'])){?>
                    <span class="input-txt__error"><?= $arResult['ERROR']['FORM']['msg']?></span>
                <?}?>

            </label>
            <input type="hidden" name='subscribe' class="btn " value="Y">
            <button class="btn btn_size_big js-subscribe" id="feedback_btn"><!--onclick="$(this).closest('form').submit()"--> Получить</button>
        </div>
    </form>
<?}?>
