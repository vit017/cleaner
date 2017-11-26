/*
* JavaScript Core for Gootax client site
*/


//Function limiter

Function.prototype.throttle = function (ms) {
    var func = this;
    var isThrottled = false,
        savedArgs,
        savedThis;

    function wrapper() {

        if (isThrottled) {
            savedArgs = arguments;
            savedThis = this;
            return;
        }

        func.apply(this, arguments);

        isThrottled = true;

        setTimeout(function() {
            isThrottled = false;
            if (savedArgs) {
                wrapper.apply(savedThis, savedArgs);
                savedArgs = savedThis = null;
            }
        }, ms);
    }

    return wrapper;
}


var setCookie = function(name, value){
    Cookies.set(name, value, {
        expires: 365,
        path: "/"
    });
}


//Ajax

var doAjax = function(method) {
    var url = config.ajaxClientUrl + '?command=' + method.name;
    $.ajax({
        async: (typeof(method.async)!== 'undefined') ? method.async : true,
        url: url,
        type: 'post',
        timeout: (typeof(method.timeout)!== 'undefined') ? method.timeout : 5000,
        data: (typeof(method.params)!== 'undefined') ? method.params : false,
        dataType: 'json',
        success: function(response) {
            if (parseInt(response.status) > 0) {
                method.successCallback(response.result);
            } else {
                method.errorCallback();
            }
        },
        error: function() {
            if (method.tryCount > 1) {
                method.tryCount = method.tryCount - 1;
                setTimeout(function() {
                    doAjax(method);
                }, method.tryPause);
            }
            method.errorCallback();
        }
    });
    return self;
};


//Init

var init_core = function (config) {
    //Connect styles
    if(config.enableMap) $('head').append('<link rel="stylesheet" href="/api_integration/assets_min/leaflet.css" type="text/css" />');


    var city = {
        name: window.city,
        lat: Cookies.get('CITY_LAT'),
        lon: Cookies.get('CITY_LON')
    };
    var geoservice = window.geoservice;
    $(config.ids.phoneNumber).mask(config.inputMask);
    var order = {};
    var routeControl;


    //Map init
    if(config.enableMap) {
        L.Icon.Default.imagePath = '/api_integration/assets_min/images/';

        var map = new L.Map(config.ids.map, {zoomAnimation: false, scrollWheelZoom: false});

        var aIcon = L.icon({
            iconUrl: config.mapAIconSrc,
            iconSize: config.mapAIconSize,
            iconAnchor: config.mapAIconAnchor,
        });

        var bIcon = L.icon({
            iconUrl: config.mapBIconSrc,
            iconSize: config.mapBIconSize,
            iconAnchor: config.mapBIconAnchor,
        });

        var mapLine = {styles: config.mapLineStyle, missingRouteStyles: config.mapMissingLineStyle};

        map.on('load', function () {
            routeControl = L.Routing.control({
                fitSelectedRoutes: true, createMarker: function (i, wp) {
                    if (i == 0) var marker = L.marker(wp.latLng, {icon: aIcon});
                    else var marker = L.marker(wp.latLng, {icon: bIcon});
                    return marker;
                }, lineOptions: mapLine
            });
            getCarsFunction();
        });


        map.setView([city.lat, city.lon], config.mapZoom);

        var mapLayer = null;
        if (geoservice == 'yandex') mapLayer = new L.Yandex();
        if (geoservice == 'google') mapLayer = new L.Google('ROADMAP');
        if (geoservice == 'osm') mapLayer = new L.TileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
        if (geoservice == 'bing') {
            var imagerySet = "AerialWithLabels";
            mapLayer = new L.BingLayer("LfO3DMI9S6GnXD7d0WGs~bq2DRVkmIAzSOFdodzZLvw~Arx8dclDxmZA0Y38tHIJlJfnMbGq5GXeYmrGOUIbS2VLFzRKCK0Yv_bAl6oe-DOc", {type: imagerySet});
        }

        map.addLayer(mapLayer);
    }


    //Getting tariffs

    var getTariffs = {
        name: 'findTariffs',
        tryCount: 2,
        successCallback: function (result) {
            if (result && result != "") {
                for (var i in result) {
                    var current = result[i];
                    var currentId = current['id'];
                    var currentLabel = current['label'];
                    $(config.ids.tariffSelect).append("<option data-id='" + currentId + "' value='" + currentId + "'>" + currentLabel + "</option>");
                };

                config.getTariffsSuccess();
            }
        },
        errorCallback: function () {
            console.log('Ошибка получения тарифов');
        }
    };

    doAjax(getTariffs);


    //Autocomplete

    var autocompleteObject={
        name: 'findGeoObjects',
        params: {
            maxLimit: config.autocompleteMaxLimit,
            city: city.name,
        },
        successCallback: function (res) {
            var autocompleteWrapper = (autocompleteObject.field=='from') ? config.ids.fromAutocomplete : config.ids.toAutocomplete;
            var autocompleteInput = (autocompleteObject.field=='from') ? config.ids.fromAddress : config.ids.toAddress;
            $(autocompleteWrapper).html('');
            if(res.length!=0) for(var key in res) $(autocompleteWrapper).append("<li data-res='"+JSON.stringify(res[key])+"'>"+res[key].label+"</li>");
            else $(autocompleteWrapper).append('<span>'+config.autocompleteNoResultsText+'</span>');

            if(!$(autocompleteInput).hasClass('locked_input')) $(autocompleteWrapper).addClass('active_autocomplete');
            $('.loading_autocomplete').removeClass('loading_autocomplete');
        },
        errorCallback: function () {
            console.log('Ошибка автокомплита')
        }
    };

    var getObjects = doAjax.throttle(1000);

    //From autocomplete
    $(config.ids.fromAddress).on('keyup', function (event) {
        if (event.keyCode === 8) {
            order.from = undefined;
            $('body').trigger('changeRoute');
        }
        if (event.keyCode === 13) {
            event.stopPropagation();
            event.preventDefault();
        } else{
            $(this).removeClass('locked_input');
            if ($(this).val().length > 2){
                $(this).addClass('loading_autocomplete');
                autocompleteObject.params.streetPart=$(this).val();
                autocompleteObject.field = 'from';
                getObjects(autocompleteObject);
            } else{
                $(config.ids.fromAutocomplete).removeClass('active_autocomplete');
            }
        }
    })

    //To autocomplete
    $(config.ids.toAddress).on('keyup', function (event) {
        if (event.keyCode === 8) {
            order.to = undefined;
            $('body').trigger('changeRoute');
        }
        if (event.keyCode === 13) {
            event.stopPropagation();
            event.preventDefault();
        } else{
            $(this).removeClass('locked_input');
            if ($(this).val().length > 2){
                $(this).addClass('loading_autocomplete');
                autocompleteObject.params.streetPart=$(this).val();
                autocompleteObject.field = 'to';
                getObjects(autocompleteObject);
            } else{
                $(config.ids.toAutocomplete).removeClass('active_autocomplete');
            }
        }
    })

    $('body').on('click', function (e) {
        if (!$('.active_autocomplete').is(e.target) && $('.active_autocomplete').has(e.target).length === 0) {
            $('.active_autocomplete').removeClass('active_autocomplete');
        }
    });

    //Autocomplete select
    $('body').on('click', '.active_autocomplete li', function (e) {
        var selectedParams = $(this).data('res');
        var autocompleteWrapper = (autocompleteObject.field=='from') ? config.ids.fromAddress : config.ids.toAddress;
        $(autocompleteWrapper).val(selectedParams.label).addClass('locked_input');

        $(this).parents('.active_autocomplete').removeClass('active_autocomplete');


        if(autocompleteObject.field=='from') order.from = $.extend(true, {}, selectedParams);
        else order.to = $.extend(true, {}, selectedParams);

        config.onAutocompleteSelect(autocompleteWrapper);

        $('body').trigger('changeRoute').trigger('callCost');
    })


    //Route creator

    $('body').on('changeRoute', function () {
        if(config.enableMap) {
            if (typeof(order.from) !== 'undefined') {
                if (typeof(order.to) !== 'undefined') {
                    routeControl.setWaypoints([
                        L.latLng(order.from.address.location[0], order.from.address.location[1]),
                        L.latLng(order.to.address.location[0], order.to.address.location[1])
                    ]);

                } else {
                    routeControl.setWaypoints([
                        L.latLng(order.from.address.location[0], order.from.address.location[1])
                    ]);
                }
            } else {
                routeControl.setWaypoints([]);
            }

            routeControl.addTo(map);
        }
    })


    //Getting cars

    function getCarsFunction() {
        if(config.enableMap) {
            var redCarIcon = L.icon({
                iconUrl: config.mapRedCarIconSrc,
                iconSize: config.mapRedCarIconSize,
                iconAnchor: config.mapRedCarIconAnchor,
                popupAnchor: config.mapRedCarPopupAnchor
            });

            var greenCarIcon = L.icon({
                iconUrl: config.mapGreenCarIconSrc,
                iconSize: config.mapGreenCarIconSize,
                iconAnchor: config.mapGreenCarIconAnchor,
                popupAnchor: config.mapGreenCarPopupAnchor
            });

            var carLayer = L.layerGroup();

            var getCars = {
                name: 'findCars',
                successCallback: function (res) {
                    carLayer.clearLayers();
                    var currentMarker, currentIcon;
                    for (var key in res) {
						if(res[key].lat!=null && res[key].lon!=null){
							currentIcon = (res[key].isFree == 0) ? redCarIcon : greenCarIcon;

							currentMarker = L.marker([res[key].lat, res[key].lon], {icon: currentIcon});
							currentMarker.bindPopup(res[key].description);
							carLayer.addLayer(currentMarker);
						}
                    }
                    carLayer.addTo(map);
                },
                errorCallback: function () {
                    console.log('Ошибка получения списка машин')
                }
            }

            doAjax(getCars);

            setInterval(function () {
                doAjax(getCars);
            }, config.mapCarsUpdateTimeout);
        }
    }


    //Call cost
    
    $('body').on('callCost', function () {
        $('.call_cost_price').css('opacity', 0);
        if (typeof(order.from)!=='undefined' && typeof(order.to)!=='undefined') {
            var carType = $(config.ids.tariffSelect).find(':selected');
            var callCost = {
                name: 'callCost',
                params: {
                    fromCity: '',
                    fromStreet: order.from.label,
                    fromHouse: '',
                    fromHousing: '',
                    fromPorch: '',
                    fromLat: order.from.address.location[0],
                    fromLon: order.from.address.location[1],
                    toCity: '',
                    toStreet: order.to.label,
                    toHouse: '',
                    toHousing: '',
                    toPorch: '',
                    toLat: order.to.address.location[0],
                    toLon: order.to.address.location[1],
                    clientName: $(config.ids.fio).val(),
                    phone: $(config.ids.phoneNumber).val(),
                    priorTime: $(config.ids.orderTime).val(),
                    customCarId: '',
                    customCar: '',
                    carType: $(carType).text(),
                    carGroupId: '',
                    tariffGroupId: $(carType).data('id'),
                    comment: ''
                },
                successCallback: function (res) {
                    $(config.ids.cost).html('');
                    if(typeof(res.summary_cost)!=='undefined'){
                        var callCostResult = '<span class="call_cost_title">'+config.callCostTitle+'</span>'+
                            '<b class="call_cost_dist">'+Math.round(parseFloat(res.summary_distance))+' '+config.callCostDist+'</b>'+
                            '<b class="call_cost_time">'+Math.round(parseFloat(res.summary_time))+' '+config.callCostTime+'</b>'+
                            '<b class="call_cost_price">'+Math.round(parseFloat(res.summary_cost))+' '+config.callCostCurrency+'</b>';
                        $(config.ids.cost).html(callCostResult);
                        $('.call_cost_price').css('opacity', 1);
                    }
                },
                errorCallback: function () {
                    console.log('Ошибка получения стоимости')
                }
            };
            doAjax(callCost);
        };

    })

    $(config.ids.tariffSelect).on('change', function () {
        $('body').trigger('callCost');
    })


    //Step 2: validateCommand

    var validateOrderFunc = doAjax.throttle(1000);

    $('body').on('click', config.ids.goToStep2Button, function (e) {
        e.preventDefault();

        if(!$(this).hasClass('loading_button')){
            $(this).addClass('loading_button');
            var carType = $(config.ids.tariffSelect).find(':selected');
            var valTime = $(config.ids.orderTime).val();
            var orderTime = (valTime != '') ? valTime.replace(/[. :]/g, '') : '';
            var validateOrder = {
                name: 'validateCommand',
                params: {
                    command: 'createOrder',
                    paramsToValidate: {
                        fromCity: '',
                        fromStreet: (typeof(order.from) !== 'undefined') ? order.from.label : '',
                        fromHouse: '',
                        fromHousing: '',
                        fromPorch: $(config.ids.fromPorch).val(),
                        fromLat: (typeof(order.from) !== 'undefined') ? order.from.address.location[0] : '',
                        fromLon: (typeof(order.from) !== 'undefined') ? order.from.address.location[1] : '',
                        toCity: '',
                        toStreet: (order.to !== undefined) ? order.to.label : '',
                        toHouse: '',
                        toHousing: '',
                        toPorch: '',
                        toLat: (typeof(order.to) !== 'undefined') ? order.to.address.location[0] : '',
                        toLon: (typeof(order.to) !== 'undefined') ? order.to.address.location[1] : '',
                        clientName: $(config.ids.fio).val(),
                        phone: $(config.ids.phoneNumber).val(),
                        priorTime: orderTime,
                        customCarId: '',
                        customCar: '',
                        carType: $(carType).text(),
                        carGroupId: '',
                        tariffGroupId: $(carType).data('id'),
                        comment: $(config.ids.comment).val()
                    },
                },
                successCallback: function (res) {
                    if(res.hasErrors){
                        config.onValidateError(res);
                        $('.loading_button').removeClass('loading_button');
                    } else{
                        config.clearErrors();
                        $('body').trigger('needSendSms');
                    }
                },
                errorCallback: function () {
                    console.log('Ошибка создания заказа');
                    $('.loading_button').removeClass('loading_button');
                }
            }

            validateOrderFunc(validateOrder);
        }
    })


    //Needsendsms

    var needSendSmsFunc = doAjax.throttle(1000);

    $('body').on('needSendSms', function () {
        var needSendSms = {
            name: 'needSendSms',
            params: {
                phone: $(config.ids.phoneNumber).val()
            },
            successCallback: function (res) {
                if(res==1) $('body').trigger('sendSms');
                else if(res==0) $('body').trigger('createOrder');
                else if (res == 2) {
                    alert('Ваш телефон в чёрном списке!');
                    $('.loading_button').removeClass('loading_button');
                }
            },
            errorCallback: function () {
                console.log('Ошибка запроса needSendSms');
                $('.loading_button').removeClass('loading_button');
            }
        };
        needSendSmsFunc(needSendSms);
    })


    //Sendsms

    var sendSmsFunc = doAjax.throttle(1000);

    $('body').on('sendSms', function () {
        var sendSms = {
            name: 'sendSms',
            params: {
                phone: $(config.ids.phoneNumber).val()
            },
            successCallback: function (res) {
                if(res.success==true){
                    if(res.isAuthorizedNow==false){
                        $('.loading_button').removeClass('loading_button');
                        config.goToStep2();
                    }
                    else $('body').trigger('createOrder');
                } else sendSms.errorCallback();
            },
            errorCallback: function () {
                console.log('Ошибка отправки смс');
                $('.loading_button').removeClass('loading_button');
            }
        }
        sendSmsFunc(sendSms);
    })


    //Step 3: login

    var loginFunc = doAjax.throttle(1000);
    
    $('body').on('click', config.ids.goToStep3Button, function (e) {
        e.preventDefault();
        if(!$(this).hasClass('loading_button')){
            $(this).addClass('loading_button');
            config.clearErrors();
            var login = {
                name: 'login',
                params: {
                    phone: $(config.ids.phoneNumber).val(),
                    smsCode: $(config.ids.smsCode).val()
                },
                successCallback: function (res) {
                    if(res.success==true){
                        if(res.token && res.browserKey){
                            setCookie('api_token', res.token);
                            setCookie('api_browser_key', res.browserKey);
                        };
                        $('body').trigger('createOrder');
                    }
                    else {
                        config.onSmsError(res.text);
                        $('.loading_button').removeClass('loading_button');
                    }
                },
                errorCallback: function () {
                    console.log('Ошибка проверки кода смс');
                    $('.loading_button').removeClass('loading_button');
                }
            }
            loginFunc(login);
        }
    });


    //Create order

    var createOrderFunc = doAjax.throttle(1000);

    $('body').on('createOrder', function () {
        var comment = '';
        if($(config.ids.comment).val()!='') comment = $(config.ids.comment).val() + '. ' + $(config.ids.options).text();
        var carType = $(config.ids.tariffSelect).find(':selected');
        var valTime = $(config.ids.orderTime).val();
        var orderTime = (valTime != '') ? valTime.replace(/[. :]/g, '') : '';
        var createOrder = {
            name: 'createOrder',
            params: {
                fromCity: '',
                fromStreet: order.from.label,
                fromHouse: '',
                fromHousing: '',
                fromPorch: $(config.ids.fromPorch).val(),
                fromLat: order.from.address.location[0],
                fromLon: order.from.address.location[1],
                toCity: '',
                toStreet: (order.to !== undefined) ? order.to.label : '',
                toHouse: '',
                toHousing: '',
                toPorch: '',
                toLat: (typeof(order.to) !== 'undefined') ? order.to.address.location[0] : '',
                toLon: (typeof(order.to) !== 'undefined') ? order.to.address.location[1] : '',
                clientName: $(config.ids.fio).val(),
                phone: $(config.ids.phoneNumber).val(),
                priorTime: orderTime,
                customCarId: '',
                customCar: '',
                carType: $(carType).text(),
                carGroupId: '',
                tariffGroupId: $(carType).data('id'),
                comment: comment
            },
            timeout: 15000,
            successCallback: function (orderId) {
                if (orderId != 'waitTime' && orderId !== '' && orderId !== null && orderId !== false && orderId !== 'server_interanl_error') {
                    setCookie('order_id', orderId);
                    order.orderId = orderId;
                    $('body').trigger('getOrderInfo');
                } else{
                    createOrder.errorCallback();
                }

                $('.loading_button').removeClass('loading_button');
            },
            errorCallback: function () {
                $('.loading_button').removeClass('loading_button');
                console.log('Ошибка создания заказа');
            }
        }
        setCookie('order_from', order.from.address.location);
        if(typeof(order.to)!=='undefined') setCookie('order_to', order.to.address.location);
        createOrderFunc(createOrder);

    });


    //Get order info

    var getOrderInfoInterval, getOrderInfo;
    
    $('body').on('getOrderInfo', function () {
        config.goToStep3();
        getOrderInfo = {
            name: 'getOrderInfo',
            params:{
                orderId: order.orderId
            },
            successCallback: function (res) {
                if(res){
                    orderInfoResult(res.status);
                    $(config.ids.orderId).html(res.id);
                    $(config.ids.orderStatus).html(res.statusLabel);
                    $(config.ids.orderPrice).html(res.cost + ' ' + res.costCurrency);
                }

            },
            errorCallback: function () {
                console.log('Ошибка получения информации по заказу');
            },
            timeout: 9000
        };

        doAjax(getOrderInfo);

        getOrderInfoInterval = setInterval(function () {
            doAjax(getOrderInfo);
        }, 10000);
    })

    function orderInfoResult (status) {
        switch (status) {
            case 'new':
                $(document).ready(function() {
                    $(config.ids.rejectOrder).show();
                });
                break;
            case 'car_assigned':
                $(document).ready(function() {
                    $(config.ids.rejectOrder).show();
                });
                break;
            case 'rejected':
                clearInterval(getOrderInfoInterval);
                clearOrder();
                $(document).ready(function() {
                    $(config.ids.rejectOrder).hide();
                });
                break;
            case 'car_at_place':
                $(document).ready(function() {
                    $(config.ids.rejectOrder).hide();
                });
                break;
            case 'executing':
                $(document).ready(function() {
                    $(config.ids.rejectOrder).hide();
                });
                break;
            case 'completed':
                clearInterval(getOrderInfoInterval);
                clearOrder();
                $(document).ready(function() {
                    $(config.ids.rejectOrder).hide();
                });
                break;
            case 'driver_busy':
                break;
            default:
                break;
        }
    }


    //If order is exited after reload
    
    $('body').on('initAfterReload', function () {
        config.goToStep3();
        order.from = {
            address: {
                location: JSON.parse(Cookies.get('order_from'))
            }
        };
        if (Cookies.get('order_to')!=undefined) {
            order.to = {
                address: {
                    location: JSON.parse(Cookies.get('order_to'))
                }
            };
        }
        order.orderId = parseInt(Cookies.get('order_id'));
        $('body').trigger('getOrderInfo').trigger('changeRoute');
    });


    //Getting order info after reload

    if (Cookies.get('order_id')!= undefined){
        $('body').trigger('initAfterReload');
    }

    //Reject order

    $('body').on('click', config.ids.rejectOrder, function (e) {
        e.preventDefault();
        if (confirm(config.rejectConfirm) && !$(this).hasClass('loading_button')){
            $(this).addClass('loading_button');
            var rejectOrder = {
                name: 'rejectOrder',
                params: {
                    orderId: order.orderId
                },
                successCallback: function (res) {
                    if(res==1){
                        clearInterval(getOrderInfoInterval);
                        doAjax(getOrderInfo);
                        orderInfoResult('rejected');
                    } else{
                        alert(config.rejectError);
                    }
                    $('.loading_button').removeClass('loading_button');
                },
                errorCallback: function () {
                    alert(config.rejectError);
                    $('.loading_button').removeClass('loading_button');
                },
                timeout: 8000
            }
            var rejectOrderFunc = doAjax.throttle(1000);
            rejectOrderFunc(rejectOrder);
        }
    })


    //New order

    $('body').on('click', config.ids.newOrder, function (e) {
        $(this).addClass('loading_button');
        e.preventDefault()
        clearOrder();
        location.reload();
    })


    //Clear order info

    function clearOrder() {
        order = {};
        Cookies.remove('order_id');
        Cookies.remove('order_from');
        Cookies.remove('order_to');
    }
};