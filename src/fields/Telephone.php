<?php

namespace newism\fields\fields;

use CommerceGuys\Addressing\Country\CountryRepository;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Html;
use libphonenumber\PhoneNumberUtil;
use newism\fields\models\TelephoneModel;

/**
 * @property-read array $countryOptions
 * @property-read array $elementValidationRules
 * @property-read null|string $settingsHtml
 */
class Telephone extends Field implements PreviewableFieldInterface
{
    protected PhoneNumberUtil $phoneNumberUtil;

    public string $defaultCountryCode = 'US';
    
    public static function displayName(): string
    {
        return Craft::t('nsm-fields', 'Telephone (Newism)');
    }
    
    public function init(): void
    {
        parent::init();
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
    }
    
    public function rules(): array
    {
        $rules = parent::rules();
        $rules = array_merge(
            $rules,
            [
                [['defaultCountryCode'], 'string'],
                [['defaultCountryCode'], 'default', 'value' => 'US'],
            ]
        );

        return $rules;
    }
    
    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        // Just return value if it's already an TelephoneModel.
        if ($value instanceof TelephoneModel) {
            return $value;
        }

        // Serialised value from the DB
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        // Array value from post or unserialized array
        if (is_array($value) && !empty(array_filter($value))) {
            return new TelephoneModel(
                strlen($value['countryCode']) ? $value['countryCode'] : $this->defaultCountryCode,
                $value['rawInput']
            );
        }

        return null;
    }
    
    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if ($value instanceof TelephoneModel && !$value->phoneNumber) {
            return null;
        }

        return parent::serializeValue($value, $element);
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Telephone/settings',
            [
                'field' => $this,
                'countryOptions' => $this->getCountryOptions(),
            ]
        );
    }
    
    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string {

        // Get our id and namespace
        $id = Html::id($this->handle);
        $namespace =  Craft::$app->getView()->getNamespace();
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Telephone/input',
            [
                'name' => $this->handle,
                'viewData' => $value,
                'field' => $this,
                'id' => $id,
                'namespace' => $namespace,
                'namespacedId' => $namespacedId,
                'countryOptions' => $this->getCountryOptions(),
                'settings' => $this->getSettings(),
            ]
        );
    }
    
    private function getCountryOptions(): array
    {
        // Removing the null default value as this causes more problems
        // We already have a default country code defined in the settings and trying to manage a field that can have
        // the first Telephone object as an optional parameter is difficult.
//        $countries = [['value' => '', 'label' => '']];

        $countryRepository = new CountryRepository();
        $countryData = $countryRepository->getList();
        $countries = [];

        foreach ($countryData as $key => $option) {
            $regionCode = $this->phoneNumberUtil->getCountryCodeForRegion(
                $key
            );
            $countries[] = [
                'value' => $key,
                'label' => $option.($regionCode ? ' +'.$regionCode : ''),
            ];
        }

        return $countries;
    }

    public function getElementValidationRules(): array
    {
        return array_merge(parent::getElementValidationRules(), [
            'validatePhoneNumber'
        ]);
    }
    
    public function isValueEmpty(mixed $value, ElementInterface $element = null): bool
    {
        return ($value instanceof TelephoneModel)
            ? $value->isEmpty()
            : parent::isValueEmpty($value, $element);
    }
    
    public function validatePhoneNumber( ElementInterface $element, array $params = null ) {

        $value = $element->getFieldValue($this->handle);
        $valid = $value->isValid();

        if (!$valid) {
            $element->addError(
                $this->handle,
                Craft::t(
                    'nsm-fields',
                    'The string supplied did not seem to be a phone number or didn\'t match the expected format for the country.'
                )
            );
        }
    }
    
    public function getTableAttributeHtml(mixed $value, ElementInterface $element): string
    {
        return ($value instanceof TelephoneModel && !$value->isEmpty())
            ? (string) $value->format("E164")
            : '';
    }
}
