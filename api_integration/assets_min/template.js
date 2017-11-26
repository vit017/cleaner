/*
* TEMPLATE for js core of api_int
*/

function init_select() {
    time_select();
    $('.js-select').each(function () {
        $(this).hide();
        var opts1 = '';
        $(this).find('option').each(function () {
            if ($(this).attr('id') != 'pre_order_option') opts1 += '<li><a data-href="' + $(this).data('id') + '">' + $(this).html() + '</a></li>';
            else opts1 += '<li><a id="pre_order_a" data-href="' + $(this).data('id') + '">' + $(this).html() + '</a></li><div class="custom_datetimepicker">' + $('.custom_datetimepicker_content').html() + '</div>';
        })
        $(this).after('<div class="js-select-wrap"><a class="js-select-a">' + $(this).find('option:first-child').html() + '<span></span></a><div class="js-select-cont"><ul>' + opts1 + '</ul></div></div>');

    });

    $('.js-select-a').each(function () {

        $(this).on('click', function (e) {

            if ($(this).next('.js-select-cont').css('display') == 'block') {
                $('.js-select-cont').hide();
            }
            else {
                $('.js-select-cont').hide();
                $(this).next('.js-select-cont').show();
            }
            e.preventDefault();
            e.stopPropagation();
            $(this).next('.js-select-cont').on('click', function (e) { e.stopPropagation() })
            $('body').on('click', function () { $('.js-select-cont').hide() })
        })
    })

    $('.js-select-cont li>a').on('click', function () {
        var clickid = $(this).data('href');
        $(this).parents('.js-select-wrap').prev('.js-select').find('option').each(function () {
            $(this).removeAttr('selected');
        });
        $('.js-select [data-id="' + clickid + '"]').attr('selected', 'selected').trigger('change');

        if ($(this).parents('.js-select-wrap').parent().hasClass('time_select')) {
            time_select($(this));
        }
        if ($(this).attr('id') != 'pre_order_a') {
            $(this).parents('.js-select-wrap').find('.js-select-a').html($(this).html() + '<span></span>');
            $(this).parents('.js-select-cont').hide();
        };

    });

    $('.js-select-cont').on('click', 'ul li label', function () {
        var sc_c = '';
        $(this).parents('ul').find('li label').each(function () {
            if ($(this).find('input[type="checkbox"]').prop("checked")) {
                sc_c += $(this).text() + ', ';

            }


        });
        if(sc_c!=='') sc_c = sc_c.substring(0, sc_c.length - 2);
        $('.checked_opt').html(sc_c);
    })

    $('.cls-sel').on('click', function () {
        $(this).parents('.js-select-cont').hide();
    })

    function time_select(item) {
        if ($('.time_select [data-id="o02"]').attr('selected') == 'selected') {
            $('.custom_datetimepicker').show().addClass('opened');
            $(item).addClass('active');
        } else {
            $('.custom_datetimepicker').removeClass('opened');
            setTimeout(function () {
                $('.custom_datetimepicker').hide();
            }, 310);
            $('#time').val('').attr('value', '');
            $('.custom_datetimepicker').parent().find('.active').removeClass('active');
        }
    };

    $('.custom_datetimepicker a').on('click', function () {
        var timestr = $('.custom_datetimepicker select option:selected').html() + ' ' + $('.custom_datetimepicker input').val() + ':00';
        $('#time').val(timestr).attr('value', timestr);


        //Call cost
        $('body').trigger('callCost');


        $('.time_select .js-select-wrap').find('.js-select-a').html(timestr + '<span></span>');
        $('.time_select .js-select-cont').hide();
    })
};