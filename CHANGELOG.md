# NSM Fields Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 0.0.17 - 2020.07.22
### Added
 - New setting to hide Address Place Data
### Fixes
 - Disabling AutoComplete breaks things! #43
 
## 0.0.16 - 2020.07.21
### Fixes
 - Fixes Show Map Url field option
 - Fixes Person Name field spacing - Added flex wrap and margins
 
## 0.0.15 - 2020.05.19
### Fixes
 - Fixes Neo / Matrix integration
 
## 0.0.14 - 2019.01.15
### Fixes
 - Fixes #30: Default Country Code doesn't do anything.

## 0.0.13 - 2019.01.15
### Changed
 - Fixes #35: latitude & longitude not being saved. Latitude and longitude are now hidden with CSS. The values are saved in the DB. Map Url is now hidden with CSS. The values are saved in the DB.

## 0.0.12 - 2019.01.15
### Changed
- Address uses a formatted string for search keywords
- Embed uses the embed title for search keywords
### Fixed
- Fixes #39 Adresse cannot contain emoji - Address and Embed now use `StringHelper::encodeMb4` to serialize their DB value 

## 0.0.11 - 2018.08.21
### Added
- `{{ entry.address.country }}` object to model
### Changed
- Craft 3.0.20 compatibility
- Updated `commerceguys/addressing` to 1.0.1 which should add more stability moving forward
- Removed `symfony/intl` in favour of `commerceguys/addressing` countryRepository

## 0.0.10 - 2018.1.03
### Fixed
- Fixes #22 Encode all emoticons in shortcodes using LitEmoji
- Craft 3 RC-13 compatibility 

## 0.0.8 - 2018.6.02
### Fixed
- Fixes #18 Telephone field causes a PHP error when entry is saved 

## 0.0.7 - 2017.12.08
### Fixed
- Fixes #15 Cannot read property 'serialize' of undefined - Address.js:88 

## 0.0.6 - 2017.12.04
### Changed
- Added LICENSE.md

## 0.0.5 - 2017.12.03
### Changed
- Craft 3 beta 36 compatibility
- Address Field Updates:
    * Always show country selector
    * Set the default country code for new entries

## 0.0.4 - 2017.06.31
### Added
- Added person name field
- Added gender field
### Changed
- Craft 3 beta 23 compatibility
- All fields store `null` in the DB if empty. Templates also receive `null` if the value is empty


## 0.0.3
### Added
- Added embed field
### Changed
- Craft 3 beta 20 compatibility
- Updated plugin handle to nsm-fields from nsmFields

## 0.0.2 - 2017.06.12
### Fixed
- Hooked up autocomplete field settings with plugin

## 0.0.1 - 2017.06.04
### Added
- Initial release
