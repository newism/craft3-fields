<?php

namespace newism\fields\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use newism\fields\models\PersonNameModel;
use yii\db\Schema;

class PersonName extends Field implements PreviewableFieldInterface
{
    public static function displayName(): string
    {
        return Craft::t('nsm-fields', 'Person Name (Newism)');
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/PersonName/settings', [
                'field' => $this,
            ]
        );
    }
    
    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_TEXT;
    }
    
    public function normalizeValue(mixed $value, ?ElementInterface $element = null ): mixed {

        // Just return value if it's already an PersonNameModel.
        if ($value instanceof PersonNameModel) {
            return $value;
        }

        // Serialised value from the DB
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        // Array value from post or unserialized array
        if (is_array($value) && !empty(array_filter($value))) {
            return new PersonNameModel($value);
        }

        return null;
    }
    
    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string {
        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/PersonName/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
            ]
        );
    }
    
    public function getSearchKeywords( mixed $value, ElementInterface $element ): string {
        return json_encode($this);
    }
    
    public function isValueEmpty(mixed $value, ElementInterface $element = null): bool
    {
        if ($value instanceof PersonNameModel) {
            return $value->isEmpty();
        }

        return parent::isValueEmpty($value, $element);
    }
}
