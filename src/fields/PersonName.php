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
use newism\fields\models\PersonNameModel;
use yii\db\Schema;

/**
 * Email Field
 *
 * Whenever someone creates a new field in Craft, they must specify what
 * type of field it is. The system comes with a handful of field types baked in,
 * and we’ve made it extremely easy for plugins to add new ones.
 *
 * https://craftcms.com/docs/plugins/field-types
 *
 * @author    Leevi Graham
 * @package   NsmFields
 * @since     1.0.0
 */
class PersonName extends Field implements PreviewableFieldInterface
{
    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('nsm-fields', 'NSM Person Name');
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules = array_merge(
            $rules,
            []
        );

        return $rules;
    }

    /**
     * Get settings HTML
     *
     * @return string
     * @throws \yii\base\Exception
     * @throws \Twig_Error_Loader
     * @throws \RuntimeException
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/PersonName/settings',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * @return string
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @param mixed $value
     * @param ElementInterface|null $element
     * @return mixed|PersonNameModel
     */
    public function normalizeValue(
        $value,
        ElementInterface $element = null
    )
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (is_array($value) && !empty(array_filter($value))) {
            return new PersonNameModel($value);
        }

        return null;
    }

    /**
     * Returns the field’s input HTML.
     *
     * @param mixed $value
     * @param ElementInterface|null $element
     * @return string
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\Exception
     * @throws \Twig_Error_Loader
     * @throws \RuntimeException
     * @throws \yii\base\InvalidConfigException
     */
    public function getInputHtml(
        $value,
        ElementInterface $element = null
    ): string
    {
        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/PersonName/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
            ]
        );
    }

    /**
     * @param mixed $value
     * @param ElementInterface $element
     * @return string
     */
    public function getSearchKeywords(
        $value,
        ElementInterface $element
    ): string
    {
        return json_encode($this);
    }

    /**
     * Returns whether the given value should be considered “empty” to a validator.
     *
     * @param mixed $value The field’s value
     * @param ElementInterface $element
     *
     * @return bool Whether the value should be considered “empty”
     * @see Validator::$isEmpty
     */
    public function isValueEmpty($value, ElementInterface $element = null): bool
    {
        if($value instanceof PersonNameModel) {
            return $value->isEmpty();
        }

        return parent::isValueEmpty($value);
    }

}
