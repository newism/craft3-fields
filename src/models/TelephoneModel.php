<?php

namespace newism\fields\models;

use Craft;
use craft\base\Model;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class TelephoneModel extends Model implements \JsonSerializable
{
    /** @var string  */
    public $countryCode;

    /** @var string  */
    public $rawInput;

    /** @var PhoneNumber  */
    public $phoneNumber;

    /** @var PhoneNumberUtil  */
    private $phoneNumberUtil;

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'countryCode' => 'Country Code',
            'rawInput' => 'Raw Input',
            'phoneNumber' => 'Phone Number',
        ];
    }

    /**
     * TelephoneModel constructor.
     * @param string $countryCode
     * @param string $rawInput
     */
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
        } catch (\Exception $e) {
//            $phoneNumber = new PhoneNumber();
//            $phoneNumber->setCountryCode($countryCode);
//            $phoneNumber->setRawInput($rawInput);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if(empty($this->phoneNumber)) {
            return "";
        }

        return $this->phoneNumberUtil->format($this->phoneNumber, PhoneNumberFormat::INTERNATIONAL);
    }

    /**
     * @param $format
     * @return string
     */
    public function format($format): string {

        $formats = [
            'E164' => PhoneNumberFormat::E164,
            'international' => PhoneNumberFormat::INTERNATIONAL,
            'national' => PhoneNumberFormat::NATIONAL,
            'RFC3966' => PhoneNumberFormat::RFC3966,
        ];

        $format = array_key_exists($format, $formats) ? $formats[$format] : $format;

        return $this->phoneNumberUtil->format($this->phoneNumber, $format);
    }

    /**
     * json serialize countryCode and phoneNumber for DB storage
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'countryCode' => $this->countryCode,
            'rawInput' => $this->rawInput,
            'phoneNumber' => $this->format(PhoneNumberFormat::E164)
        ];
    }

    /**
     * Return view data
     *
     * @return array
     */
    public function getViewData(): array {
        return [
            'countryCode' => $this->countryCode,
            'rawInput' => $this->rawInput,
            'phoneNumber' => $this->phoneNumber
                ? $this->format(PhoneNumberFormat::NATIONAL)
                : null
        ];
    }

    /**
     * Is valid?
     *
     * @return bool
     */
    public function isValid() {
        return (boolean) ($this->phoneNumber && $this->phoneNumberUtil->isValidNumberForRegion(
                $this->phoneNumber,
                $this->countryCode
            )
        );
    }
}
