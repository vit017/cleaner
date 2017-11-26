<?
$props = $arResult["ORDER_PROPS"];?>
	<div class="container">
		<div class="col-lg-10">
			<h1 class="text-uppercase">Личный кабинет
				<button type="button" class="btn btn-default hidden-xs" id="get_clean_2" onclick="location.href='/order/basket/';" >Оформить заказ</button>
			</h1>
		</div>
		<div class="col-lg-2 hidden-xs"></div>
		<div class="row">
			<div class="col-lg-10">
				<ul class="nav nav-pills nav-justified">
					<li class="active"><a href="#now_cleaning">Текущие уборки</a></li>
					<li ><a href="/user/history/">Все уборки</a></li>
					<li><a href="/user/settings/">Настройки</a></li>
					<li><a href="?logout=yes">Выйти</a></li>
				</ul>
				<div class="tab-content">
					<div id="cleaning_plan" class="content_area tab-pane fade in active">
						
						<div class="row">
							<div class="col-sm-6"><h2>
									<?if ($arResult['DAYS_BEFORE'] == 1 ) {?>
										Уборка уже завтра
									<?} elseif ($arResult['DAYS_BEFORE'] == 0 ) {?>
										Уборка уже сегодня
									<?} else {?>
										<span >До уборки <?=$arResult['DAYS_BEFORE']?> <?=bhTools::words($arResult['DAYS_BEFORE'], array('день', 'дня', 'дней'))?></span>
									<?}?></h2>
							</div>
							<div class="col-sm-6 btns">
								<span class="status"><i class="icon wait_for"></i>Ожидается</span>
								<?if ( $arResult['CAN_CANCEL'] == 'Y' ){?>
                                    <button href='#cancel_order' data-toggle="pill" type="button" class="btn btn-link"><i class="icon delete"></i>Отменить уборку</button>
                                <?}?>

							</div>
						</div>
						<div class="block">
							<h5><i class="icon date_time"></i>Дата и время</h5>
							<p class="order-item__param"><?=$arResult['WEEK_DAY']?>, <?=$props['DATE']['VALUE_FORMATED']?></p>
							<p class="order-item__param"><?=$props['TIME']['VALUE_FORMATED']?> (<?=$props['DURATION']['VALUE_FORMATED']?>)</p>
						</div>
						<div class="block">
							<h5><i class="icon obj_data"></i>Параметры квартиры</h5>
							<?foreach ($arResult['BASKET']['MAIN'] as $service){
								if ( $service['QUANTITY'] > 0 ){?>
									<?='<p>' . $service['NAME_FORMATED'].'м&#178;</p>'?>
								<?}?>
							<?}

                            $additional_line = bhTools::makeAddLineLi($arResult['BASKET']['ADDITIONAL']);
                            if ( strlen($additional_line) > 0 ){?>
                                <p><b>Дополнительно:</b></p>
                                <ul class="list-unstyled">
<!--                                        <p class="order-item__param"><span class="order-item__param-name">Дополнительно:</span>-->
                                            <?=$additional_line?>
<!--                                        </p>-->
                                    <!--<li>Помыть внутри холодильника</li>
                                    <li>Помыть внутри духовки</li>
                                    <li>Помыть внутри микроволновки</li>
                                    <li>Помыть внутри кухонных шкафов</li>
                                    <li>Помыть окна (6)</li>-->
                                </ul>
                            <?}?>
						</div>
						<div class="block">
							<h5><i class="icon locate"></i>Контактные данные</h5>
							<ul class="list-unstyled">
								<li>Город: <b><?=$props['PERSONAL_CITY']['VALUE_FORMATED']?></b></li>
								<li>Адрес: <b><?=$props['PERSONAL_STREET']['VALUE_FORMATED']?></b></li>
								<li>Телефон: <b><?=$props['PERSONAL_PHONE']['VALUE_FORMATED']?></b></li>
								<li>Ваше имя: <b><?=$props['NAME']?></b></li>
								<!--<li>E-mail: <b><?=$props['MAIL']?></b></li>-->
							</ul>
						</div>
						<div class="block">

							<h5><i class="icon your_data"></i>Ваш клинер</h5>

							<? if ( isset($arResult['CLEANER']) ){?>
								<div class="order-item">

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
									<p class="order-item__param order-item__param_cleaner clearfix">
										<p><b>Клинер пока не назначен.</b></p>
									</p>
								</div>
							<?}?>

						</div>
						<div class="block">
							<h5><i class="icon rouble">&#8381;</i>Стоимость</h5>
							<!--<p><b>3400 &#8381; за 4,5 часа</b> (оплата наличными)</p>
							<p><b>Итого: 3400 &#8381;</b> (оплата после выполнения заказа)</p>-->


								<p class="order-item__param"><b></b> <?=$arResult['SUMMARY']['BASKET_PRICE_FORMATED']?></b> <span class="rouble">Р</span> за <?=$props['DURATION']['VALUE_FORMATED']?><?if ( $arResult['SUMMARY']['ORDER_PRICE'] == $arResult['SUMMARY']['BASKET_PRICE'] ){?> <span class="grey" style="text-transform: lowercase;"><?=$arResult['SUMMARY']['PAYMENT']?><?}?></p>
								<?if ( $arResult['SUMMARY']['DISCOUNT_PRICE'] > 0 ){?>
									<p class="order-item__param"><b>Скидка:</b> <?=$arResult['SUMMARY']['DISCOUNT_PRICE_FORMATED']?> <span class="rouble">Р</span></p>
								<?}?>
								<?if ( $arResult['SUMMARY']['SUM_PAID'] > 0 ){?>
									<p class="order-item__param"><b>Уже оплачено:</b> <?=$arResult['SUMMARY']['SUM_PAID_FORMATED']?> <span class="rouble">Р</span></p>
								<?}?>
								<?//if ( $arResult['SUMMARY']['ORDER_PRICE'] <> $arResult['SUMMARY']['BASKET_PRICE'] ){?>
								<p class="order-item__param"><b>Итого:</b> <?=$arResult['SUMMARY']['NEED_TO_PAY_FORMATED']?> <span class="rouble">Р</span>
									<?if($arResult['SUMMARY']['NEED_TO_PAY'] > 0){?>
										<span class="grey" style="text-transform: lowercase;"> (<?=$arResult['PAY_SYSTEM']['ID']==2 ?'списывается':'оплата'?> после выполнения заказа)</span>
									<?}?>
								</p>
								<?//}?>
								<?if ( $arResult['PAYED'] == 'Y' ){?>
									<p class="order-item__param"><b>Оплачено</b></p>
								<?}?>

							<?if ( $arResult['NEED_CARD'] &&  $arResult['PAYED'] != 'Y' ){?>
								<? include($arResult['PAY_SYSTEM']['PATH_TO_ACTION']) ?>
							<?}?>
						</div>
					</div>
                    <div id="cancel_order" class="content_area tab-pane fade">
                        <button type="button" class="btn btn-link" data-toggle="pill" href="#cleaning_plan"><i class="icon back"></i> Детали уборки</button>
                        <div class="row">
                            <?
                            $rawDate = strtotime($arResult['DATE_STATUS']);
                            $monthes = array(
                                '01' => 'Января',
                                '02' => 'Февраля',
                                '03' => 'Марта',
                                '04' => 'Апреля',
                                '05' => 'Мая',
                                '06' => 'Июня',
                                '07' => 'Июля',
                                '08' => 'Августа',
                                '09' => 'Сентября',
                                '10' => 'Октября',
                                '11' => 'Ноября',
                                '12' => 'Декабря'
                            );
                            $d = array();
                            $d['day'] = date('d', $rawDate);
                            $d['month'] = $monthes[date('m', $rawDate)];
                            $d['year'] = date('Y', $rawDate);
                            $date = implode(' ', $d);
                            ?>
                            <div class="col-sm-12"><h2>Отменить уборку <?=$date;?></h2></div>
                            <form role="form" method="post" action="/user/history/?ID=<?=$arResult['ID'];?>&CANCEL=Y">
                                <input type="hidden" name="CANCEL" value="Y">
                                <!--                            <input type="hidden" name="sessid" id="sessid" value="--><?//=session_id();?><!--">-->
                                <?=bitrix_sessid_post()?>
                                <div class="col-sm-6">
                                    <p>Вы уверены что хотите отменить заказ # <?=$arResult['ID'];?>?<br /> Отмена заказа необратима.</p>
                                </div>
                                <div class="col-sm-12">
                                    <textarea autofocus="autofocus" placeholder="Укажите, пожалуйста, причину отмены заказа" name="REASON_CANCELED" maxlength="140"></textarea>
                                </div>
                                <div class="col-sm-12">
                                    <button type="submit" name='action' value="Отменить заказ" class="btn btn-default">Отменить заказ</button>
                                    <input type="reset" value="Не сейчас" onclick="location.href='/user/history';">
                                </div>
                            </form>
                        </div>
                </div>
			</div>
			<div class="col-lg-2 hidden-xs"></div>
		</div>
	</div>



<div class="order-detail" style="display: none">

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
