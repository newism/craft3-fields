<?php
/**
 * Created by PhpStorm.
 * User: leevigraham
 * Date: 25/2/17
 * Time: 20:52
 */

namespace newism\fields\models;


use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;

class Settings extends Model
{
    public ?string $googleApiKey = '';

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
