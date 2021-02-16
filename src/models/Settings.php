<?php

namespace newism\fields\models;

use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;

class Settings extends Model
{
    public $googleApiKey = '';
    public $facebookToken = '';
    public $instagramToken = '';

    public function behaviors()
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

    public function rules()
    {
        return [
            ['googleApiKey', 'string'],
            ['facebookToken', 'string'],
            ['instagramToken', 'string'],
        ];
    }
}
