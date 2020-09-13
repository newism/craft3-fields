<?php
namespace newism\fields\models;

use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;

class Settings extends Model
{
    public $googleApiKey = '';

    public function behaviors()
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => ['googleApiKey'],
            ],
        ];
    }

    public function rules()
    {
        return [
            ['googleApiKey', 'string'],
        ];
    }
}
