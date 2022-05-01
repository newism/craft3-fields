<?php

namespace newism\fields\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use newism\fields\assetbundles\EmbedField\EmbedFieldAsset;
use newism\fields\models\EmbedModel;
use newism\fields\NsmFields;
use yii\db\Schema;
use yii\helpers\Json;

class Embed extends Field implements PreviewableFieldInterface
{
    public static function displayName(): string
    {
        return Craft::t('nsm-fields', 'Embed (Newism)');
    }

    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_TEXT;
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Embed/settings',
            [
                'field' => $this,
            ]
        );
    }

    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        // Just return value if it's already an EmbedModel.
        if ($value instanceof EmbedModel) {
            return $value;
        }

        // Serialised value from the DB
        if (is_string($value)) {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        // Array value from post or unserialized array
        if (is_array($value) && $value['rawInput']) {
            if (!Craft::$app->getRequest()->getIsConsoleRequest() && Craft::$app->getRequest()->getIsPost()) {
                $embedData = NsmFields::getInstance()->embed->parse($value['rawInput']);
                $value['embedData'] = $embedData;
            }

            return new EmbedModel($value);
        }

        return null;
    }

    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if (empty($value)) {
            return null;
        }

        $data = json_encode(
            [
                'rawInput' => $value->rawInput,
                'embedData' => $value->embedData,
            ]
        );

        if (Craft::$app->getDb()->getIsMysql()) {
            // Encode any 4-byte UTF-8 characters.
            $data = StringHelper::encodeMb4($data);
        }

        return $data;
    }
    
    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {

        // Register our asset bundle
        Craft::$app->getView()->registerAssetBundle(EmbedFieldAsset::class);

        // Get our id and namespace
// Get our id and namespace
        $id = Html::id($this->handle);
        $namespace = Craft::$app->getView()->getNamespace();
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
            "$('#{$namespacedId}-field').NsmFieldsEmbed({$jsonVars});"
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

    public function getTableAttributeHtml(mixed $value, ElementInterface $element): string
    {
        Craft::$app->getView()->registerAssetBundle(EmbedFieldAsset::class);

        if (!$value) {
            return '';
        }

        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Embed/tableAttributeHtml',
            ['value' => $value]
        );
    }

    public function isValueEmpty(mixed $value, ElementInterface $element = null): bool
    {
        return ($value instanceof EmbedModel)
            ? $value->isEmpty() :
            parent::isValueEmpty($value, $element);
    }
}
