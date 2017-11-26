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
          me.abort(); // Сбрасываем обработку предыдущего ответа
          var form = $(this).closest('form');
          me.sendForm(form);
      })

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
        $basket.find('.count-input').countInput();
        // order time select
        $basket.find('.js-custom-select').selectize({
          onChange: function(value) {
            if (!value.length) return;
            me.abort();
            var form = $('.js-basket-form');
            me.sendForm(form);
          }
        });

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

function show(){
    $.ajax({
        url: "/ajax/update.php",
        cache: false,
        dataType: "json",
        success: function(result){
            if(result.STATUS == 'OK'){
                $('.js-show-more').show();
                $(result.HTML).insertAfter('.js-show-more');
                $('.aside-nav__item-link:first').text('Новые ('+result.AVAIL+')');
            }
        }
    });
}


$(function () {
    $('body').on('change','.additional-control__item input:checkbox' , function () {
        if ($(this).is(':checked')) {
            $(this).closest('.additional-control__item').addClass('checked');
        } else {
            $(this).closest('.additional-control__item').removeClass('checked');
        }

    })

});
$(document).ready(function() {
    setInterval('show()', 120000);

    $('body').on('click', '.js-show-more', function(){
        $(this).slideUp();
        $('.order-detail__content.fresh-orders').removeClass('fresh-orders');

    })

    basketSendFormAjax.init();
    sendOrder = false;
    $('body').on('click.smsResend', '.js-sms-resend', function (e) {
        e.preventDefault();
        var $confirmBlock = $(this).parents('.sms-confirm');
        $confirmBlock.find('.sms-confirm__resend-input').prop('checked', true);
        $(this).closest('form').submit();
    });

    $('body').on('click', '.js-cleaner-action', function(){
        if ( !sendOrder ){
            sendOrder = true;
            var action = $(this).data('action');
            var order = $(this).data('order');

            var cleaner = $(this).data('cleaner');
            var propid = false;
            if ( $(this).data('propid') > 0 )
                propid = $(this).data('propid');
            $.ajax({
                url: '/ajax/cleaner.php',
                dataType: "json",
                data: {order:order, action:action, cleaner:cleaner, propId:propid},
                success: function(data){
                    $("body").prepend(data.HTML);
                    setOrdersNum(data.COUNT);
                },
                complete: function(){
                        sendOrder = false;
                    }
            });
        }
    })

    function setOrdersNum(count){
        var text = 'Мои уборки';
        if ( count > 0 ){
            text = text + '('+count+')';
        }
        $('.js-countOrders').find('.aside-nav__item-link').text(text);


    }

    var $validateForm = $('.js-form-validate');
        $validateForm.parsley({
        'errorsWrapper': '<span class="input-error"></span>',
        'errorTemplate': '<span class="input-error__item"></span>',
        classHandler: function (el) {
          if ($(el.$element[0]).hasClass('input-txt__field')) {
            return $(el.$element[0]).parents('.input-txt');
          }
        }
    });

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

});