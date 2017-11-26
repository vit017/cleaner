<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Business");
?>



	</div>
	<section class="inner b2b_1">
		<div class="container-fluid text-center">
			<h1>MaxClean позаботится о вашем офисе,<br>а Вы управляете своим бизнесом</h1>
			<h3>Профессиональная уборка для профессионалов</h3>
			<button onclick="location.href='/order/basket/';" type="submit" class="btn btn-default">Заказать уборку</button>
		</div>
	</section>

	<section class="b2b_2">
		<div class="container text-center">
			<div class="row advs">
				<div class="col-sm-6 adv_7 adv">
					<div class="pic"></div>
					<h4 class="text-uppercase"><b>Просто и удобно</b></h4>
					<p><b>Гибкое бронирование</b>. Без минимальных обязательных контрактов.</p>
				</div>
				<div class="col-sm-6 adv_8 adv">
					<div class="pic"></div>
					<h4 class="text-uppercase"><b>Индивидуальный подход</b></h4>
					<p>Наведем <b>порядок</b> в частном офисе, Коворкинг-зоне, торговом пространстве и магазине.</b></p>
				</div>
				<div class="col-sm-6 adv_9 adv">
					<div class="pic"></div>
					<h4 class="text-uppercase"><b>Все для уборки</b></h4>
					<p>Конечно, все необходимое MaxClean <b>привезет с собой</b>.</p>
				</div>
				<div class="col-sm-6 adv_10 adv">
					<div class="pic"></div>
					<h4 class="text-uppercase"><b>Полный пакет документов</b></h4>
					<p>Оплачивая <b>по безналичному расчету</b>, вы всегда сможете получить закрывающие документы.</p>
				</div>
			</div>
		</div>
	</section>

	<section class="b2b_3">
		<div class="container">
			<div class="center-block col-md-8 col-sm-10">
				<div class="row">
					<div class="col-sm-5 text-center"><h3 class="text-uppercase">Вам нужна помощь?</h3></div>
					<div class="col-sm-2 hidden-xs"></div>
					<div class="col-sm-5">
						<a href="mailto:b2b@maxclean.help">b2b@maxclean.help</a><br>
						<a href="tel:+78000000000"><b>8 800 222-83-30</b></a>
					</div>
				</div>
			</div>
		</div>
	</section>
	<section class="b2b_4">
		<div class="container">
			<div class="center-block col-md-6 col-sm-8">
				<div class="row">
					<div class="col-sm-12"><h3 class="text-uppercase text-center">Расскажите нам о себе</h3></div>
				<?$APPLICATION->IncludeComponent("bitrix:main.feedback", "template1", Array(
					"USE_CAPTCHA" => "Y",	// Использовать защиту от автоматических сообщений (CAPTCHA) для неавторизованных пользователей
					"OK_TEXT" => "Спасибо, ваше сообщение принято.",	// Сообщение, выводимое пользователю после отправки
//					"EMAIL_TO" => "otvet@maxclean.help",	// E-mail, на который будет отправлено письмо
					"EMAIL_TO" => "ju.kazachenko@naibecar.com",	// E-mail, на который будет отправлено письмо
					"REQUIRED_FIELDS" => "",	// Обязательные поля для заполнения
					"EVENT_MESSAGE_ID" => "",	// Почтовые шаблоны для отправки письма
				),
					false
				);?>


					<!--
						<div class="col-sm-6">
							<input required type="text" name="company_name" placeholder="Название компании"><br>
							<input required type="text" name="contact_name" placeholder="Имя"><br>
							<input required type="tel" name="phone" placeholder="Телефон">
						</div>
						<div class="col-sm-6">
							<textarea maxlength="140" name="text" placeholder="Ваш вопрос или комментарий"></textarea>
						</div>
						<div class="col-sm-12 text-center"><button type="submit" class="btn btn-default">Отправить</button></div>-->

				</div>
			</div>
		</div>
	</section>
<style>
	section.order, section.user, section.faq {
		background: #fdfaf5 url(/layout/mcstyle/img/pic_b2b_bg.jpg) no-repeat 50% 0 fixed;
	}
	h1 {
		color: #3257fe !important;
		font-size: 48px !important;
		font-weight: 100;
	}
	section.b2b_4 {
		background: #f1ffff;
	}
</style>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>