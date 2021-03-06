<div class="order-detail">
	<?$props = $arResult["ORDER_PROPS"];?>
	<div class="order-detail__header clearfix">
		<h2 class="order-detail__title">
			<?if ($arResult['DAYS_BEFORE'] == 1 ) {?>
				Уборка уже завтра
			<?} elseif ($arResult['DAYS_BEFORE'] == 0 ) {?>
				Уборка уже сегодня
			<?} else {?>
				<span >До уборки <?=$arResult['DAYS_BEFORE']?> <?=bhTools::words($arResult['DAYS_BEFORE'], array('день', 'дня', 'дней'))?></span>
			<?}?>
		</h2>
		<div class="order-detail__header-content">
			<?if ( $arResult['CAN_CANCEL'] == 'Y' ){?>
				<div class="cabinet__cancel" style="padding:0">
					<a href="<?=$arResult['URL_TO_CANCEL']?>" class="cancel-link">Отменить уборку</a>
				</div>
			<?}?>
		</div>
	</div>
	<div class="order-detail__content">
		<div class="order-item">
			<h4 class="order-item__title order-item__title_date">Дата и время</h4>
			<p class="order-item__param"><?=$arResult['WEEK_DAY']?>, <?=$props['DATE']['VALUE_FORMATED']?></p>
			<p class="order-item__param"><?=$props['TIME']['VALUE_FORMATED']?> (<?=$props['DURATION']['VALUE_FORMATED']?>)</p>
		</div>
		<div class="order-item">
			<h4 class="order-item__title order-item__title_params">Параметры квартиры</h4>
			<p class="order-item__param">
				<?foreach ($arResult['BASKET']['MAIN'] as $service){
					if ( $service['QUANTITY'] > 0 ){?>
						<?=$service['NAME_FORMATED'].'м&#178;'?>
					<?}?>
				<?}?>
			</p>
			<?$additional_line = bhTools::makeAddLine($arResult['BASKET']['ADDITIONAL']);
			if ( strlen($additional_line) > 0 ){?>
				<p class="order-item__param"><span class="order-item__param-name">Дополнительно:</span>
					<?=$additional_line?>
				</p>
			<?}?>
		</div>
		<div class="order-item">
			<h4 class="order-item__title order-item__title_contacts">Контактные данные</h4>
			<p class="order-item__param"></p>
			<p class="order-item__param"><span class="order-item__param-name">Город: </span><?=$props['PERSONAL_CITY']['VALUE_FORMATED']?></p>
			<p class="order-item__param"><span class="order-item__param-name">Адрес: </span><?=$props['PERSONAL_STREET']['VALUE_FORMATED']?></p>
			<p class="order-item__param"><span class="order-item__param-name">Телефон: </span><?=$props['PERSONAL_PHONE']['VALUE_FORMATED']?></p>
			<p class="order-item__param"><span class="order-item__param-name">Ваше имя: </span><?=$props['NAME']?></p>
		</div>
		<? if ( isset($arResult['CLEANER']) ){?>
			<div class="order-item">
				<h4 class="order-item__title order-item__title_cleaner">Ваш клинер</h4>
				<p class="order-item__param order-item__param_cleaner clearfix">
					<span class="order-item__param-pic">
						<img src="<?=$arResult['CLEANER']['PERSONAL_PHOTO']?>"/>
					</span>
					<a href="/cleaner/?ID=<?=$arResult['CLEANER']['ID']?>&BACK_URL=/user/history/?ID=<?=$arResult['ID']?>">
						<?=$arResult['CLEANER']['NAME']?>
					</a>
				</p>
			</div>
		<?} else {?>
			<div class="order-item">
				<h4 class="order-item__title order-item__title_cleaner">Ваш клинер</h4>
				<p class="order-item__param order-item__param_cleaner clearfix">
					  <span class="order-item__param-pic">
						<img src="/layout/assets/images/content/cleaner-unknown.png"/>
					  </span>
                    Клинер пока не назначен.
				</p>
			</div>
		<?}?>

		<div class="order-item">
			<h4 class="order-item__title order-item__title_price">Стоимость</h4>
			<p class="order-item__param"> <?=$arResult['SUMMARY']['BASKET_PRICE_FORMATED']?> <span class="rouble">Р</span> за <?=$props['DURATION']['VALUE_FORMATED']?><?if ( $arResult['SUMMARY']['ORDER_PRICE'] == $arResult['SUMMARY']['BASKET_PRICE'] ){?> <span class="grey" style="text-transform: lowercase;"><?=$arResult['SUMMARY']['PAYMENT']?><?}?></p>
			<?if ( $arResult['SUMMARY']['DISCOUNT_PRICE'] > 0 ){?>
				<p class="order-item__param">Скидка: <?=$arResult['SUMMARY']['DISCOUNT_PRICE_FORMATED']?> <span class="rouble">Р</span></p>
			<?}?>
			<?if ( $arResult['SUMMARY']['SUM_PAID'] > 0 ){?>
				<p class="order-item__param">Уже оплачено: <?=$arResult['SUMMARY']['SUM_PAID_FORMATED']?> <span class="rouble">Р</span></p>
			<?}?>
			<?//if ( $arResult['SUMMARY']['ORDER_PRICE'] <> $arResult['SUMMARY']['BASKET_PRICE'] ){?>
				<p class="order-item__param">Итого: <?=$arResult['SUMMARY']['NEED_TO_PAY_FORMATED']?> <span class="rouble">Р</span>
					<?if($arResult['SUMMARY']['NEED_TO_PAY'] > 0){?>
						<span class="grey" style="text-transform: lowercase;"> (<?=$arResult['PAY_SYSTEM']['ID']==2 ?'списывается':'оплата'?> после выполнения заказа)</span>
					<?}?>
				</p>
			<?//}?>
			<?if ( $arResult['PAYED'] == 'Y' ){?>
				<p class="order-item__param">Оплачено</p>
			<?}?>
		</div>
		<?if ( $arResult['NEED_CARD'] &&  $arResult['PAYED'] != 'Y' ){?>
			<? include($arResult['PAY_SYSTEM']['PATH_TO_ACTION']) ?>
		<?}?>
	</div>
</div>
