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