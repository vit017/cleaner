/*Config file for js core of api_int*/

var config;
config = {
    ajaxClientUrl: '/api_integration/index_client.php',
    autocompleteMaxLimit: 200,  //Limit of objects in autocomplete
    autocompleteNoResultsText: 'Нет результатов', //Text of no results in autocomplete


    //map settings

    enableMap: true,
    mapZoom: 10,
    mapAIconSrc: '/bitrix/templates/taxi_yellow/i/a_icon.png',
    mapAIconSize: [24, 27],
    mapAIconAnchor: [12, 27], // point of the icon which will correspond to marker's location
    mapBIconSrc: '/bitrix/templates/taxi_yellow/i/b_icon.png',
    mapBIconSize: [24, 27],
    mapBIconAnchor: [12, 27],
    mapLineStyle: [{color: 'black', opacity: 0.15, weight: 9}, {
        color: 'white',
        opacity: 0.8,
        weight: 6
    }, {color: '#4e4e4e', opacity: 1, weight: 2}],
    mapMissingLineStyle: [{color: 'black', opacity: 0.15, weight: 7}, {
        color: 'white',
        opacity: 0.6,
        weight: 4
    }, {color: 'gray', opacity: 0.8, weight: 2, dashArray: '7,12'}],
    mapCarsUpdateTimeout: 20000,
    mapRedCarIconSrc: '/bitrix/templates/taxi_yellow/i/red_car.png',
    mapRedCarIconSize: [24, 27],
    mapRedCarIconAnchor: [12, 27],
    mapRedCarPopupAnchor: [0, -27], // point from which the popup should open relative to the iconAnchor
    mapGreenCarIconSrc: '/bitrix/templates/taxi_yellow/i/green_car.png',
    mapGreenCarIconSize: [24, 27],
    mapGreenCarIconAnchor: [12, 27],
    mapGreenCarPopupAnchor: [0, -27],


    //tel mask

    inputMask: '+ 7 (999) 999 99 99',


    //Some localization

    callCostTitle: 'Примерный расчёт:',
    callCostDist: 'км',
    callCostTime: 'мин.',
    callCostCurrency: '₽',
    rejectError: 'Не удалось отменить заказ!',
    rejectConfirm: 'Вы уверены, что хотите отменить заказ?',


    //IDs of fields and elements

    ids: {
        fromAddress: '#FIELD_ADDRESS',
        fromPorch: '#FIELD_FROM_PORCH',
        fromAutocomplete: '#from_autocomplete',
        toAddress: '#FIELD_ADDRESS_TO',
        toPorch: '#FIELD_TO_PORCH',
        toAutocomplete: '#to_autocomplete',
        phoneNumber: '#FIELD_TEL',
        comment: '#FIELD_COMM',
        tariffSelect: '#tariff_travel',
        orderTime: '#time',
        options: '#options',
        fio: '#FIELD_FIO',
        cost: '#list',
        smsCode: '#smsCode',
        goToStep2Button: '#send_order_form',
        goToStep3Button: '#go_to_step3',
        orderId: '#order_id',
        orderStatus: '#order_status',
        orderPrice: '#order_price',
        rejectOrder: '#reject_order',
        newOrder: '#new_order',
        map: 'map' // ID without '#' symbol
    },


    //Validate errors

    onValidateError: function (res) {
        $('.errortext').remove();
        $('#form_order form').before(res.errorsInfo.summaryHtml);
    },
    clearErrors: function () {
        $('.errortext').remove();
    },
    onSmsError: function (text) {
        $('.errortext').remove();
        $('.step_2').before('<div class="errortext sms_error">' + text + '</div>');
    },


    //After getting tariffs

    getTariffsSuccess: function () {
        init_select();
    },


    //Changing order`s steps

    goToStep1: function () {
        $('.order_steps').removeClass('active');
        $('.step_1').addClass('active');
    },
    goToStep2: function () {
        $('.order_steps').removeClass('active');
        $('.step_2').addClass('active');
    },
    goToStep3: function () {
        $('.order_steps').removeClass('active');
        $('.step_3').addClass('active');
    },


    //Other events

    onAutocompleteSelect: function (handle) {

    },


};

init_core(config);