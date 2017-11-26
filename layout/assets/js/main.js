var orderSlider = (function () {
    var me = {};

    var sliderIndex = -1;

    var $oldSmallPic,
        $newSmallPic,
        $oldBigPic,
        $newBigPic,
        $item;

    var title,
        smallPicUrl,
        bigPicUrl;

    var style,
        left;

    function changeSlide($object, dir, index) {
        if (sliderIndex == -1) {
            $item = $object.find('.order-slider__item').eq(0);
            title = $item.data('title');
            smallPicUrl = $item.data('smallpic');
            bigPicUrl = $item.data('bigpic');
            $newBigPic = $('<img src="'+bigPicUrl+'">');
            $newSmallPic = $('<img src="'+smallPicUrl+'">');

            $object.find('.order-slider__frame_type_desktop .order-slider__frame-content').append($newBigPic);
            $object.find('.order-slider__frame_type_phone .order-slider__frame-content').append($newSmallPic);
            $object.find('.order-slider__title').text(title);
            $('.order-slider__nav_prev').addClass('order-slider__nav_disabled');

        } else {
            if (!$('.order-slider__frame_type_desktop img').is(':animated')) {
                if (index >= 0) {
                    switch (true) {
                        case index < sliderIndex:
                            dir = 'prev';
                            break;
                        case index > sliderIndex:
                            dir = 'next';
                            break;
                        case index == sliderIndex:
                            return false;
                    }
                }
                if (dir == 'prev' && sliderIndex != 0) {
                    if (!(index >= 0)) {
                        sliderIndex = sliderIndex - 1
                    } else {
                        sliderIndex = index;
                    }
                    $item = $object.find('.order-slider__item').eq(sliderIndex);
                    style = 'style="left: -100%;"';
                    left = '100%';

                } else if (dir == 'next' && $object.find('.order-slider__item').eq(sliderIndex + 1).length) {
                    if (!(index >= 0)) {
                        sliderIndex = sliderIndex + 1
                    } else {
                        sliderIndex = index;
                    }
                    $item = $object.find('.order-slider__item').eq(sliderIndex);
                    style = 'style="left: 100%;"';
                    left = '-100%';
                } else {
                    return false;
                }

                title = $item.data('title');
                smallPicUrl = $item.data('smallpic');
                bigPicUrl = $item.data('bigpic');
                $newBigPic = $('<img src="'+bigPicUrl+'"'+style+'>');
                $newSmallPic = $('<img src="'+smallPicUrl+'"'+style+'>');
                $oldBigPic = $object.find('.order-slider__frame_type_desktop img');
                $oldSmallPic = $object.find('.order-slider__frame_type_phone img');
                $object.find('.order-slider__frame_type_desktop .order-slider__frame-content').append($newBigPic);
                $object.find('.order-slider__frame_type_phone .order-slider__frame-content').append($newSmallPic);
                $object.find('.order-slider__title').text(title);

                $oldBigPic.animate({'left': left}, 300, function() {
                    $(this).remove();
                });
                $oldSmallPic.animate({'left': left}, 300, function() {
                    $(this).remove();
                    if (dir == 'prev' && sliderIndex == 0) {
                        $('.order-slider__nav_prev').addClass('order-slider__nav_disabled');
                        $('.order-slider__nav_next').removeClass('order-slider__nav_disabled');
                    } else if (dir == 'next' && !$object.find('.order-slider__item').eq(sliderIndex + 1).length) {
                        $('.order-slider__nav_next').addClass('order-slider__nav_disabled');
                        $('.order-slider__nav_prev').removeClass('order-slider__nav_disabled');
                    } else if (dir == 'next' && sliderIndex != 0) {
                        $('.order-slider__nav_prev').removeClass('order-slider__nav_disabled');
                    } else if ($object.find('.order-slider__item').eq($item.index() + 1).length) {
                        $('.order-slider__nav_next').removeClass('order-slider__nav_disabled');
                    }
                });
                $newBigPic.animate({'left': 0}, 300, function() {});
                $newSmallPic.animate({'left': 0}, 300, function() {});

            }
        }

        sliderIndex = $item.index();
        $('.order-slider__indicator-item')
            .eq(sliderIndex).addClass('order-slider__indicator-item_active')
            .siblings('.order-slider__indicator-item').removeClass('order-slider__indicator-item_active');
        $object.data('index', sliderIndex);

    }

    me.init = function (url) {
        var $slider = $('.order-slider');

        if ($slider.hasClass('order-slider_inited')) {
            return false;
        }

        changeSlide($slider);

        $slider.on('click', '.order-slider__nav_prev', function() {
            changeSlide($slider, 'prev');
        });

        $slider.on('click', '.order-slider__nav_next', function() {
            changeSlide($slider, 'next');
        });
        $slider.on('click', '.order-slider__indicator-item ', function() {
            var index = $(this).index();
            changeSlide($slider, '', index);
        });

    };

    return me;

}());


var modalMenu = (function () {

    var me = {};
    var $menu = $('.modal-menu');
    var ESC_KEY = 27;

    me.isShown = false;

    me.init = function () {

        $('.page-header__control_type_menu').on('click.openMenu', function() {
            if (!me.isShown) {
                me.Show();
            } else {
                me.Hide();
            }
        });

        $(document).on('keyup.modal', function (e) {
            if (e.keyCode == ESC_KEY && me.isShown) {
                me.Hide();
            }
        });

        $menu.on('click', '.modal-menu__header-close', function() {
            me.Hide();
        });

    };

    me.Show = function () {
        $menu.show();
        setTimeout(function() {
            me.isShown = true;
            $menu.addClass('modal-menu_state_visible');
        }, 10);
        $('body').addClass('menu-open');
    };

    me.Hide = function () {
        $menu.removeClass('modal-menu_state_visible');
        $menu.one($.support.transition.end, function() {
            $menu.hide();
            $('body').removeClass('menu-open');
            me.isShown = false;
        }).emulateTransitionEnd(300);
    };

    return me;

}());

var orderCalendar = (function () {

    var me = {};
    var calendars = {};
    me.init = function (daysArray) {

        var $calendar = $('.order-calendar');
        var $calendarValue = $calendar.find('.order-calendar__date');

        calendars.clndr1 = $('.order-calendar__content').clndr({
            events: daysArray,
            constraints: {
                startDate: moment().format('YYYY-MM-DD')
                //endDate: '2013-11-15'
            },
            weekOffset: 0,
            daysOfTheWeek: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
            clickEvents: {
                click: function(target) {
                    if(!$(target.element).hasClass('inactive') && $(target.element).hasClass('event')) {
                        // сохраняем дату в input hidden
                        $calendar.find('.day').removeClass('selected');
                        $calendarValue.val(target.date._i);
                        $(target.element).addClass('selected');
                        for (var i = 0; i < target.events[0].timing.length; i++) {
                            if (target.events[0].timing[i].disable == 'true') {
                                $('.time-picker__item').eq(i).find('.time-picker__item-control').prop('disabled', true);
                            } else {
                                $('.time-picker__item').eq(i).find('.time-picker__item-control').prop('disabled', false);
                            }
                            $('.time-picker__item').eq(i).find('.time-picker__item-control').prop('checked', false);
                        }
                    }
                },
                onMonthChange: function() {
                    if(typeof $calendarValue.val() !== 'undefined'){
                        // восстанавливаем выбранную дату при переключении месяца
                        $calendar.find('.day').removeClass('selected');
                        $('.calendar-day-'+$calendarValue.val()).addClass('selected');
                    }
                }
            },
            showAdjacentMonths: true,
            adjacentDaysChangeMonth: false,
            selectedDate: '2016-12-14'
        });
        return calendars.clndr1;
    };

    return me;

}());

var cityDropdown = (function () {

    var me = {};

    me.init = function () {
        me.bindUIEvents();
    };

    me.bindUIEvents = function () {
        var openClassName = 'city-dropdown_state_open';

        $('.city-dropdown').on('click','.city-dropdown__title', function(e) {
            e.stopPropagation();
            var $dropdown = $(this).closest('.city-dropdown');
            var $contentBlock = $dropdown.find('.city-dropdown__content');

            if ($dropdown.hasClass(openClassName)) {
                $dropdown.removeClass(openClassName)
            } else {
                $dropdown.addClass(openClassName)
            }
        });

        $('body').on('click.city-dropdown', function(e) {
            if ($(e.target).is('.city-dropdown__content')) {
                return;
            } else {
                $('.city-dropdown').removeClass(openClassName);
            }
        });
    };


    return me;

}());
$(document).ready(function () {

    // init modal menu
    if ($('.modal-menu').length) {
        modalMenu.init();
    }

    //init order-slider
    if ($('.order-slider').length) {
        orderSlider.init();
    }

    // calendar init
    if ($('.order-calendar').length) {
        orderCalendar.init(daysAvailable);
        var curDate = new Date();
        var curDateString = curDate.getFullYear() + '-' + curDate.getMonth() + '-' + curDate.getDay();
        var day = daysAvailable[daysAvailable.length - 1];
        console.log('.calendar-day-curDateString-' + day.date);
        $('.calendar-day-' + day.date).click();

    }

    // floated labels init
    $('.input-txt__field').floatlabel();

    // count inputs init
    $('.count-input').countInput();

    // count inputs init
    $('.rating-input').ratingInput();

    // last slide height
    //$('.form-section').height($(window).height());

    // feature slider om main page
    $('.type-slider').on('click', '.type-slider__nav-item', function() {

        var $link = $(this),
            $slider = $link.parents('.type-slider');

        if (!$link.hasClass('type-slider__nav-item_active')) {

            var index = $link.index();

            $link
                .addClass('type-slider__nav-item_active')
                .siblings('.type-slider__nav-item').removeClass('type-slider__nav-item_active');

            $slider.find('.type-slider__item').eq(index)
                .addClass('type-slider__item_active')
                .siblings('.type-slider__item').removeClass('type-slider__item_active');

        }

    });

    //animated scroll to anchor
    $('.js-scroll').on('click', function() {
        var id = $(this).data('target');
        $('html, body').animate({ scrollTop: $(id).offset().top}, 500);
    });



    cityDropdown.init();

    //всплывающее окно "Перезвонить"
    var timeToSite=getCookie("timeToSite");
    var showPopUp=getCookie("showPopUp");
    if(!showPopUp){
        if (!timeToSite)
            setCookie("timeToSite",1,30);
        setInterval(function() {
            console.log(timeToSite);
            if (timeToSite<=60){
                timeToSite++;
                setCookie("timeToSite",timeToSite,30);
            }else{
                showPopUp=getCookie("showPopUp");
                if(!showPopUp){
                    setCookie("showPopUp","ok",30);
                    $(".callBackLink").trigger("click");
                }
            }
        }, 1000);
    }

    $(".callBackForm").submit(function() {
        $.ajax({
            type: "POST", 
            url: "/bitrix/templates/index/ajax/callbackform.php",  
            data: $(this).serialize(),  
            success: function(html){  
                $(".callBackForm").html(html);  
            }
        });     
        return false;
    }); 

    $(".feedbackForm").submit(function() {
        $.ajax({
            type: "POST", 
            url: "/bitrix/templates/index/ajax/feedbackform.php",  
            data: $(this).serialize(),  
            success: function(html){  
                $(".feedbackForm").html(html);  
            }
        });     
        return false;
    }); 

    $(".feedbackCleaner").submit(function() {
        $.ajax({
            type: "POST", 
            url: "/bitrix/templates/index/ajax/feedbackform_cleaner.php",  
            data: $(this).serialize(),  
            success: function(html){  
                $(".feedbackCleaner").html(html);  
            }
        });     
        return false;
    });

});

    $('.js-city-select-time').selectize();
    $('.js-city-select-city').selectize();
    $('.js-city-select').selectize();
    $('.js-city-select_personal').selectize();

    $('.js-custom-select').selectize({
        allowEmptyOption: true
    });

    $('.selectize-input input').attr('readonly', 'true');


// САМОПИСНЫЙ ТАЙМЕР НА ГЛАВНОЙ
if($('#sec').length){
    var maxPerc = (Math.round(Math.random()*((1500 - 1000)) + 1000)*10); // рандомное максимальное время
    var sec = 1;
    var span = document.getElementById('sec');
    var timer = setInterval(function(){
        sec += 551; //сколько прибавлять
        var h = sec/3600 ^ 0;
        var m = (sec-h*3600)/60 ^ 0;
        var s = sec-h*3600-m*60;
        span.innerHTML = ((h<10?"0"+h:h)+"<span>:</span>"+(m<10?"0"+m:m)+"<span>:</span>"+(s<10?"0"+s:s));
        if(sec >= maxPerc) { // останавливаем таймер при достижении максимального времени
            clearInterval(timer);
        }
    }, 100); // 0.1 секунда
}


// КАЛЕНДАРЬ в заказе
$( "#datepicker" ).datepicker({
    inline: true,
    showOtherMonths: true,
    selectOtherMonths: true,
    minDate: -0,
    maxDate: "+1M",
    monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
    dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
    firstDay: 1,
    dateFormat: "dd.mm.yy",
    dayNames: ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"],
    monthNamesShort: [ "Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря"],
    //beforeShowDay: $.datepicker.noWeekends, // закрываем выходные
});

// КАЛЕНДАРЬ в MNOGO
$( "#datepicker_mnogo" ).datepicker({
    inline: true,
    showOtherMonths: true,
    selectOtherMonths: true,
    changeMonth: true,
    changeYear: true,
    //minDate: -0,
    maxDate: "-10Y",
    monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
    dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
    firstDay: 1,
    dateFormat: "DD, d M, yy",
    dayNames: ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"],
    monthNamesShort: [ "Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря"],
    //beforeShowDay: $.datepicker.noWeekends, // закрываем выходные
});

// Заменяем полноценное меню на меню-полоску и обратно при скроллинге
// $('.fixed_block').hide();
jQuery(document).scroll(function() {
    if (jQuery(document).scrollTop() > 150 ) {
        $('.fixed_block').slideDown();
    } else {
        $('.fixed_block').slideUp();
    }
});

// инициируем ТАБЫ
$( "#tabs" ).tabs();

// инициируем АККОРДЕОН
$( function() {
    $( "#accordion" ).accordion({
        heightStyle: "content"
    });
} );

//инициируем маски ввода данных
$('input[name="mnogoru"]').inputmask("9999 9999");  // для номера карты Много.ру
$('input[name="phone"]').inputmask("+7 (999) 9999999");  // для телефона




$(document).bind("ajaxComplete",function(){

    $( "#datepicker" ).datepicker({
        inline: true,
        showOtherMonths: true,
        selectOtherMonths: true,
        minDate: -0,
        maxDate: "+1M",
        monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        firstDay: 1,
        dateFormat: "dd.mm.yy",
        dayNames: ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"],
        monthNamesShort: [ "Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря"],
        //beforeShowDay: $.datepicker.noWeekends, // закрываем выходные
    });


    $('input[name="mnogoru"]').inputmask("9999 9999");  // для номера карты Много.ру
    $('input[name="phone"]').inputmask("+7 (999) 9999999");  // для телефона
    
});





function setCookie(cname,cvalue,exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires=" + d.toGMTString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}