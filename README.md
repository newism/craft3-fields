**Attention**: This plugin is under active development and will change. This is a preview release.

# Address, telephone and email fields for CraftCMS 3.x

## Address

Address field powered by Google's dataset ([commerceguys/addressing](https://github.com/commerceguys/addressing)).

Features:

* Auto-complete powered by Google Place API
* Address form formatting based on country powered by [commerceguys/addressing](https://github.com/commerceguys/addressing)
* Validation / Geo-coding on submission (TODO)

![Address Demo](resources/img/address-demo.gif)

## Telephone

Telephone field powered by [Googles phone number library](https://github.com/googlei18n/libphonenumber) implemented via [giggsey/libphonenumber-for-php](https://github.com/googlei18n/libphonenumber/)

Features:

* Validate phone number based on country code
* Format phone number as E164, international, national or RFC3966
* Stores Telephone model and raw user input

![Telephone Demo](resources/img/telephone-demo.gif)!

## Email

Features:

* Email validation using Yii validation

![Email Demo](resources/img/email-demo.gif)!

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require newism/craft3-fields
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Install

Install plugin in the Craft Control Panel under Settings > Plugins.
 
## Configuration

The plugin comes with a config.php file that defines some sensible defaults. 

Copy `src/config.php` to `CRAFT_CONFIG_PATH` and rename the file to `nsmfields.php`.

## Road Map

Some things to do, and ideas for potential features:

* Address validation / Geo-coding on submission
* Display address as text in field with option to "Edit" to reduce size of field in UI
* Update commerceguys/addressing when next stable version is released

### 1.0

* Release it

### 1.1

* Add "NSM Publish Hints" field

### 1.2

* Add video field with yoututbe, vimeo previews

[![Newism Logo](../src/icon.svg)](http://newism.com.au)

Brought to you by [Leevi Graham](http://newism.com.au)
