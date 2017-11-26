;(function ($, window, document, undefined) {

  var pluginName = 'countInput',
    defaults = {
      maxValue: 10
    };

  function Plugin(element, options) {

    this.$element = $(element);
    this.settings = $.extend({}, defaults, options);
    this.init();

  }

  Plugin.prototype = {

    init: function() {

      var self = this,
          settings = this.settings,
          thisElement = this.$element;

      thisElement.on('click', '.count-input__btn', function(e) {
        e.preventDefault();
        var $btn = $(this);

        if ($btn.prop('disabled') || $btn.hasClass('count-input__btn_state_disabled')) {
          return;
        }

        var $value = thisElement.find('.count-input__value');
        var value = parseInt($value.val());

        if ($btn.hasClass('count-input__btn_type_minus')) {
          value = value - 1;
        } else {
          value = value + 1;
        }

        $value.val(value);

        self.checkValue(value);

      });

      this.checkValue();

    },

    checkValue: function(value) {

      var settings = this.settings,
          thisElement = this.$element;

      var $textNumber = thisElement.find('.count-input__content-value');
      var $textName = thisElement.find('.count-input__content-text');
      var words = $textName.data('word-forms').split('/');

      if(value) {
        btnsStateCheck(value);
      } else {
        value =  parseInt(thisElement.find('.count-input__value').val());
        btnsStateCheck(value);
      }

      $textNumber.text(value);
      thisElement.find('.count-input__content-text').text(wordFormat(words, value));

      function wordFormat(words,count) {
        count=count%100;
        if (count>=20) {
          count=count%10;
        }
        if (count==1) {
          return words[0];
        }
        if (count>1 && count<5) {
          return words[1];
        }
        return words[2];
      }

      function btnsStateCheck(val) {

        var btnMinusDisabled = false,
            btnPlusDisabled = true;

        if (val < 2) {
          btnMinusDisabled = true;
          btnPlusDisabled = false;
        } else if (val > (settings.maxValue - 1)) {
          btnMinusDisabled = false;
          btnPlusDisabled = true;
        } else if (val > 1 && val < settings.maxValue) {
          btnMinusDisabled = false;
          btnPlusDisabled = false;
        }

        if (btnMinusDisabled) {
          thisElement.find('.count-input__btn_type_minus').addClass('count-input__btn_state_disabled')
        } else {
          thisElement.find('.count-input__btn_type_minus').removeClass('count-input__btn_state_disabled')
        }
        if (btnPlusDisabled) {
          thisElement.find('.count-input__btn_type_plus').addClass('count-input__btn_state_disabled')
        } else {
          thisElement.find('.count-input__btn_type_plus').removeClass('count-input__btn_state_disabled')
        }
      }

    }
  };

  $.fn[ pluginName ] = function ( options ) {
    return this.each(function() {
      if ( !$.data( this, "plugin_" + pluginName ) ) {
        $.data( this, "plugin_" + pluginName, new Plugin( this, options ) );
      }
    });
  };

})( jQuery, window, document );