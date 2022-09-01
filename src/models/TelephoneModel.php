<?php

namespace newism\fields\models;

use craft\base\Model;
use Exception;
use JsonSerializable;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class TelephoneModel extends Model implements JsonSerializable
{
    public string $countryCode;
    public string $rawInput;
    public ?PhoneNumber $phoneNumber = null;
    private PhoneNumberUtil $phoneNumberUtil;
    
    public function attributeLabels(): array
    {
        return [
            'countryCode' => 'Country Code',
            'rawInput' => 'Raw Input',
            'phoneNumber' => 'Phone Number',
        ];
    }
    
    public function __construct($countryCode, $rawInput)
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
        $this->countryCode = $countryCode;
        $this->rawInput = $rawInput;

        try {
            $phoneNumber = $this->phoneNumberUtil->parse(
                $rawInput,
                $this->countryCode
            );
            $this->phoneNumber = $phoneNumber;
        } catch (Exception $e) {
//            $phoneNumber = new PhoneNumber();
//            $phoneNumber->setCountryCode($countryCode);
//            $phoneNumber->setRawInput($rawInput);
        }
    }
    
    public function __toString()
    {
        return $this->phoneNumber
            ? $this->phoneNumberUtil->format($this->phoneNumber, PhoneNumberFormat::INTERNATIONAL)
            : '';
    }

    public function format($format): string
    {
        $formats = [
            'E164' => PhoneNumberFormat::E164,
            'international' => PhoneNumberFormat::INTERNATIONAL,
            'national' => PhoneNumberFormat::NATIONAL,
            'RFC3966' => PhoneNumberFormat::RFC3966,
        ];

        $format = array_key_exists($format, $formats) ? $formats[$format] : $format;

        return !empty($this->phoneNumber) ? $this->phoneNumberUtil->format($this->phoneNumber, $format) : '';
    }
    
    public function jsonSerialize(): array
    {
        return [
            'countryCode' => $this->countryCode,
            'rawInput' => $this->rawInput,
            'phoneNumber' => $this->format(PhoneNumberFormat::E164),
        ];
    }
    
    public function getViewData(): array
    {
        return [
            'countryCode' => $this->countryCode,
            'rawInput' => $this->rawInput,
            'phoneNumber' => $this->phoneNumber
                ? $this->format(PhoneNumberFormat::NATIONAL)
                : null,
        ];
    }
    
    public function isValid(): bool
    {
        return ($this->phoneNumber && $this->phoneNumberUtil->isValidNumberForRegion(
                $this->phoneNumber,
                $this->countryCode
            )
        );
    }
    
    public function isEmpty(): bool
    {
        return empty(trim($this->phoneNumber));
    }
}
