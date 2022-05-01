<?php

namespace newism\fields\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use newism\fields\models\GenderModel;
use yii\db\Schema;

class Gender extends Field implements PreviewableFieldInterface
{
    public static function displayName(): string
    {
        return Craft::t('nsm-fields', 'Gender (Newism)');
    }
    
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Gender/settings',
            [
                'field' => $this,
            ]
        );
    }
    
    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_TEXT;
    }
    
    public function normalizeValue( mixed $value, ?ElementInterface $element = null): mixed {

        // Just return value if it's already an GenderModel.
        if ($value instanceof GenderModel) {
            return $value;
        }

        // Serialised value from the DB
        if (is_string($value)) {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        // Array value from post or unserialized array
        if (is_array($value) && !empty(array_filter($value))) {
            return new GenderModel($value);
        }

        return null;
    }
    
    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string {
        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Gender/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'sexOptions' => $this->getSexOptions(),
            ]
        );
    }

    protected function getSexOptions(): array
    {
        return [
            '' => '',
            'M' => 'Male',
            'F' => 'Female',
            'O' => 'Other',
            'N' => 'None',
            'U' => 'Unknown',
        ];
    }
    
    public function getSearchKeywords(mixed $value, ElementInterface $element): string {
        return json_encode($this);
    }
    
    public function isValueEmpty(mixed $value, ElementInterface $element = null): bool {
        return ($value instanceof GenderModel)
            ? $value->isEmpty()
            : parent::isValueEmpty($value, $element);
    }

}
