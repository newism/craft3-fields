<?php

namespace newism\fields\models;

use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\AddressInterface;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Formatter\DefaultFormatter;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
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
                    'giveName',
                    'additionalName',
                    'familyName',
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

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if (isset($config['countryCode'])) {
            $countryRepository = new CountryRepository();
            $country = $countryRepository->get($config['countryCode']);
            $this->country = $country;
        }
    }

    public function __toString()
    {
        $addressFormatRepository = new AddressFormatRepository();
        $countryRepository = new CountryRepository();
        $subdivisionRepository = new SubdivisionRepository();
        $formatter = new DefaultFormatter(
            $addressFormatRepository,
            $countryRepository,
            $subdivisionRepository,
            [
                'html' => false,
                'html_attributes' => [],
            ]
        );

        return $formatter->format($this);
    }

    public $country;

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
    public $givenName;
    public $additionalName;
    public $familyName;

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
        return join(" ", [$this->getGivenName(), $this->getAdditionalName(), $this->getFamilyName()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getGivenName()
    {
        return $this->givenName;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalName()
    {
        return $this->additionalName;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }
}
