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

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use libphonenumber\PhoneNumberUtil;
use newism\fields\models\TelephoneModel;
use Symfony\Component\Intl\Intl;

/**
 * Telephone Field
 *
 * @author    Leevi Graham
 * @package   NsmFields
 * @since     1.0.0
 */
class Telephone extends Field implements PreviewableFieldInterface
{
    // Public Properties
    // =========================================================================

    /**
     * @var PhoneNumberUtil
     */
    protected $phoneNumberUtil;

    /**
     * Default Country Code
     *
     * @var string
     */
    public $defaultCountryCode = 'US';

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('nsm-fields', 'NSM Telephone');
    }

    // Public Methods
    // =========================================================================

    /**
     * Init the field, set the phoneNumberUtil
     */
    public function init()
    {
        parent::init();
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
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
                [['defaultCountryCode'], 'default', 'value' => 'US'],
            ]
        );

        return $rules;
    }

    /**
     * Normalizes the field’s value for use.
     *
     * This method is called when the field’s value is first accessed from the element. For example, the first time
     * `entry.myFieldHandle` is called from a template, or right before [[getInputHtml()]] is called. Whatever
     * this method returns is what `entry.myFieldHandle` will likewise return, and what [[getInputHtml()]]’s and
     * [[serializeValue()]]’s $value arguments will be set to.
     *
     * @param mixed $value The raw field value
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @return mixed The prepared field value
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        /**
         * Serialised value from the DB
         */
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        /**
         * Default values
         */
        if (!is_array($value)) {
            $value = [
                'countryCode' => $this->defaultCountryCode,
                'rawInput' => '',
            ];
        }

        return new TelephoneModel($value['countryCode'] ?? $this->defaultCountryCode, $value['rawInput']);
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        if ($value instanceof TelephoneModel && !$value->phoneNumber) {
            return null;
        }

        return parent::serializeValue($value, $element);
    }


    /**
     * Returns the component’s settings HTML.
     *
     * @return string
     * @throws \yii\base\Exception
     * @throws \Twig_Error_Loader
     * @throws \RuntimeException
     */
    public function getSettingsHtml(): string
    {
        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Telephone/settings',
            [
                'field' => $this,
                'countryOptions' => $this->getCountryOptions(),
            ]
        );
    }

    /**
     * Returns the field’s input HTML.
     *
     * @param $value
     * @param ElementInterface|null $element
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getInputHtml(
        $value,
        ElementInterface $element = null
    ): string {

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Telephone/input',
            [
                'name' => $this->handle,
                'viewData' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
                'countryOptions' => $this->getCountryOptions(),
                'settings' => $this->getSettings(),
            ]
        );
    }

    /**
     * Get country options
     *
     * @return array
     */
    private function getCountryOptions(): array
    {
        $countries = [['value' => '', 'label' => '']];
        $countryData = Intl::getRegionBundle()->getCountryNames();

        foreach ($countryData as $key => $option) {
            $regionCode = $this->phoneNumberUtil->getCountryCodeForRegion(
                $key
            );
            $countries[] = [
                'value' => $key,
                'label' => $option . ($regionCode ? ' +' . $regionCode : ''),
            ];
        }

        return $countries;
    }

    /**
     * Returns the validation rules for an element with this field.
     *
     * Rules should be defined in the array syntax required by [[\yii\base\Model::rules()]],
     * with one difference: you can skip the first argument (the attribute list).
     *
     * Below are some examples:
     *
     * ```php
     * [
     *     // explicitly specify the field attribute
     *     [$this->handle, 'string', 'min' => 3, 'max' => 12],
     *     // skip the field attribute
     *     ['string', 'min' => 3, 'max' => 12],
     *     // you can only pass the validator class name/handle if not setting any params
     *     'bool',
     * ];
     * ```
     *
     * @return array
     */
    public function getElementValidationRules(): array
    {
        $rules = parent::getElementValidationRules();
        // add our rule
        $rules[] = 'validatePhoneNumber';

        return $rules;
    }

    /**
     * Returns whether the given value should be considered “empty” to a validator.
     *
     * @param mixed $value The field’s value
     * @param ElementInterface $element
     *
     * @return bool Whether the value should be considered “empty”
     * @see Validator::$isValueEmpty
     */
    public function isValueEmpty($value, ElementInterface $element = null ): bool
    {
        if ($value instanceof TelephoneModel) {
            return (null === $value->phoneNumber);
        }

        return parent::isValueEmpty($value);
    }

    /**
     * Validates the field value.
     *
     * @param ElementInterface $element
     * @param array|null $params
     * @return null|string
     */
    public function validatePhoneNumber(
        ElementInterface $element,
        array $params = null
    ) {
        /** @var TelephoneModel $value */
        $value = $element->getFieldValue($this->handle);
        $valid = $value->isValid();

        /**
         * Add the error
         */
        if (!$valid) {
            $element->addError(
                $this->handle,
                Craft::t(
                    'nsm-fields',
                    'The string supplied did not seem to be a phone number.'
                )
            );
        }
    }

    /**
     * Returns the HTML that should be shown for this field in Table View.
     *
     * @param mixed $value The field’s value
     * @param ElementInterface $element The element the field is associated with
     *
     * @return string The HTML that should be shown for this field in Table View
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        if (!$value->phoneNumber) {
            return '';
        }

        return (string)sprintf('%s [%s]', (string)$value, $value->countryCode);
    }
}
