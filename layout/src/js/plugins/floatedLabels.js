;(function ($, window, document, undefined) {

  var pluginName = 'floatlabel',
    defaults = {
      labelClass                      : '',
      typeMatches                     : /text|password|email|number|search|url/
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

      if (!(thisElement.prop('tagName').toUpperCase() == 'INPUT' || thisElement.prop('tagName').toUpperCase() == 'TEXTAREA')) {
        return;
      }

      if (thisElement.prop('tagName').toUpperCase() == 'INPUT' ) {
        if (!settings.typeMatches.test(thisElement.attr('type'))) {
          return;
        }
      }

      var placeholderText = thisElement.attr('placeholder');

      if(!placeholderText || placeholderText === '') {
        placeholderText = "You forgot to add placeholder attribute!";
      }

      this.$label = thisElement.parent('label');

      this.$label.data('placeholder', placeholderText);

      thisElement.on('keyup blur change', function(e) {
        self.checkValue(e);
      });

      thisElement.on('focus', function(e) {
        self.$label.addClass('input-txt_state_focused');
      });

      thisElement.on('blur', function(e) {
        self.$label.removeClass('input-txt_state_focused');
      });

      this.checkValue();

    },

    checkValue: function(e) {

      if(e) {
        var keyCode = e.keyCode || e.which;
        var TAB_KEY = 9;
        if(keyCode === TAB_KEY) {
          return;
        }
      }

      var thisElement = this.$element,
          currentFloat = thisElement.data('float');

      if(thisElement.val() !== '') {
        thisElement.data('float', '1');
      }

      if(thisElement.val() === '') {
        thisElement.data('float', '0');
      }

      if(thisElement.data('float') === '1' && currentFloat !== '1') {
        this.showLabel();
      }

      if(thisElement.data('float') === '0' && currentFloat !== '0') {
        this.hideLabel();
      }

    },
    showLabel: function() {
      var self = this;
      self.$label.addClass('input-txt_placeholder_small');

    },

    hideLabel: function() {
      var self = this;

      self.$label.removeClass('input-txt_placeholder_small');
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