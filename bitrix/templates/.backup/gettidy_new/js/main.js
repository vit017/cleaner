// Общие самописные скрипты

//инициируем маски ввода данных
  $('input[type="tel"]').inputmask("8 999 999-99-99");  //для телефонов

// Вызов селектов
$('#town').selectpicker({
  width: 'auto',
});
$('#flat_S').selectpicker({
  width: '300px',
  title: 'Укажите площадь квартиры'
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
  prevArrow: '<button type="button" class="slick-prev"><img src="/bitrix/templates/gettidy_new/img/arrow_left.png"></button>',
  nextArrow: '<button type="button" class="slick-next"><img src="/bitrix/templates/gettidy_new/img/arrow_right.png"></button>',
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
var textSlider =
.slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: false,
    variableWidth: false,
    adaptiveHeight: true,
    centerMode: true,
    prevArrow: '<button type="button" class="slick-prev"><img src="/bitrix/templates/gettidy_new/img/arrow_left.png"></button>',
    nextArrow: '<button type="button" class="slick-next"><img src="/bitrix/templates/gettidy_new/img/arrow_right.png"></button>'
});
$('.slider-for').on('swipe', function(event, slick, direction){
    console.log(slick.currentSlide);
    // left
});
$('.single-item').slick({
	autoplay: false,
	fade: true,
	lazyLoad: 'ondemand',
	centerMode: true,
	centerPadding: '0px',
	prevArrow: '<button type="button" class="slick-prev"><img src="/bitrix/templates/gettidy_new/img/arrow_left_w.png"></button>',
	nextArrow: '<button type="button" class="slick-next"><img src="/bitrix/templates/gettidy_new/img/arrow_right_w.png"></button>',
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
$('.nav-tabs a').on('shown.bs.tab', function(){
    // alert('The new tab is about to be shown.');

    var heightTerm1 = $("#term_1").outerHeight();
	var heightTerm2 = $("#term_2").outerHeight();
	var heightTerm3 = (heightTerm1 + heightTerm2)+50;

	$('#order_check .positioner').css("height",heightTerm3+'px');

	$(window).resize(function(){
		var heightTerm11 = $("#term_1").outerHeight();
		var heightTerm12 = $("#term_2").outerHeight();
		var heightTerm13 = (heightTerm11 + heightTerm12)+50;

		$('#order_check .positioner').css("height",heightTerm13+'px');

		// console.log( heightTerm11 );
		// console.log( heightTerm12 );
		// console.log( heightTerm13 );
	});
});

// Раскрываем блок "НЕ ДЕЛАЕМ"
// $(document).on('click', '.not_do h4', function(){
//     $('.not_do').toggleClass('uncut'); 
// });

// включаем и настраиваем календарь
$(function () {
    var dtcfg = {
        inline: true,
        sideBySide: false,
        format: 'L',
        locale: 'ru'
    };
    if (typeof moment !== 'undefined') {
        dtcfg.minDate = moment()
    }
    try {
        $('#order_datetimepicker').datetimepicker(dtcfg);
    } catch ($e) {
        console.warn('НЕТ datapicker');
    }
});

// скрипт превращения input[type=number] в кнопки [+] и [-]
$(document).ready(function() {
    $('.minus').click(function () {
        var $input = $(this).parent().find('input');
        var count = parseInt($input.val()) - 1;
        count = count < 1 ? 1 : count;
        $input.val(count);
        $input.change();
        return false;
    });
    $('.plus').click(function () {
        var $input = $(this).parent().find('input');
        $input.val(parseInt($input.val()) + 1);
        $input.change();
        return false;
    });
});

// при загрузке страницы чекбокс выбран
if($('#company_n').length){
    $('#company_n')[0].checked=true;
}
// манипуляции с инпутами если клиент и компании
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

$('div.open>.dropdown-menu').click(
    function settown() {
        

        $.ajax({
            type : 'GET',
            url : 'ajax/setTown.php?town='+$("#town").val(),

        });
    }
)


