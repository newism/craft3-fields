<?php

namespace newism\fields\fields;

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\AddressFormat;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\AddressFormat\AdministrativeAreaType;
use CommerceGuys\Addressing\AddressFormat\DependentLocalityType;
use CommerceGuys\Addressing\AddressFormat\LocalityType;
use CommerceGuys\Addressing\AddressFormat\PostalCodeType;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\helpers\App;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\web\View;
use newism\fields\assetbundles\AddressField\AddressFieldAsset;
use newism\fields\models\AddressModel;
use newism\fields\NsmFields;
use newism\fields\validators\JsonValidator;
use yii\db\Schema;

/**
 * @property-read string[][] $countryOptions
 * @property-read string|array $contentColumnType
 * @property-read null|string $settingsHtml
 */
class Address extends Field implements PreviewableFieldInterface
{
    public ?string $defaultCountryCode = null;
    public bool $showAutoComplete = false;
    public bool $showMap = false;
    public string $autoCompleteConfiguration = '';
    public bool $showOrganization = false;
    public bool $showRecipient = true;
    public bool $showLatLng = true;
    public bool $showMapUrl = true;
    public bool $showPlaceData = false;

    public static function displayName(): string
    {
        return Craft::t('nsm-fields', 'Address (Newism)');
    }

    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_TEXT;
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Address/settings',
            [
                'field' => $this,
            ]
        );
    }

    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                [['defaultCountryCode'], 'string'],
                [['showAutoComplete'], 'boolean'],
                [['showMap'], 'boolean'],
                [['autoCompleteConfiguration'], 'string'],
                [['autoCompleteConfiguration'], JsonValidator::class],
                [['showOrganization'], 'boolean'],
                [['showRecipient'], 'boolean'],
                [['showLatLng'], 'boolean'],
                [['showMapUrl'], 'boolean'],
            ]
        );
    }

    public function getTableAttributeHtml(mixed $value, ElementInterface $element): string
    {
        if (!$value) {
            return '';
        }

        return "<pre>$value</pre>";
    }

    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        Craft::$app->getView()->registerAssetBundle(AddressFieldAsset::class);

        $this->renderFieldJs();

        // Get our id and namespace
        $id = Html::id($this->handle);
        $namespace = Craft::$app->getView()->getNamespace();
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        $pluginSettings = NsmFields::getInstance()->getSettings();
        $fieldSettings = $this->getSettings();
        $fieldSettings['autoCompleteConfiguration'] =
            $fieldSettings['autoCompleteConfiguration']
                ? json_decode($fieldSettings['autoCompleteConfiguration'], true, 512, )
                : [];

        // Variables to pass down to our field JavaScript to let it namespace properly
        $jsonVars = [
            'id' => $id,
            'context' => $this->context,
            'name' => $this->handle,
            'namespace' => $namespace,
            'namespacedId' => $namespacedId,
            'prefix' => Craft::$app->getView()->namespaceInputId(''),
            'fieldSettings' => $fieldSettings,
            'pluginSettings' => $pluginSettings,
        ];

        $jsonVars = Json::encode($jsonVars);

        Craft::$app->getView()->registerJs(
            "$('#{$namespacedId}-field').NsmFieldsAddress({$jsonVars});"
        );

        return $this->renderFormFields($value);
    }

    public function renderFormFields(AddressModel $value = null): string
    {
        // Get our id and namespace
        $id = Html::id($this->handle);
        $namespace =  Craft::$app->getView()->getNamespace();
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        $fieldSettings = $this->getSettings();
        $pluginSettings = NsmFields::getInstance()->getSettings();

        $countryCode = $value ? $value->getCountryCode() : $this->defaultCountryCode;
        $countryCodeField = Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Address/input/countryCode',
            [
                'name' => $this->handle,
                'value' => $countryCode,
                'field' => $this,
                'id' => $id,
                'namespace' => $namespace,
                'namespacedId' => $namespacedId,
                'settings' => $fieldSettings,
                'countryOptions' => $this->getCountryOptions(),
            ]
        );

        $addressFields = $this->renderAddressFields($value);

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Address/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'id' => $id,
                'namespace' => $namespace,
                'namespacedId' => $namespacedId,
                'fieldSettings' => $fieldSettings,
                'pluginSettings' => $pluginSettings,
                'addressFields' => $addressFields,
                'countryCode' => $countryCode,
                'countryCodeField' => $countryCodeField,
            ]
        );
    }

    private function renderFieldJs()
    {
        $pluginSettings = NsmFields::getInstance()->getSettings();

        // Note: refer to src/assetbundles/addressfield/dist/js/Address.js for the callback name
        $googleApiKey = App::parseEnv($pluginSettings->googleApiKey);
        $mapUrl = sprintf(
            'https://maps.googleapis.com/maps/api/js?key=%s&libraries=places&callback=googleMapsPlacesApiLoadedCallback',
            $googleApiKey
        );
        Craft::$app->view->registerJsFile(
            $mapUrl,
            [
                'async' => '',
                'defer' => '',
                'position' => View::POS_END,
                'depends' => [AddressFieldAsset::class],
            ],
			'googleMapsPlaces'
        );
    }

    private function renderAddressFields($value): string
    {
        // Get our id and namespace
        $id = Html::id($this->handle);
        $namespace =  Craft::$app->getView()->getNamespace();
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        $countryCode = $value ? $value->getCountryCode() : $this->defaultCountryCode;

        if (empty($countryCode)) {
            return '';
        }

        $fieldSettings = $this->getSettings();

        $addressFormatRepository = new AddressFormatRepository();
        $addressFormat = $addressFormatRepository->get($countryCode);
        $fieldLabels = $this->getFieldLabels($addressFormat);
        $formatTemplate = $addressFormat->getFormat();

        if (!$fieldSettings['showRecipient']) {
            $formatTemplate = str_replace('%givenName', '', $formatTemplate);
            $formatTemplate = str_replace('%additionalName', '', $formatTemplate);
            $formatTemplate = str_replace('%familyName', '', $formatTemplate);
            $formatTemplate = str_replace('%recipient', '', $formatTemplate);
        }

        if (!$fieldSettings['showOrganization']) {
            $formatTemplate = str_replace('%organization', '', $formatTemplate);
        }

        $formatTemplate = preg_replace(
            '/(?:(?:\r\n|\r|\n)\s*){2}/s',
            "\n",
            $formatTemplate
        );
        $formatTemplate = str_replace(',', '', $formatTemplate);

        $formatRows = array_filter(
            array_map('trim', explode("\n", $formatTemplate))
        );

        $addressFields = '';

        foreach ($formatRows as $formatRow) {
            preg_match_all('/%([a-zA-Z0-9]+)/i', $formatRow, $matches);
            $className = implode('-', $matches[1]);

            $addressFields .= '<div class="flex nsmFields-fieldRow nsmFields-fieldRow-' . $className . '">';
            foreach ($matches[1] as $match) {

                $subdivisionOptions = ($match === 'administrativeArea')
                    ? $this->getSubdivisionOptions($countryCode)
                    : [];

                $formatRow = str_replace(
                    '%' . $match,
                    Craft::$app->getView()->renderTemplate(
                        'nsm-fields/_components/fieldtypes/Address/input/' . $match,
                        [
                            'name' => $this->handle,
                            'value' => $value,
                            'field' => $this,
                            'id' => $id,
                            'namespace' => $namespace,
                            'namespacedId' => $namespacedId,
                            'settings' => $fieldSettings,
                            'addressFormat' => $addressFormat,
                            'fieldLabels' => $fieldLabels,
                            'subdivisionOptions' => $subdivisionOptions,
                            'template' => $match,
                        ]
                    ),
                    $formatRow
                );
            }
            $addressFields .= $formatRow;
            $addressFields .= '</div>';
        }

        return $addressFields;
    }

    protected function getFieldLabels(AddressFormat $addressFormat): array
    {
        // All possible subdivision labels.
        $subdivisionLabels = [
            AdministrativeAreaType::AREA => 'Area',
            AdministrativeAreaType::COUNTY => 'County',
            AdministrativeAreaType::DEPARTMENT => 'Department',
            AdministrativeAreaType::DISTRICT => 'District',
            AdministrativeAreaType::DO_SI => 'Do',
            AdministrativeAreaType::EMIRATE => 'Emirate',
            AdministrativeAreaType::ISLAND => 'Island',
            AdministrativeAreaType::OBLAST => 'Oblast',
            AdministrativeAreaType::PARISH => 'Parish',
            AdministrativeAreaType::PREFECTURE => 'Prefecture',
            AdministrativeAreaType::PROVINCE => 'Province',
            AdministrativeAreaType::STATE => 'State',
            LocalityType::CITY => 'City',
            LocalityType::DISTRICT => 'District',
            LocalityType::POST_TOWN => 'Post Town',
            DependentLocalityType::DISTRICT => 'District',
            DependentLocalityType::NEIGHBORHOOD => 'Neighborhood',
            DependentLocalityType::VILLAGE_TOWNSHIP => 'Village / Township',
            DependentLocalityType::SUBURB => 'Suburb',
            PostalCodeType::POSTAL => 'Postal Code',
            PostalCodeType::ZIP => 'ZIP code',
            PostalCodeType::PIN => 'PIN code',
        ];

        // Determine the correct administrative area label.
        $administrativeAreaType = $addressFormat->getAdministrativeAreaType();
        $administrativeAreaLabel = '';
        if (isset($subdivisionLabels[$administrativeAreaType])) {
            $administrativeAreaLabel = $subdivisionLabels[$administrativeAreaType];
        }
        // Determine the correct locality label.
        $localityType = $addressFormat->getLocalityType();
        $localityLabel = '';
        if (isset($subdivisionLabels[$localityType])) {
            $localityLabel = $subdivisionLabels[$localityType];
        }
        // Determine the correct dependent locality label.
        $dependentLocalityType = $addressFormat->getDependentLocalityType();
        $dependentLocalityLabel = '';
        if (isset($subdivisionLabels[$dependentLocalityType])) {
            $dependentLocalityLabel = $subdivisionLabels[$dependentLocalityType];
        }
        // Determine the correct postal code label.
        $postalCodeType = $addressFormat->getPostalCodeType();
        $postalCodeLabel = $subdivisionLabels[PostalCodeType::POSTAL];
        if (isset($subdivisionLabels[$postalCodeType])) {
            $postalCodeLabel = $subdivisionLabels[$postalCodeType];
        }

        // Assemble the final set of labels.
        $labels = [
            AddressField::ADMINISTRATIVE_AREA => $administrativeAreaLabel,
            AddressField::LOCALITY => $localityLabel,
            AddressField::DEPENDENT_LOCALITY => $dependentLocalityLabel,
            AddressField::ADDRESS_LINE1 => 'Street Address',
            AddressField::ADDRESS_LINE2 => 'Street Address',
            AddressField::ORGANIZATION => 'Company',
            AddressField::GIVEN_NAME => 'Given Name',
            AddressField::ADDITIONAL_NAME => 'Additional Name',
            AddressField::FAMILY_NAME => 'Family Name',
            // Google's libaddressinput provides no label for this field type,
            // Google wallet calls it "CEDEX" for every country that uses it.
            AddressField::SORTING_CODE => 'Cedex',
            AddressField::POSTAL_CODE => $postalCodeLabel,
        ];

        return $labels;
    }

    public function getCountryOptions(): array
    {
        $countryRepository = new CountryRepository();
        $countryData = $countryRepository->getList();
        $options = [['value' => '', 'label' => '']];
        foreach ($countryData as $key => $option) {
            $options[] = [
                'value' => $key,
                'label' => $option,
            ];
        }

        return $options;
    }

    private function getSubdivisionOptions($countryCode, $parentId = null): array
    {
        if (!$countryCode) {
            return [];
        }

        $subdivisionRepository = new SubdivisionRepository();
        $subdivisions = $subdivisionRepository->getAll(array_filter([$countryCode, $parentId]));
        $options = [
            [
                'value' => '',
                'label' => '',
            ],
        ];

        foreach ($subdivisions as $key => $option) {
            $options[] = [
                'value' => $option->getCode(),
                'label' => $option->getName(),
            ];
        }

        return $options;
    }

    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        /**
         * Just return value if it's already an AddressModel.
         */
        if ($value instanceof AddressModel) {
            return $value;
        }

        /**
         * Serialised value from the DB
         */
        if (is_string($value)) {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        /**
         * Array value from post or unserialized array
         */
        if (is_array($value) && !empty(array_filter($value))) {
            unset($value['country']);
            return new AddressModel($value);
        }

        return null;
    }

    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if (empty($value)) {
            return null;
        }

        $data = json_encode($value, JSON_THROW_ON_ERROR);

        if (Craft::$app->getDb()->getIsMysql()) {
            // Encode any 4-byte UTF-8 characters.
            $data = StringHelper::encodeMb4($data);
        }

        return $data;
    }

}
