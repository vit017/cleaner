// Общие самописные скрипты

//инициируем маски ввода данных
  $('input[type="tel"]').inputmask("+7 (999) 999-99-99");  //для телефонов

// Вызов селектов
$('#town').selectpicker({
  width: 'auto',
});
$('#flat_S').selectpicker({
  width: '300px',
  title: 'Укажите площадь помещения'
});
$('#square').selectpicker({
  width: '225px',
});
$('#city').selectpicker({
  width: '195px',
});

// Вызов слайдера
$('.slider-for').slick({
  slidesToShow: 1,
  //centerPadding: '80px',
  slidesToScroll: 1,
  arrows: true,
  variableWidth: true,
  adaptiveHeight: true,
  centerMode: true,
  prevArrow: '<button type="button" class="slick-prev"><img src="img/arrow_left.png"></button>',
  nextArrow: '<button type="button" class="slick-next"><img src="img/arrow_right.png"></button>',
  asNavFor: '.slider-nav',
  responsive: [
    {
      breakpoint: 767,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
        infinite: true,
        dots: false,
        arrows: false,
        centerMode: false,
        centerPadding: '0px',
      }
    }
  ]  
});
$('.slider-nav').slick({
  slidesToShow: 5,
  slidesToScroll: 1,
  asNavFor: '.slider-for',
  dots: false,
  centerMode: false,
  focusOnSelect: true,
});
$('.single-item').slick({
	autoplay: false,
	fade: true,
	lazyLoad: 'ondemand',
	centerMode: true,
	centerPadding: '0px',
	prevArrow: '<button type="button" class="slick-prev"><img src="img/arrow_left_w.png"></button>',
	nextArrow: '<button type="button" class="slick-next"><img src="img/arrow_right_w.png"></button>',
});

// Заменяем полноценное меню на меню-полоску и обратно при скроллинге
$('#floating').hide();
jQuery(document).scroll(function() { 
  if (jQuery(document).scrollTop() > 50 ) {
    $('#floating').show();
    $('#fixed').slideUp();
  } else { 
    $('#floating').hide();
    $('#fixed').show();
  }});

// Вызываем тултипы
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip(); 
});

// Подгоняем ширину синих оверлеев и позиционируем навигационные стрелки в слайдерах
$(document).ready(function(){
	var widthSection1 = $(".slider-holder").width();
	var widthContainer1 = $(".slider-holder .container").width();
	var widthMargin1 = (widthSection1 - widthContainer1)/2-15;

	$('.slider_cover').css("width",widthMargin1+'px');
	$('.slick-prev').css("left",widthMargin1+'px');
	$('.slick-next').css("right",widthMargin1+'px');

	$(window).resize(function(){
	    var widthSection = $(".slider-holder").width();
	    var widthContainer = $(".slider-holder .container").width();
	    var widthMargin = (widthSection - widthContainer)/2-15;

	    $('.slider_cover').css("width",widthMargin+'px');
	    $('.slick-prev').css("left",widthMargin+'px');
		$('.slick-next').css("right",widthMargin+'px');

		// console.log( widthSection );
		// console.log( widthContainer );
		// console.log( widthMargin );
	});
});

// подгоняем высоту блока-отбивки (костыль) в оформлении заказа
//$('.nav-tabs a').on('shown.bs.tab', function(){ // изначальный вариант
$(document).on('mouseover', '#order_check.active', function(){ // временное решение
// $('#sms form').submit(function(){
	var heightTerm1 = $("#term_1").outerHeight();
	var heightTerm2 = $("#term_2").outerHeight();
	var heightTerm3 = (heightTerm1 + heightTerm2)+50;

	$('#order_check .positioner').css("height",heightTerm3+'px');

	$(window).resize(function(){
		var heightTerm11 = $("#term_1").outerHeight();
		var heightTerm12 = $("#term_2").outerHeight();
		var heightTerm13 = (heightTerm11 + heightTerm12)+50;

		$('#order_check .positioner').css("height",heightTerm13+'px');
	});
});

// переключаем активную вкладку при редактровании заказа
$(document).on('click','a.edit',function(){
	$('.nav-tabs li').removeClass('active');
	$("html,body").animate({"scrollTop":0},"fast");
	var linkValue = $(this).attr('data-link');
	// console.log( linkValue );
	$('.nav-tabs li.'+ linkValue ).addClass('active');
});

// меняем цыфры на галочки в пунктах заказа при навигации по табам
$('.nav-tabs a').on('hidden.bs.tab', function(){
	$(this).addClass('v');
});

// активизируем нужную вкладку при навигации по кнопке submit
$(document).on('click','button[data-toggle="tab"]',function(){
	$("html,body").animate({"scrollTop":0},"fast");
	$('.nav-tabs li').removeClass('active');
	var linkValue = $(this).attr('data-link');
	$('.nav-tabs li.'+ linkValue ).addClass('active');

	// меняем цыфры на галочки в пунктах заказа при навигации по кнопке submit
 	var tabNum = $('.nav-tabs li.'+ linkValue ).index(); // получаем прядковый номер предыдущей вкладки
	var prevTab = $('.nav-tabs li.'+ linkValue ).parent().find('a').eq(tabNum - 1).attr('data-link'); // получаем адрес ссылки предыдущей вкладки
	$('.nav-tabs li.' + prevTab + ' a').addClass('v');
});

// запрещаем навигацию по табам вообще
// $('.nav-tabs a').bind('click',function(){return false;});

// либо разрешаем навигацию только по пройденным этапам
$('.nav-tabs a').bind('click',function(){
	if($(this).hasClass('v')) {
		$(this).tab('show');	
	} else {return false;}
});

// Раскрываем блок "НЕ ДЕЛАЕМ"
// $(document).on('click', '.not_do h4', function(){
//     $('.not_do').toggleClass('uncut'); 
// });

// включаем и настраиваем календарь
if($('#order_datetimepicker').length){
	$(function () {
        var maxdaysInMonth = new Date(new Date().getFullYear(), new Date().getMonth()+1, 0).getDate();
        var period = 0;
        var mindate = moment().add(period, 'days');
        var maxdate = moment().add(maxdaysInMonth + period, 'days');
		$('#order_datetimepicker').datetimepicker({
			inline: true,
			sideBySide: false,
			format: 'L',
			locale: 'ru',
			minDate: mindate,
			maxDate: maxdate
			//daysOfWeekDisabled: [0, 6],
		});
	});
    var disabletoday = false;
    $("#order_datetimepicker").on("dp.change",function (e) {
        var date = e.date.format("YYYY-MM-DD");
        $("#datasend").val(date);
        var dateAr = date.split('-');
        var today = new Date();
        var todayStr = new Date(today.getFullYear(), today.getMonth(), today.getDate()).toDateString();
        var dateStr = new Date(dateAr[0], dateAr[1] - 1, dateAr[2]).toDateString();
        if (todayStr == dateStr) {
            var time = parseInt(today.getHours() + '' + today.getMinutes() / 0.6) + 400;
            var nondisabled = false;
            $('[name=ORDER_PROP_TIME]').each(function(){
                var val = parseInt($(this).val().replace(':',''));
                if (time > val) {
                    $(this).attr('disabled', 'disabled');
                    $(this).addClass('todaydisabled');
                    if ($(this).is($('[name=ORDER_PROP_TIME]:checked'))) {
                        $(this).prop('checked', false);
                    }
                }
                if (typeof $(this).attr('disabled') == 'undefined') {
                    nondisabled = true;
                }
            });
            if (!nondisabled) {
                disabletoday = true;
                setTimeout(function(){
                    var d = today.getDate() < 9 ?  '0' + (today.getDate() + 1) : today.getDate() + 1;
                    var m = today.getMonth() < 10 ? '0' + (today.getMonth() + 1) : today.getMonth() + 1;
                    var select = '[data-day="' + d + '.' + m + '.' + today.getFullYear() + '"]';
                    $(select).click();
                }, 10);
            }
            //
        } else {
            $('.todaydisabled').each(function(){
                $(this).removeAttr('disabled').removeClass('todaydisabled');
                $('[for=' + $(this).attr('id')).attr('data-original-title', '').attr('title', '');
            });
        }

        $('[name=ORDER_PROP_TIME]:disabled').each(function(){
            var title = $(this).hasClass('todaydisabled') ? 'Клинеру необходимо время, чтобы доехать до вас.' : 'Время не может быть выбрано из-за окончания работ Клинера до 23:00.';
            $('[for=' + $(this).attr('id')).attr('data-original-title', title);

        });
        $('.radio_label').tooltip();


        if (disabletoday) {
            var d = today.getDate() < 9 ?  '0' + (today.getDate()) : today.getDate();
            var m = today.getMonth() < 10 ? '0' + (today.getMonth() + 1) : today.getMonth() + 1;
            var select = '[data-day="' + d + '.' + m + '.' + today.getFullYear() + '"]';
            $(select).removeClass('today').addClass('disabled');
        }

    });
}
// скрипт превращения input[type=number] в кнопки [+] и [-]
//$(document).ready(function() {

//});

// при каждой смене положения чекбокса устанавливаем значение по умолчанию "2"
$(document).on('change', '#clean_window', function(){
	$('.number input').val('1');
	// и меняем текст в лейбле
	var text = $('label[for=clean_window] p').text();
    $('label[for=clean_window] p').text(
        text == "Количество окон" ? "Окна" : "Количество окон");
});

// при загрузке страницы чекбокс "Не из компании" выбран
if($('#company_n').length){
    $('#company_n')[0].checked=true;
}
// манипуляции с инпутами если клиент из компании
$(document).on('change','input[name=company]',function(){
	// console.log(this);
	var value = $(this).val();
	// console.log(value);
	if(value=='y'){
        $('input[name=company_name]').removeAttr('disabled');
        $('input[name=company_inn]').removeAttr('disabled');
    }
    else { 
    	$('input[name=company_name]').attr('disabled','disabled');
        $('input[name=company_inn]').attr('disabled','disabled');
        $('input[name=company_name]').attr('value','');
        $('input[name=company_inn]').attr('value','');
	}
});

// при загрузке страницы чекбокс "Согласен с условиями продажи" выбран
if($('#agreement').length){
    $('#agreement')[0].checked=true;
// разрешаем двигаться дальше только если клиент согласен
	$(document).on('change','#agreement',function(){
		if($(this).is(':checked')){
			// console.log('#agreement:checked');
			$('#order_check button[type=submit]').removeAttr('disabled');
	    }
	    else { 
	    	$('#order_check button[type=submit]').attr('disabled','disabled');
		}
	});
}




//------старые скрипты
var basketSendFormAjax = (function () {
	/* Модуль перезагрузки корзины при изменении состава заказа*/
	var me = {};
	var xhr;

	me.init = function () {
		$('body').on('click.sendBasketForm', '.js-send-basketForm', function(event){
			me.abort(); // Сбрасываем обработку предыдущего ответа
			var form = $(this).closest('form');
			me.sendForm(form);
		});
		$('body').on('change.sendBasketForm', '.js-update-basketForm', function(event){
			me.abort(); // Сбрасываем обработку предыдущего ответа
			var form = $(this).closest('form');
			me.sendForm(form);
		});
		$('body').on('submit', '.js-basket-form', function(event) {
			if (xhr && xhr.readyState == 1) {
				event.preventDefault();
			}
		});
		$('body').on('click', '.js-plus', function(){
			var input = $(this).siblings('.js-input-qnt');
			var val = parseInt(input.val());
			var step = parseInt(input.data('step'));
			val = val+step;
			input.val(val);
			$(this).siblings('.js-quantity').text(val);
			me.abort(); // Сбрасываем обработку предыдущего ответа
			var form = $(this).closest('form');
			me.sendForm(form);
		})
		$('body').on('click', '.js-minus', function(){
			var input = $(this).siblings('.js-input-qnt');
			var val = parseInt(input.val());

			if (val == 0)
				return false;
			var step = parseInt(input.data('step'));
			val = val-step;
			input.val(val);
			$(this).siblings('.js-quantity').text(val);
			if (val == 0) {
				$(this).closest('.additional-control__item').find('input:checkbox').click();
			}
			me.abort(); // Сбрасываем обработку предыдущего ответа
			var form = $(this).closest('form');
			me.sendForm(form);
		})
		initCleaners();
	};

	me.sendForm = function(form) {
		form.find('input[name=AJAX_CALL]').val('Y');
		var $basket = $('#js-basket');
		xhr = $.ajax({
			url: '/ajax/basket.php',
			type: "POST",
			data: form.serialize(),
			success: function(data){
				$basket.html(data);
				// count inputs init
                if ($basket.find('.count-input').size()) $basket.find('.count-input').countInput();
				// order time select
				if ($basket.find('.js-custom-select').size()) $basket.find('.js-custom-select').selectize({
					onChange: function(value) {
						if (!value.length) return;
						me.abort();
						var form = $('.js-basket-form');
						me.sendForm(form);
					}
				});
				initCleaners();
				form.find('input[name=AJAX_CALL]').val('');
			}
		});
	};

	me.abort = function() {
		if(xhr){
			xhr.abort();
		}
	};

	return me;

}());


$(function () {
	$('body').on('change','.additional-control__item input:checkbox' , function () {
		if ($(this).is(':checked')) {
			$(this).closest('.additional-control__item').addClass('checked');
		} else {
			$(this).closest('.additional-control__item').removeClass('checked');
		}

	})

});
function initCleaners(){
    return false;
	if($('.js-select_cleaner').length) {
		var CLEANERS = $('.js-cleaner-option').data('val');
		$('.js-select_cleaner').selectize({
			maxItems: 1,
			options: CLEANERS,
			labelField: 'name',
			valueField: 'id',

			//allowEmptyOption: true,
			persist: false,
			sortField: 'sort',
			render: {
				item: function (item, escape) {
					if(escape(item.img).length){
						return "<div><img src='"+ escape(item.img) +"' class='cleaner-face' />" + escape(item.name) + "</div>";
					}else{
						return "<div>" + escape(item.name) + "</div>";
					}
				},
				option: function (item, escape) {
					if(escape(item.img).length){
						return "<div><img src='"+ escape(item.img) +"' class='cleaner-face' />" + escape(item.name) + "</div>";
					}else{
						return "<div>" + escape(item.name) + "</div>";
					}
				}

			}
		});
		var wishCleaner = $('input[name=wishCleaner]').val();
		$('.js-select_cleaner')[0].selectize.setValue(wishCleaner)
	}
}

$(document).ready(function() {

	basketSendFormAjax.init();
	sendOrder = false;
	$('body').on('click.smsResend', '.js-sms-resend', function (e) {
		e.preventDefault();
		var $confirmBlock = $(this).parents('.sms-confirm');
		$confirmBlock.find('.sms-confirm__resend-input').prop('checked', true);
		$(this).closest('form').submit();
	});


	$('body').on('click', '.js-set_qnt', function(){
		var name = $(this).data('name');
		if ( $('.js-set_qnt_field.'+name).length ){
			$('.js-set_qnt_field.'+name).show();
		}
	})

	var $validateForm = $('.js-form-validate');
    try {
        $validateForm.parsley({
            'errorsWrapper': '<span class="input-error"></span>',
            'errorTemplate': '<span class="input-error__item"></span>',
            classHandler: function (el) {
                if ($(el.$element[0]).hasClass('input-txt__field')) {
                    return $(el.$element[0]).parents('.input-txt');
                }
            }
        });
    } catch (e) {}

	// ссылки "Поделиться" в личном кабинете для получения бонуса
	if ($('#refLinkShare').length) {
		var userID = $('input[name=USER_ID]').val();
		var shareLink = 'http://' + window.location.hostname + '/?repost&utm_user_id=' + userID;
		//console.log(shareLink, window.location.hostname);
		var YaShareInstance = new Ya.share({
			element: 'refLinkShare',
			elementStyle: {
				'type': 'none',
				'quickServices': ['vkontakte', 'facebook']
			},
			link: 'http://' + window.location.hostname + '/?repost&utm_user_id=' + userID,
			title: 'GetTidy'
		});
		//YaShareInstance.updateShareLink(shareLink, 'GetTidy');
		//console.log(YaShareInstance);
	}

	$('.js-promocode').on('click.promocode', '.btn', function (e) {
		if ($('.js-promocode-input').val() == '') {
			e.preventDefault();
		}
	});

	$('.js-phone-format').inputmask('+7 (999) 999-99-99', {
		'placeholder': '+7 (___) ___-__-__',
		'onincomplete': function () {
			var name = $(this).attr('name');
			var $input = $(this).parent('.input-txt');
			$input.addClass('input-txt_state_error');
			$input.find('.input-error__item').remove();
			$input.find('.input-error').append('<span class="input-error__item parsley-custom-error-message">Поле заполнено неверно</span>')
		},
		'oncomplete': function () {
			var $input = $(this).parent('.input-txt');
			$input.removeClass('input-txt_state_error');
			$input.find('.input-error__item').remove();
		}
	});

	$('.order-form__next, .js-submit').on('click', function (e) {
		var $phoneFiled = $(this).closest('form').find('.js-phone-format').parent('.input-txt');
		if ($phoneFiled.hasClass('input-txt_state_error')) {
			e.preventDefault();
			return false;
		}

		if(sendOrder){
			e.preventDefault();
			return false;
		}

		if($validateForm.parsley().isValid()){
			sendOrder = true;
		}

	});

	$('.js-pass').on('keyup', function () {
		var value = $(this).val();
		$('.js-pass-confirm').val(value);
	});

	send = false;
	$('body').on('change', '.js-city-select', function(){
		if (!send) {
			send = true;
			$.ajax({
				url: '/ajax/city.php',
				type: "POST",
				data: {change_city:'Y', CITY_ID:$(this).val()},
				success: function (data) {

					send = false;
					$('body').append(data);
				}
			})
		}
	});
	$('body').on('click', '.js-city-change', function () {
		var form = $(this).closest('form');
		var obCity = $(this);
		form.find('input[name=CITY_ID]').val($(this).data('id'));
		if (!send) {
			send = true;
			$.ajax({
				url: '/ajax/city.php',
				type: "POST",
				data: form.serialize(),
				success: function (data) {
					//console.log(data);
					$('body').append(data);
					$('.js-city').text(city);
					$('.city-dropdown__list-item').removeClass('city-dropdown__list-item_active');
					$('.city-dropdown__list-item').removeClass('js-city-change');
					$('.city-dropdown__list-item').each(function(){
						if($(this).data('id')==obCity.data('id')){
							$(this).addClass('city-dropdown__list-item_active');
						}else{
							$(this).addClass('js-city-change');
						}
					})
					$('.js-phone').text(phone);
					$('.js-phone').attr('href', clear_phone);
					$('.js-address').text(address);
					$('.js-hour_price').text(hour_price);
					send = false;
				}
			});
		}
	});

    var sendingComment = false;
    $('.form_comment_clean').submit(function(){
        if (sendingComment) return false;
        var form = $(this);
        var errfield = $(this).parent().find('.errfield');
        var success = $(this).parent().find('.success');
        errfield.hide();

        var data = {};
        $(this).find('input, textarea').each(function(){
            data[$(this).attr('name')] = $(this).val();
        });
        var errors = [];
        if (!data['clean-rating'] || data['clean-rating'] == 0) {
            errors.push('Поставьте, пожалуйста, уборке Вашу оценку.');
        }
        if (errors.length) {
            errfield.html(errors.join("<br />\n"));
            errfield.show();
            return false;
        }
        var sendingComment = true;
        $.get('/ajax/addCommentToOrder.php', data)
            .done(function(){
                success.show();
                form.hide();
            })
            .fail(function(){
                errfield.html('Извините, на сервере произошла ошибка. Повторите, пожалуйста, позже.');
                errfield.show();
            })
            .always(function(){
                sendingComment = false;
            });
        return false;
    });

});
$('div.open>.dropdown-menu').click(
	function settown() {
		//alert($("button>span.filter-option").text());
		$.ajax({
			type : 'GET',
			url : '/ajax/setTown.php?town='+$("#town").val(),
		});
		/*$.ajax({
			type : 'GET',
			url : '/ajax/setTown.php?town='+$("#town").val(),
		});*/
	}
);

// включаем и настраиваем StarRating
if($('.clean-rating').length){
    $(".clean-rating").rating({
        starCaptions: {1: 'Плохо', 2: 'Сносно', 3: 'Нормально', 4: 'Хорошо', 5: 'Отлично'},
        clearCaption: 'Не оценено',
        showClear: false,
        size: 'xs',
        stars: 5,
        step: 1,
        starCaptionClasses: function(val) {
            if (val == 0) {
                return 'text-info';
            }
            else {
                return 'text-info';
            }
        }
        // emptyStar: '<i class="icon star-o"></i>',
        // filledStar: '<i class="icon star"></i>',
    });
}

// включаем и настраиваем таймер на главной
$(document).ready(function() {

    // генерируем случайное время в заданном диапазоне
    var startTime = (Math.round(Math.random()*((50 - 28)) + 28)*100);
    console.log(startTime);

    var clock;
    clock = $('.clock').FlipClock({
        clockFace: 'MinuteCounter', //вид счетчика (с количеством дней)
        language:'ru-ru', //Локаль языка
    });
    //clock.setTime(2800); //Устанавливаем нужное время в секундах
    clock.setTime(startTime); //Устанавливаем рандомное время в секундах
    clock.setCountdown(false); //Устанавливаем отсчет вперед
    clock.start(); //Запускаем отсчет
    setTimeout(function() {
        clock.stop();
    }, 1200000); // Останавливаем отсчет через ... (в милисекундах)
});