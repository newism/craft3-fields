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

(function ($, Craft, window, document, undefined) {
  // We need to create a callback for the Google Maps API to use when it has loaded
  window.googleMapsPlacesApiLoaded = window.googleMapsPlacesApiLoaded || false;
  window.googleMapsPlacesApiLoadedCallback = function () {
    window.googleMapsPlacesApiLoaded = true;
    document.body.dispatchEvent(new Event('googleMapsPlacesApiLoaded'));
  };

  var pluginName = 'NsmFieldsAddress',
    defaults = {};

  // Plugin constructor
  function Plugin (element, options) {
    this.$element = $(element);
    this.options = $.extend({}, defaults, options);
    this._defaults = defaults;
    this._name = pluginName;
    this.init();
  }

  Plugin.prototype = {

    getElement: function (element) {
      return this.$element.find('#' + this.options.namespacedId + '-' + element);
    },

    init: function (id) {

      // Init country code
      this.$addressFieldsContainer = this.$element.find('.nsmFields-address-addressFieldsContainer');
      this.$autoCompleteInput = this.getElement('autoComplete');
      this.$countryCodeInput = this.getElement('countryCode');
      this.currentCountryCode = this.$countryCodeInput.val();
      this.currentCountryCodeName = this.$countryCodeInput.find('option[value="'+this.currentCountryCode+'"]').text();
      this.$countryCodeInput.on('change', $.proxy(this.refreshCountry, this));
      this.$mapContainer = this.$element.find('.nsmFields-address-map')


      // Add loading state
      this.$spinner = $('<div class="spinner hidden"/>').insertAfter(this.$countryCodeInput.parent());

      if (window.googleMapsPlacesApiLoaded) {
        this.initGoogleServices();
      } else {
        $('body').on('googleMapsPlacesApiLoaded', $.proxy(this.initGoogleServices, this));
      }
    },

    initGoogleServices: function () {
      var _this = this;
      _this.initAutocomplete();
      if(_this.$mapContainer.length){
        _this.$geocoder = new google.maps.Geocoder();
        _this.$defaultMapCenter = { lat: -32.8781082, lng: 151.7210444 };
        _this.geocode({ address: _this.currentCountryCodeName }, function(results){
          _this.$defaultMapCenter = { lat: results.geometry.location.lat(), lng: results.geometry.location.lng() };
          _this.initMap();
        });
      }
    },

    initMap: function () {
      this.$map = new google.maps.Map(this.$mapContainer[0], {
        center: { lat: parseFloat(this.getElement('latitude').val() || this.$defaultMapCenter.lat), lng: parseFloat(this.getElement('longitude').val() || this.$defaultMapCenter.lng) },
        zoom: 8
      });

      this.initMarker();
    },

    initMarker: function () {
      var _this = this;
      var pos = new google.maps.LatLng(parseFloat(this.getElement('latitude').val() || this.$defaultMapCenter.lat), parseFloat(this.getElement('longitude').val() || this.$defaultMapCenter.lng));

      this.$marker = new google.maps.Marker({
        position: pos,
        draggable: true,
        animation: google.maps.Animation.DROP,
        map: this.$map
      });


      google.maps.event.addListener(this.$marker, 'click', function(){
        _this.updateMarkerPosition();
      });

      google.maps.event.addListener(this.$marker, 'dragend', function(){
        _this.updateMarkerPosition();
      });
    },

    updateMarkerPosition: function () {
      var _this = this;
      _this.geocode({ location: _this.$marker.getPosition() }, function(results){
        _this.mapChanged(results)
      });
    },

    initAutocomplete: function () {

      if (this.$autoCompleteInput.length) {

        this.autocomplete = new google.maps.places.Autocomplete(
          this.$autoCompleteInput[0],
          this.options.fieldSettings.autoCompleteConfiguration,
        );

        google.maps.event.addDomListener(this.$autoCompleteInput[0], 'keydown', function (e) {
          if (e.keyCode === 13) {
            e.preventDefault();
          }
        });

        // When the user selects an address from the dropdown, populate the address
        // fields in the form.
        this.autocomplete.addListener('place_changed', $.proxy(this.placeChanged, this));
      }
    },

    refreshCountry: function () {
      var newCountryCode = this.$countryCodeInput.val(),
        jqXHR;

      var newCountryCodeName = this.$countryCodeInput.find('option[value="'+newCountryCode+'"]').text();

      if (!newCountryCode) {
        this.$addressFieldsContainer.hide();
      }

      if (this.currentCountryCode !== newCountryCode) {
        this.clearInputs();
        this.recenterMap(newCountryCodeName);
      }

      this.currentCountryCode = newCountryCode;
      this.$spinner.removeClass('hidden');

      jqXHR = Craft.postActionRequest(
        'nsm-fields/address/refresh-country',
        {
          'CRAFT_CSRF_TOKEN': Craft.cp.$primaryForm.find('[name="CRAFT_CSRF_TOKEN"]').val(),
          'countryCode': this.currentCountryCode,
          'namespace': this.options.namespace,
          'handle': this.options.id,
        },
        $.proxy(function (response, textStatus) {
          this.$spinner.addClass('hidden');
          if (textStatus === 'success') {
            var newHtml = $(response.html).find('.nsmFields-address-addressFieldsContainer');
            this.$addressFieldsContainer.replaceWith(newHtml);
            this.$addressFieldsContainer = newHtml;
          }
        }, this));

      return jqXHR;
    },

    recenterMap: function (newCountryCodeName) {
      var _this = this;
      if(_this.$map && _this.$marker){
        _this.geocode({ address: newCountryCodeName }, function(results){
          _this.$map.setCenter({ lat: results.geometry.location.lat(), lng: results.geometry.location.lng() })
          _this.$marker.setPosition({ lat: results.geometry.location.lat(), lng: results.geometry.location.lng() })
        });
      }
    },

    geocode: function (query, resultsFunction){
      var _this = this;
      _this.$geocoder.geocode(query, function(results, status) {
        if (status === "OK") {
          if (results[0]) {
            resultsFunction(results[0]);
          } else {
            window.alert("No Geocoding results found");
          }
        } else {
          window.alert("Geocoder failed due to: " + status);
        }
      });
    },

    clearInputs: function () {
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

    placeChanged: function () {
      var _this = this;
      var place = _this.autocomplete.getPlace();
      var normalisedPlace = this.normalisePlace(place);
      var currentCountryCode = this.getElement('countryCode').val();
      var newCountryCode = normalisedPlace.countryCode || '';

      this.$autoCompleteInput.val('');
      this.getElement('countryCode').val(newCountryCode);

      $.when((currentCountryCode === newCountryCode) || this.refreshCountry()).then(function () {
        _this.getElement('addressLine2').val((normalisedPlace.streetNumber || '') + ' ' + (normalisedPlace.route || ''));
        _this.getElement('locality').val(normalisedPlace.locality);
        _this.getElement('administrativeArea').val(normalisedPlace.administrativeAreaCode);
        _this.getElement('postalCode').val(normalisedPlace.postalCode);
        _this.getElement('placeData').val(JSON.stringify(place, null, 4));
        _this.getElement('latitude').val(normalisedPlace.latitude);
        _this.getElement('longitude').val(normalisedPlace.longitude);
        _this.getElement('mapUrl').val(normalisedPlace.mapUrl);
        if(_this.$map && _this.$marker){
          _this.$map.setCenter({ lat: normalisedPlace.latitude(), lng: normalisedPlace.longitude() })
          _this.$marker.setPosition({ lat: normalisedPlace.latitude(), lng: normalisedPlace.longitude() })
        }
      });
    },

    mapChanged: function (place) {
      var _this = this;
      var normalisedPlace = this.normalisePlace(place);

      _this.getElement('latitude').val(normalisedPlace.latitude);
      _this.getElement('longitude').val(normalisedPlace.longitude);
    },

    normalisePlace: function (result) {
      var normalised = {};

      normalised.latitude = result.geometry.location.lat;
      normalised.longitude = result.geometry.location.lng;
      normalised.mapUrl = result.url || '';

      for (var i in result.address_components) {
        for (var j in result.address_components[i].types) {
          switch (result.address_components[i].types[j]) {
          case 'street_number':
            normalised.streetNumber = result.address_components[i].long_name;
            break;
          case 'route':
            normalised.route = result.address_components[i].short_name;
            break;
          case 'locality':
            normalised.locality = result.address_components[i].long_name;
            break;
          case 'administrative_area_level_1':
            normalised.administrativeArea = result.address_components[i].long_name;
            normalised.administrativeAreaCode = result.address_components[i].short_name;
            // Normalise Japan
            normalised.administrativeAreaCode = normalised.administrativeAreaCode.replace(' Prefecture', '');
            normalised.administrativeAreaCode = normalised.administrativeAreaCode.replace(' Parish', '');
            break;
          case 'postal_code':
            normalised.postalCode = result.address_components[i].long_name;
            break;
          case 'country':
            normalised.country = result.address_components[i].long_name;
            normalised.countryCode = result.address_components[i].short_name;
            break;
          }
        }
      }

      return normalised;
    },
  };

  // A really lightweight plugin wrapper around the constructor,
  // preventing against multiple instantiations
  $.fn[pluginName] = function (options) {
    return this.each(function () {
      if (!$.data(this, 'plugin_' + pluginName)) {
        $.data(this, 'plugin_' + pluginName,
          new Plugin(this, options));
      }
    });
  };

})(jQuery, Craft, window, document);
