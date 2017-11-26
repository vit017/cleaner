$(document).ready(function () {
    taxi.suggestCaller.setApiMethodName('findGeoObjects');
    var txtMessageMin = ' мин.';
    var txtMessageLey = ' руб';
    var txtTimeWithoutTrafficJams = 'Время без учета пробок ';
    var txtFixedPrice = 'Фиксированная стоимость: ';
    var txtMessageKm = ' км';
    var txtCallCostError = 'Не удалось расчитать стоимость';
    var txtCallCostInProgress = 'Идет расчет стоимости...';
    var txtSampleCalculation = 'Примерный расчёт';

    console.log($('#time').val());

    function getCostFromApi() {
        if (needSendToValidate() === true) {
            var values = taxi.ordering.createOrderQuery();
            var callCostQuery = new TaxiMethod('callCost');
            callCostQuery.tryCount = 1;
            callCostQuery.params = values;
            callCostQuery.successCallback = function (result) {
                if (result.summary_cost !== null && result.summary_cost !== 0) {
                    taxi.lastCostfromApi = result;
                    var len = Math.round(result.summary_distance);
                    var time = Math.round(result.summary_time);
                    var cost = Math.round(result.summary_cost);
                    var timeLine = '';
                    timeLine = '<p>' + txtTimeWithoutTrafficJams + time + txtMessageMin + '</p>';

                    $('#list').html('<span>' + txtSampleCalculation + '</span><b>' + len + txtMessageKm + '</b><b>' + time + txtMessageMin + '</b><b>' + cost + txtMessageLey + '</b>');
                    if (result.isFix == '1') {
                        $('#pdata').html(txtFixedPrice + '<span id="cost_order" style="color: black;">' + cost + txtMessageLey + '</span>');
                    }
                } else {
                    taxi.lastCostfromApi = null;
                    $('#cost_order').html(String(txtCallCostError));
                }

            };
            callCostQuery.errorCallback = function () {
                taxi.lastCostfromApi = null;
                $('#cost_order').html(String(txtCallCostError));
            };
            $('#cost_order').html('<p>' + txtCallCostInProgress + '</p>');
            taxi.taxiClient.executeQuery(callCostQuery);
        }
    }

    function needSendToValidate() {
        //console.log($('#FIELD_ADDRESS').length());
        if (($('#FIELD_ADDRESS').val() !== "") && ($("#FIELD_ADDRESS_TO").val() !== "")) {
            return true;
        }
        else {
            return false;
        }
    }



    $.each([$('#FIELD_CITY_OTKUDA'), $("#FIELD_FROM"), $("#FIELD_FROM_HOUSE"),
        $("#FIELD_FROM_HOUSING"), $('#FIELD_CITY_KUDA'), $("#FIELD_TO"),
        $("#FIELD_TO_HOUSE"), $("#FIELD_TO_HOUSING"), $("#FIELD_ADDRESS"), $("#FIELD_ADDRESS_TO")], function () {
            $(this).on('blur', function () {
                getCostFromApi();
            });
        });

    taxi.routeInfoFunction = function (res) {
        getCostFromApi();
    };



    //ПОЛУЧАЕМ ТАРИФЫ ДЛЯ Online-Msk
    function getTariffs() {
        var getTariffs = new TaxiMethod('findTariffs');
        getTariffs.tryCount = 1;
        getTariffs.successCallback = function (result) {
            if (result && result != "") {
                $('#tariff_travel').before(
										'<div class="sp6_tar" id="tariff_travelNew">' +
												'<select class="js-select" style="display:none">' +
												'</select>' +
												'<div id="list" class="price">' +
												'</div>' +
										'</div>');
                $('#tariff_travel').remove();
                $("#tariff_travelNew").attr("id", "tariff_travel");
                for (var i in result) {
                    var current = result[i];
                    var currentId = current['id'];
                    var currentLabel = current['label'];
                    var additional = JSON.stringify(current['additional']);
                    var arrTranslateTariffs = { '107': 'Standart', '177': 'Business' };
                    if ($.cookie('USER_LANG') == 'ro') {
                        currentLabel = arrTranslateTariffs[currentId];
                    }
                    $("#tariff_travel .js-select").append("<option data-id='" + currentId + "' data-additional='" + additional + "' value='" + currentId + "'>" + currentLabel + "</option>");
                }

                var firstTar = result.shift().additional;
                for (var i in firstTar) {
                    var currentAdd = firstTar[i];
 
                    $('.sp6_opt .js-select-cont ul').append("<li><label><input type='checkbox' value='" + currentAdd.id + "' />" + currentAdd.name + "</label></li>");
                }                
            }
            init_select();
        };

        getTariffs.errorCallback = function () {
        };
        taxi.taxiClient.executeQuery(getTariffs);
    }

    getTariffs();

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
            $('.js-select [data-id="' + clickid + '"]').attr('selected', 'selected');

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
                $('.checked_opt').html(sc_c);

            });
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
            $('.time_select .js-select-wrap').find('.js-select-a').html(timestr + '<span></span>');
            $('.time_select .js-select-cont').hide();
        })

        $('#tariff_travel .js-select-cont a').click(function () {
            var adds = $('#tariff_travel .js-select option[data-id="' + $(this).data('href') + '"]').data('additional');

            $('.sp6_opt .js-select-cont ul').empty();
            $('.checked_opt').empty();
            for (var i in adds) {
                var currentAdd = adds[i];

                $('.sp6_opt .js-select-cont ul').append("<li><label><input type='checkbox' value='" + currentAdd.id + "' />" + currentAdd.name + "</label></li>");
            }
        });

        $('.js-select-cont').on('click', function () {
            getCostFromApi();
        })

        $('.dop_input').on('click', function () {
            getCostFromApi();
        });

        $('.label').on('click', function () {
            getCostFromApi();
        });
    };
});
