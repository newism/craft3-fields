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
use craft\helpers\UrlHelper;
use LitEmoji\LitEmoji;
use newism\fields\assetbundles\embedfield\EmbedFieldAsset;
use newism\fields\models\EmbedModel;
use newism\fields\NsmFields;
use yii\db\Schema;
use yii\helpers\Json;

/**
 * Embed Field
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
class Embed extends Field implements PreviewableFieldInterface
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
        return Craft::t('nsm-fields', 'NSM Embed');
    }

    /**
     * @return string
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
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
            'nsm-fields/_components/fieldtypes/Embed/settings',
            [
                'field' => $this,
            ]
        );
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
     * @param mixed $value
     * @param ElementInterface|null $element
     * @return mixed|EmbedModel
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if (is_string($value)) {
            $value = json_decode(LitEmoji::shortcodeToUnicode($value), true);
        }

        if (is_array($value) && $value['rawInput']) {
            if (Craft::$app->getRequest()->getIsPost()) {
                $embedData = NsmFields::getInstance()->embed->parse($value['rawInput']);
                $value['embedData'] = $embedData;
            }
            return new EmbedModel($value);
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

        $data = json_encode([
            'rawInput' => $value->rawInput,
            'embedData' => $value->embedData
        ]);

        return LitEmoji::unicodeToShortcode($data);
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
    ): string {

        // Register our asset bundle
        Craft::$app->getView()->registerAssetBundle(EmbedFieldAsset::class);

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        $pluginSettings = NsmFields::getInstance()->getSettings();
        $fieldSettings = $this->getSettings();

        // Variables to pass down to our field JavaScript to let it namespace properly
        $jsonVars = [
            'id' => $id,
            'name' => $this->handle,
            'namespace' => $namespacedId,
            'prefix' => Craft::$app->getView()->namespaceInputId(''),
            'fieldSettings' => $fieldSettings,
            'pluginSettings' => $pluginSettings,
            'endpointUrl' => UrlHelper::actionUrl('nsm-fields/embed/parse'),
        ];

        $jsonVars = Json::encode($jsonVars);

        Craft::$app->getView()->registerJs(
            '$("#'.$namespacedId.'-field").NsmFieldsEmbed('.$jsonVars.');'
        );

        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Embed/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
                'fieldSettings' => $fieldSettings,
                'pluginSettings' => $pluginSettings,
            ]
        );
    }

    public function getElementValidationRules(): array
    {
        $rules = parent::getElementValidationRules();

        return $rules;
    }

    /**
     * @param mixed $value
     * @param ElementInterface $element
     * @return string
     */
    public function getSearchKeywords($value, ElementInterface $element): string
    {
        return json_encode($this);
    }

    /**
     * @param mixed $value
     * @param ElementInterface $element
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getTableAttributeHtml(
        $value,
        ElementInterface $element
    ): string {

        Craft::$app->getView()->registerAssetBundle(EmbedFieldAsset::class);

        if (!$value) {
            return '';
        }

        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Embed/tableAttributeHtml',
            ['value' => $value]
        );
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
        if($value instanceof EmbedModel) {
            return $value->isEmpty();
        }

        return parent::isValueEmpty($value);
    }


}
