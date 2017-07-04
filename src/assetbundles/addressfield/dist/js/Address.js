/**
 * NSM Fields plugin for Craft CMS
 *
 * Address Field JS
 *
 * @author    Leevi Graham
 * @copyright Copyright (c) 2017 Leevi Graham
 * @link      http://newism.com.au
 * @package   NsmFields
 * @since     1.0.0NsmFieldsAddress
 */

window.googleMapsPlacesApiLoaded = false;

function googleMapsPlacesApiLoaded() {
    window.googleMapsPlacesApiLoaded = true;
    $('body').trigger('googleMapsPlacesApiLoaded');
}

;(function ($, Craft, window, document, undefined) {

    var pluginName = "NsmFieldsAddress",
        defaults = {};

    // Plugin constructor
    function Plugin(element, options) {
        this.$element = $(element);
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this.init();

        if (window.googleMapsPlacesApiLoaded) {
            this.initAutocomplete();
        }
    }

    Plugin.prototype = {

        init: function (id) {
            this.id = id;
            this.$autoCompleteInput = this.getElement('autoComplete');
            this.initCountryCode();
            $('body').on('googleMapsPlacesApiLoaded', $.proxy(this.initAutocomplete, this));
        },

        getElement: function (name) {
            return this.$element.find(this.getElementSelector(name));
        },

        getElementSelector: function (name) {
            return '#fields-' + this.options.name + '-' + name;
        },

        initCountryCode: function () {

            var _this = this;

            this.fieldSelector = '#fields-' + _this.options.name + '-field';
            this.$field = $(this.fieldSelector);

            this.addressFieldsContainerSelector = this.fieldSelector + ' .nsmFields-address-addressFieldsContainer';
            this.$addressFieldsContainer = $(this.addressFieldsContainerSelector);

            this.$autoCompleteInput = this.getElement('autoComplete');
            this.$countryCodeInput = this.getElement('countryCode');
            this.currentCountryCode = this.$countryCodeInput.val();

            this.$spinner = $('<div class="spinner hidden"/>').insertAfter(this.$countryCodeInput.parent());
            this.$countryCodeInput.on('change', $.proxy(this.refreshCountry, this));
        },

        refreshCountry: function () {
            var _this = this,
                newCountryCode = this.$countryCodeInput.val(),
                jqXHR;

            debugger;
            if(!newCountryCode) {
                this.$addressFieldsContainer.hide();
            }

            if(this.currentCountryCode !== newCountryCode) {
                this.clearInputs();
            }

            this.currentCountryCode = newCountryCode;

            _this.$spinner.removeClass('hidden');

            jqXHR = Craft.postActionRequest('entries/switch-entry-type', Craft.cp.$container.serialize(), $.proxy(function (response, textStatus) {
                var newHtml;
                this.$spinner.addClass('hidden');
                if (textStatus === 'success') {
                    newHtml = $(response.paneHtml).find(this.addressFieldsContainerSelector);
                    newHtml.toggle(!! this.$countryCodeInput.val());
                    this.$addressFieldsContainer.replaceWith(newHtml);
                    this.$addressFieldsContainer = newHtml;
                    Craft.initUiElements(this.$addressFieldsContainer);
                }
            }, _this));

            return jqXHR;
        },

        clearInputs: function() {
            this.getElement('addressLine1').val('');
            this.getElement('addressLine2').val('');
            this.getElement('locality').val('');
            this.getElement('administrativeArea').val('');
            this.getElement('postalCode').val('');
            this.getElement('placeData').val('');
            this.getElement('latitude').val('');
            this.getElement('longitude').val('');
            this.getElement('mapUrl').val('');
        },

        initAutocomplete: function () {
            // Create the autocomplete object, restricting the search to geographical
            // location types.
            this.autocomplete = new google.maps.places.Autocomplete(
                this.$autoCompleteInput[0],
                this.options.fieldSettings.autoCompleteConfiguration
            );

            google.maps.event.addDomListener(this.$autoCompleteInput[0], 'keydown', function(e) {
                if (e.keyCode === 13 && $('.pac-container:visible').length) {
                    e.preventDefault();
                }
            });

            // When the user selects an address from the dropdown, populate the address
            // fields in the form.
            this.autocomplete.addListener('place_changed', $.proxy(this.placeChanged, this));
        },

        placeChanged: function () {
            var _this = this;
            var place = _this.autocomplete.getPlace();
            var normalisedPlace = this.normalisePlace(place);
            var currentCountryCode = _this.getElement('countryCode').val();
            var newCountryCode = normalisedPlace.countryCode || '';

            _this.$autoCompleteInput.val('');
            _this.getElement('countryCode').val(newCountryCode);

            $.when((currentCountryCode === newCountryCode) || this.refreshCountry()).then(function () {
                _this.getElement('addressLine2').val((normalisedPlace.streetNumber || '') + ' ' + (normalisedPlace.route || ''));
                _this.getElement('locality').val(normalisedPlace.locality);
                _this.getElement('administrativeArea').val(normalisedPlace.administrativeAreaCode);
                _this.getElement('postalCode').val(normalisedPlace.postalCode);
                _this.getElement('placeData').val(JSON.stringify(place, null, 4));
                _this.getElement('latitude').val(normalisedPlace.latitude);
                _this.getElement('longitude').val(normalisedPlace.longitude);
                _this.getElement('mapUrl').val(normalisedPlace.mapUrl);
            });
        },

        normalisePlace: function (result) {
            var normalised = {};

            normalised.latitude = result.geometry.location.lat;
            normalised.longitude = result.geometry.location.lng;
            normalised.mapUrl = result.url || '';

            for (var i in result.address_components) {
                for (var j in result.address_components[i].types) {
                    switch (result.address_components[i].types[j]) {
                        case "street_number":
                            normalised.streetNumber = result.address_components[i].long_name;
                            break;
                        case "route":
                            normalised.route = result.address_components[i].short_name;
                            break;
                        case "locality":
                            normalised.locality = result.address_components[i].long_name;
                            break;
                        case "administrative_area_level_1":
                            normalised.administrativeArea = result.address_components[i].long_name;
                            normalised.administrativeAreaCode = result.address_components[i].short_name;
                            // Normalise Japan
                            normalised.administrativeAreaCode = normalised.administrativeAreaCode.replace(' Prefecture', '');
                            normalised.administrativeAreaCode = normalised.administrativeAreaCode.replace(' Parish', '');
                            break;
                        case "postal_code":
                            normalised.postalCode = result.address_components[i].long_name;
                            break;
                        case "country":
                            normalised.country = result.address_components[i].long_name;
                            normalised.countryCode = result.address_components[i].short_name;
                            break;
                    }
                }
            }


            return normalised;
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
