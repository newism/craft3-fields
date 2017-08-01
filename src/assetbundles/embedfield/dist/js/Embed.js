/**
 * NSM Fields plugin for Craft CMS
 *
 * Embed Field JS
 *
 * @author    Leevi Graham
 * @copyright Copyright (c) 2017 Leevi Graham
 * @link      http://newism.com.au
 * @package   NsmFields
 * @since     1.0.0NsmFieldsAddress
 */
;(function ($, Craft, window, document, undefined) {

    var pluginName = "NsmFieldsEmbed",
        defaults = {};

    // Plugin constructor
    function Plugin(element, options) {
        this.$element = $(element);
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }

    Plugin.prototype = {

        init: function (id) {
            this.id = id;
            this.$rawInput = this.$element.find('.nsmFields-embed-rawInput');
            this.$embedDataContainer = this.$element.find('.nsmFields-embed-embedDataContainer');
            this.$embedDataInput = this.$element.find('.nsmFields-embed-embedDataInput');
            this.$rawInput.on('change', $.proxy(this.fetchEmbedData, this));
            this.$rawInput.on('keydown', $.proxy(this.handleKeydown, this));
            this.$spinner = $('<div class="spinner hidden"/>').insertAfter(this.$rawInput.parent());
        },

        fetchEmbedData: function(event) {
            var self = this;
            var jxhr;

            event.preventDefault();

            this.$embedDataContainer.addClass('is-loading');
            this.$embedDataInput.val(null);
            this.$spinner.removeClass('hidden');

            jxhr = $.get(this.options.endpointUrl,{
                url: this.$rawInput.val(),
                name: this.options.name
            });

            jxhr.done(function(data, textStatus, jqXHR){
                console.log(data);
                self.$embedDataContainer.html(data);
            });

            jxhr.fail(function(jqXHR, textStatus, errorThrown){
                console.log(errorThrown);
            });

            jxhr.always(function(){
                Craft.initUiElements(self.$embedDataContainer);
                self.$embedDataContainer.removeClass('is-loading');
                self.$spinner.addClass('hidden');
            });

        },

        handleKeydown: function(event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                this.fetchEmbedData(event);
            }
        }
    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName,
                    new Plugin(this, options));
            }
        });
    };

})(jQuery, Craft, window, document);
