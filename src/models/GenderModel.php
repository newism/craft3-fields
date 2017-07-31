<?php

namespace newism\fields\models;

use yii\base\Model;

class GenderModel extends Model
{
    public $sex;
    public $identity;

    public static $sexLabels = [
        'M' => 'Male',
        'F' => 'Female',
        'O' => 'Other',
        'N' => 'None',
        'U' => 'Unknown',
    ];

    public function rules()
    {
        return [
            [
                [
                    'sex',
                    'identity'
                ],
                'safe',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'sex' => 'Sex',
            'identity' => 'Identity'
        ];
    }

    /**
     * @return string
     */
    public function __toString() {

        $str =  self::$sexLabels[$this->sex];

        if($this->identity) {
            $str .= " [".$this->identity."]";
        }

        return $str;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty(array_filter([
            $this->sex,
            $this->identity
        ]));
    }

}
