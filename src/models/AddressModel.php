<?php

namespace newism\fields\models;

use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\AddressInterface;
use CommerceGuys\Addressing\Country\Country;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Formatter\DefaultFormatter;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use yii\base\Model;

class AddressModel extends Model implements AddressInterface
{
    public ?Country $country = null;
    public string $countryCode = '';
    public string|array $placeData = '';
    public ?string $latitude = null;
    public ?string $longitude = null;
    public ?string $mapUrl = null;
    public ?string $administrativeArea = null;
    public ?string $locality = null;
    public ?string $dependentLocality = null;
    public ?string $postalCode = null;
    public ?string $sortingCode = null;
    public ?string $addressLine1 = null;
    public ?string $addressLine2 = null;
    public ?string $organization = null;
    public ?string $recipient = null;
    public ?string $givenName = null;
    public ?string $additionalName = null;
    public ?string $familyName = null;
    public ?string $locale = null;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if (isset($config['countryCode'])) {
            $countryRepository = new CountryRepository();
            $country = $countryRepository->get($config['countryCode']);
            $this->country = $country;
        }
    }

    public function rules(): array
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
                    'givenName',
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

    public function getPlaceData(): string
    {
        return $this->placeData;
    }

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }

    public function getMapUrl(): string
    {
        return $this->mapUrl;
    }

    public function getCountryCode()
    {
        return $this->countryCode;
    }

    public function getAdministrativeArea()
    {
        return $this->administrativeArea;
    }
    
    public function getLocality()
    {
        return $this->locality;
    }
    
    public function getDependentLocality()
    {
        return $this->dependentLocality;
    }
    
    public function getPostalCode()
    {
        return $this->postalCode;
    }
    
    public function getSortingCode()
    {
        return $this->sortingCode;
    }
    
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }
    
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }
    
    public function getOrganization()
    {
        return $this->organization;
    }
    
    public function getRecipient()
    {
        return join(" ", [$this->getGivenName(), $this->getAdditionalName(), $this->getFamilyName()]);
    }
    
    public function getGivenName()
    {
        return $this->givenName;
    }
    
    public function getAdditionalName()
    {
        return $this->additionalName;
    }
    
    public function getFamilyName()
    {
        return $this->familyName;
    }

    public function getLocale()
    {
        return $this->locale;
    }
}
