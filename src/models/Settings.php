<?php

namespace newism\fields\models;

use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;

class Settings extends Model
{
    public $googleApiKey = '';
    public $facebookToken = '';
    public $instagramToken = '';

    public function behaviors(): array
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => [
                    'googleApiKey',
                    'facebookToken',
                    'instagramToken',
                ],
            ],
        ];
    }

    public function rules(): array
    {
        return [
            ['googleApiKey', 'string'],
            ['facebookToken', 'string'],
            ['instagramToken', 'string'],
        ];
    }
}
