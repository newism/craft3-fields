<?php

namespace newism\fields\models;

use CommerceGuys\Addressing\Model\AddressInterface;
use yii\base\Model;

class AddressModel extends Model implements AddressInterface
{
    public function rules()
    {
        return [
            [
                [
                    'countryCode',
                    'administrativeArea',
                    'locality',
                    'dependentLocality',
                    'postalCode',
                    'sortingCode',
                    'addressLine1',
                    'addressLine2',
                    'organization',
                    'recipient',
                    'locale',

                    'placeData',
                    'latitude',
                    'longitude',
                    'mapUrl',
                ],
                'safe',
            ],
        ];
    }

    /**
     * Google Place Data
     *
     * @var string
     */
    public $placeData;

    /**
     * @return string
     */
    public function getPlaceData(): string
    {
        return $this->placeData;
    }

    /**
     * Latitude
     *
     * @var string
     */
    public $latitude;

    /**
     * @return string
     */
    public function getLatitude(): string
    {
        return $this->latitude;
    }

    /**
     * Longitude
     *
     * @var string
     */
    public $longitude;

    /**
     * @return string
     */
    public function getLongitude(): string
    {
        return $this->longitude;
    }

    /**
     * Map Url
     *
     * @var string
     */
    public $mapUrl;

    /**
     * @return string
     */
    public function getMapUrl(): string
    {
        return $this->mapUrl;
    }

    /**
     * Copied from
     */
    /**
     * The two-letter country code.
     *
     * @var string
     */
    public $countryCode;

    /**
     * The top-level administrative subdivision of the country.
     *
     * @var string
     */
    public $administrativeArea;

    /**
     * The locality (i.e. city).
     *
     * @var string
     */
    public $locality;

    /**
     * The dependent locality (i.e. neighbourhood).
     *
     * @var string
     */
    public $dependentLocality;

    /**
     * The postal code.
     *
     * @var string
     */
    public $postalCode;

    /**
     * The sorting code.
     *
     * @var string
     */
    public $sortingCode;

    /**
     * The first line of the address block.
     *
     * @var string
     */
    public $addressLine1;

    /**
     * The second line of the address block.
     *
     * @var string
     */
    public $addressLine2;

    /**
     * The organization.
     *
     * @var string
     */
    public $organization;

    /**
     * The recipient.
     *
     * @var string
     */
    public $recipient;

    /**
     * The locale.
     *
     * @var string
     */
    public $locale;


    /**
     * {@inheritdoc}
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdministrativeArea()
    {
        return $this->administrativeArea;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependentLocality()
    {
        return $this->dependentLocality;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortingCode()
    {
        return $this->sortingCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
