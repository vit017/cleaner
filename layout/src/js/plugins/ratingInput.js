;(function ($, window, document, undefined) {

  var pluginName = 'ratingInput',
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

      thisElement.on('click', '.rating-input__item', function(e) {
        e.preventDefault();
        var $item = $(this);

        var $value = thisElement.find('.count-input__value');
        var value = $item.index() + 1;
        var id = $item.data('id');

        self.setValue(value, id);

      });

      this.setValue();

    },

    setValue: function(value, id) {

      var thisElement = this.$element;

      var $input = thisElement.find('.rating-input__value');
      var $text = thisElement.find('.rating-input__txt');


      if(value) {
        $input.val(id);
        $text.text(value);
      } else {

        value =  parseInt($input.val());
        $text.text(value);
      }

      thisElement.find('.rating-input__item').removeClass('rating-input__item_active');
      thisElement.find('.rating-input__item').slice( 0, value ).addClass('rating-input__item_active');

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