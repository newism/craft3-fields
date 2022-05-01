# A collection of custom Fields CraftCMS

## Installation

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this plugin:

```console
$ composer require newism/craft-fields && ./craft plugin/install nsm-fields
```

## Configuration

* If you're using Address Autocomplete you'll need an [api key](https://developers.google.com/places/web-service/get-api-key).
* If you're embedding Instagram you'll need an [api key](https://developers.facebook.com/docs/instagram/oembed).
* If you're embedding Facebook you'll need an [api key](https://developers.facebook.com/docs/plugins/oembed).

Add the api keys to your environment and set in the plugin settings. See: https://craftcms.com/docs/3.x/config/#environmental-configuration

Alternatively…

Copy `src/config.php` to `CRAFT_CONFIG_PATH` and rename the file to `nsm-fields.php`.

---

# Fields

## Address

Address field powered by Google's dataset ([commerceguys/addressing](https://github.com/commerceguys/addressing)).

Features:

* Auto-complete powered by Google Place API
* Map powered by Google Maps Javascript API and Google Maps Geocoding API
* Address form formatting based on country powered by [commerceguys/addressing](https://github.com/commerceguys/addressing)
* Validation / Geo-coding on submission (TODO)

Template Tags:

The [`normalizeValue`](./src/fields/Address.php#L504) method always returns an [`AddressModel`](./src/models/AddressModel.php). All public properties are available:

Given `entry.address` is your field…

```
See: https://github.com/commerceguys/addressing/blob/master/src/AddressInterface.php
{{ entry.address.countryCode }}
{{ entry.address.administrativeArea }}
{{ entry.address.locality }}
{{ entry.address.dependentLocality }}
{{ entry.address.postalCode }}
{{ entry.address.sortingCode }}
{{ entry.address.addressLine1 }}
{{ entry.address.addressLine2 }}
{{ entry.address.organization }}
{{ entry.address.recipient }}
{{ entry.address.locale }}

See: https://github.com/commerceguys/addressing/blob/master/src/Country/Country.php
{{ entry.address.country.countryCode }}
{{ entry.address.country.name }}
{{ entry.address.country.threeLetterCode }}
{{ entry.address.country.numericCode }}
{{ entry.address.country.currencyCode }}
{{ entry.address.country.locale }}

{{ entry.address.placeData }}
{{ entry.address.latitude }}
{{ entry.address.longitude }}
{{ entry.address.mapUrl }}

```

![Address Demo](resources/img/address-demo.gif)

## Telephone

Telephone field powered by [Googles phone number library](https://github.com/googlei18n/libphonenumber) implemented via [giggsey/libphonenumber-for-php](https://github.com/giggsey/libphonenumber-for-php)

Features:

* Validate phone number based on country code
* Format phone number as E164, international, national or RFC3966
* Stores Telephone model and raw user input

Template Tags:

Given `entry.telephone` is your field…

`{{ entry.telephone }}` outputs the phone number in international format.

You can also choose a specific format:

```
{{ entry.telephone.format('E164') }}
{{ entry.telephone.format('international') }}
{{ entry.telephone.format('national') }}
{{ entry.telephone.format('RFC3966') }}
```

The raw input from the user is also available:

```{{ entry.telephone.rawInput }}```

![Telephone Demo](resources/img/telephone-demo.gif)

## Email

Features:

* Email validation using Yii validation

![Email Demo](resources/img/email-demo.gif)

## Embed

Features:

* Embed social media posts / media directly from a URL
* Live preview

Template Tags:

Given `entry.embedField` is your field…

* `{{ entry.embedField.embedData }}` outputs the returned embed object.
* `{{ entry.embedField.embedData.code | raw }}` outputs the returned embed javascript code.

Additional data:

    {{ entry.embedField.embedData.authorName }} // The resource author
    {{ entry.embedField.embedData.authorUrl }} // The author url
    {{ entry.embedField.embedData.cms }} // The cms used
    {{ entry.embedField.embedData.code.html }} // The code to embed the image, video, etc
    {{ entry.embedField.embedData.code.width }} // The exact width of the embed code (if exists)
    {{ entry.embedField.embedData.code.height }} // The exact height of the embed code (if exists)
    {{ entry.embedField.embedData.code.aspectRatio }} // The aspect ratio (width/height)
    {{ entry.embedField.embedData.description }} //The page description
    {{ entry.embedField.embedData.favicon }} // The favicon of the site (an .ico file or a png with up to 32x32px)
    {{ entry.embedField.embedData.feeds }} // The RSS/Atom feeds
    {{ entry.embedField.embedData.icon }} // The big icon of the site
    {{ entry.embedField.embedData.image }} // The thumbnail or main image
    {{ entry.embedField.embedData.keywords }} // The page keywords
    {{ entry.embedField.embedData.language }} // The language of the page
    {{ entry.embedField.embedData.languages }} // The alternative languages
    {{ entry.embedField.embedData.license }} // The license url of the resource
    {{ entry.embedField.embedData.providerName }} // The provider name of the page (Youtube, Twitter, Instagram, etc)
    {{ entry.embedField.embedData.providerUrl }} // The provider url
    {{ entry.embedField.embedData.publishedTime }} // The published time of the resource
    {{ entry.embedField.embedData.redirect }}
    {{ entry.embedField.embedData.title }} //The page title
    {{ entry.embedField.embedData.url }} //The canonical url
    {{ entry.embedField.embedData.ombed }} // oembed data 
    {{ entry.embedField.embedData.linkedData }} // json-LD data

Note: embed data saved with v1 saved the html to `{{entry.embedField.embedData.code}}`. In v2 `{{ entry.embedField.embedData.code }}` changed to an array. Your templates will need to account for both versions… something like:

```
{{ (entry.embedField.embedData.code.html|default ?: entry.embedField.embedData.code|default) | raw }}
```

![Embed Demo](resources/img/embed-demo.gif)

## Person Name

Person name field with:

* Honorific Prefix
* Given Names
* Additional Names
* Family Names
* Honorific Suffix

![Person Name Demo](resources/img/person-name-field.png)

## Gender

Non-binary gender field with:

* Sex
* Identity

![Gender](resources/img/gender-field.png)

## Road Map

Some things to do, and ideas for potential features:

* Split out each field into it's own plugin. Keep this plugin as a single composer file which pulls all fields in
* Address validation / Geo-coding on submission
* Display address as text in field with option to "Edit" to reduce size of field in UI
* Update commerceguys/addressing when next stable version is released

## Credits

Brought to you by [Newism](http://newism.com.au)

[<img src="http://newism.com.au/uploads/content/newism-logo.png" width="150px" />](http://newism.com.au/)
