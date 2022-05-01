<?php

namespace newism\fields\fields;

use Craft;
use craft\base\ElementInterface;
use craft\fields\PlainText;

/**
 *
 * @property-read array $elementValidationRules
 * @property-read null|string $settingsHtml
 */
class Email extends PlainText
{
    public static function displayName(): string
    {
        return Craft::t('nsm-fields', 'Email (Newism)');
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Email/settings',[
                'field' => $this,
            ]
        );
    }

    public function getElementValidationRules(): array
    {
        $rules = parent::getElementValidationRules();
        $rules[] = 'email';

        return $rules;
    }

    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Email/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
            ]
        );
    }
}
