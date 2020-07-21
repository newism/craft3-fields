<?php
/**
 * NSM Fields plugin for Craft CMS 3.x
 *
 * Various fields for CraftCMS
 *
 * @link      http://newism.com.au
 * @copyright Copyright (c) 2017 Leevi Graham
 */

namespace newism\fields\fields;

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\AddressFormat;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\AddressFormat\AdministrativeAreaType;
use CommerceGuys\Addressing\AddressFormat\DependentLocalityType;
use CommerceGuys\Addressing\AddressFormat\LocalityType;
use CommerceGuys\Addressing\AddressFormat\PostalCodeType;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Formatter\DefaultFormatter;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\web\View;
use newism\fields\assetbundles\addressfield\AddressFieldAsset;
use newism\fields\models\AddressModel;
use newism\fields\NsmFields;
use newism\fields\validators\JsonValidator;
use yii\db\Schema;

/**
 * Address Field
 *
 * @author    Leevi Graham
 * @package   NsmFields
 * @since     1.0.0
 */
class Address extends Field implements PreviewableFieldInterface
{
    public $defaultCountryCode = false;
    public $showAutoComplete = false;
    public $autoCompleteConfiguration = '';
    public $showOrganization = false;
    public $showRecipient = true;
    public $showLatLng = true;
    public $showMapUrl = true;
    public $showPlaceData = false;

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('nsm-fields', 'NSM Address');
    }

    /**
     * Returns the column type that this field should get within the content table.
     *
     * This method will only be called if [[hasContentColumn()]] returns true.
     *
     * @return string The column type. [[\yii\db\QueryBuilder::getColumnType()]] will be called
     * to convert the give column type to the physical one. For example, `string` will be converted
     * as `varchar(255)` and `string(100)` becomes `varchar(100)`. `not null` will automatically be
     * appended as well.
     * @see \yii\db\QueryBuilder::getColumnType()
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @return string
     * @throws \yii\base\Exception
     * @throws \Twig_Error_Loader
     * @throws \RuntimeException
     */
    public function getSettingsHtml(): string
    {
        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Address/settings',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules = array_merge(
            $rules,
            [
                [['defaultCountryCode'], 'string'],
                [['showAutoComplete'], 'boolean'],
                [['autoCompleteConfiguration'], 'string'],
                [['autoCompleteConfiguration'], JsonValidator::class],
                [['showOrganization'], 'boolean'],
                [['showRecipient'], 'boolean'],
                [['showLatLng'], 'boolean'],
                [['showMapUrl'], 'boolean'],
            ]
        );

        return $rules;
    }

    /**
     * @param mixed $value
     * @param ElementInterface $element
     * @return string
     */
    public function getTableAttributeHtml(
        $value,
        ElementInterface $element
    ): string {

        if (!$value) {
            return '';
        }

        return '<pre>' . (string)$value . '</pre>';
    }

    /**
     * @param null|AddressModel $value The field’s value. This will either be the [[normalizeValue() normalized value]],
     *                                               raw POST data (i.e. if there was a validation error), or null
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @return string The input HTML.
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\Exception
     * @throws \Twig_Error_Loader
     * @throws \RuntimeException
     */
    public function getInputHtml(
        $value,
        ElementInterface $element = null
    ): string {
        // Register our asset bundle
        Craft::$app->getView()->registerAssetBundle(AddressFieldAsset::class);

        $this->renderFieldJs();

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        $pluginSettings = NsmFields::getInstance()->getSettings();
        $fieldSettings = $this->getSettings();
        $fieldSettings['autoCompleteConfiguration'] =
            $fieldSettings['autoCompleteConfiguration']
                ? Json::decode($fieldSettings['autoCompleteConfiguration'], true)
                : [];

        // Variables to pass down to our field JavaScript to let it namespace properly
        $jsonVars = [
            'id' => $id,
            'name' => $this->handle,
            'namespace' => $namespacedId,
            'prefix' => Craft::$app->getView()->namespaceInputId(''),
            'fieldSettings' => $fieldSettings,
            'pluginSettings' => $pluginSettings,
        ];

        $jsonVars = Json::encode($jsonVars);

        Craft::$app->getView()->registerJs(
            '$("#' . $namespacedId . '-field").NsmFieldsAddress(' . $jsonVars . ');'
        );

        return $this->renderFormFields($value);
    }

    /**
     * @param AddressModel $value
     * @return string
     * @throws \yii\base\Exception
     * @throws \Twig_Error_Loader
     * @throws \RuntimeException
     */
    protected function renderFormFields(AddressModel $value = null)
    {
        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        $fieldSettings = $this->getSettings();
        $pluginSettings = NsmFields::getInstance()->getSettings();

        $fieldLabels = null;
        $addressFields = null;

        $countryCode = $value ? $value->getCountryCode() : $this->defaultCountryCode;
        $countryCodeField = Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Address/input/countryCode',
            [
                'name' => $this->handle,
                'value' => $countryCode,
                'field' => $this,
                'id' => $id,
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
        $googleApiKey = $pluginSettings->googleApiKey;
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
                'depends' => [AddressFieldAsset::class]
            ]
        );
    }

    private function renderAddressFields($value)
    {
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);
        $countryCode = $value ? $value->getCountryCode() : $this->defaultCountryCode;

        if (empty($countryCode)) {
            return;
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

        $formatTemplate .= "\n%latitude %longitude";
        $formatTemplate .= "\n%mapUrl";

        $formatTemplate = preg_replace(
            '/(?:(?:\r\n|\r|\n)\s*){2}/s',
            "\n",
            $formatTemplate
        );

        $formatRows = array_filter(
            array_map('trim', explode("\n", $formatTemplate))
        );

        $addressFields = '';

        foreach ($formatRows as $formatRow) {
            preg_match_all('/%([a-zA-Z0-9]+)/i', $formatRow, $matches);
            $className = implode('-', $matches[1]);

            if (!$fieldSettings['showLatLng'] && $className === 'latitude-longitude') {
                $className .= ' hidden';
            }

            if (!$fieldSettings['showMapUrl'] && $className === 'mapUrl') {
                $className .= ' hidden';
            }

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
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'mapUrl' => 'Map URL',
        ];

        return $labels;
    }

    /**
     * Get country options
     *
     * @return array
     */
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

    /**
     * Get subdivision options
     *
     * @param $countryCode
     * @param null $parentId
     * @return array
     */
    private function getSubdivisionOptions(
        $countryCode,
        $parentId = null
    ): array {

        if (!$countryCode) {
            return [];
        }

        $subdivisionRepository = new SubdivisionRepository();
        $subdivisions = $subdivisionRepository->getAll(array_filter([$countryCode, $parentId]));
        $options = [[
            'value' => '',
            'label' => ''
        ]];

        foreach ($subdivisions as $key => $option) {
            $options[] = [
                'value' => $option->getCode(),
                'label' => $option->getName(),
            ];
        }

        return $options;
    }

    /**
     * @param mixed $value
     * @param ElementInterface|null $element
     * @return mixed|AddressModel
     */
    public function normalizeValue($value, ElementInterface $element = null)
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
            $value = json_decode($value, true);
        }

        /**
         * Array value from post or unserialized array
         */
        if (is_array($value) && !empty(array_filter($value))) {
            return new AddressModel($value);
        }

        return null;
    }

    /**
     * @param mixed $value
     * @param ElementInterface|null $element
     * @return array|mixed|null|string
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        if(empty($value)) {
            return null;
        }

        $data = json_encode($value);

        if (Craft::$app->getDb()->getIsMysql()) {
            // Encode any 4-byte UTF-8 characters.
            $data = StringHelper::encodeMb4($data);
        }

        return $data;
    }

    public function getSearchKeywords($value, ElementInterface $element): string
    {
        return (string)$value; // TODO: Change the autogenerated stub
    }
}
