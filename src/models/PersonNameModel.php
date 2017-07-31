<?php

namespace newism\fields\models;

use yii\base\Model;

class PersonNameModel extends Model
{
    public function rules()
    {
        return [
            [
                [
                    'honorificPrefix',
                    'givenNames',
                    'additionalNames',
                    'familyNames',
                    'honorificSuffix',
                ],
                'safe',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'honorificPrefix' => 'Honorific Prefix',
            'givenNames' => 'Given Names',
            'additionalNames' => 'Additional Names',
            'familyNames' => 'Family Names',
            'honorificSuffix' => 'Honorific Suffix',
        ];
    }

    /**
     * @return string
     */
    public function __toString() {
        return implode(" ", [
            $this->honorificPrefix,
            $this->givenNames,
            $this->additionalNames,
            $this->familyNames,
            $this->honorificSuffix
        ]);
    }

    public $honorificPrefix;
    public $givenNames;
    public $additionalNames;
    public $familyNames;
    public $honorificSuffix;

    /**
     * @return mixed
     */
    public function getHonorificPrefix()
    {
        return $this->honorificPrefix;
    }

    /**
     * @return mixed
     */
    public function getGivenNames()
    {
        return $this->givenNames;
    }

    /**
     * @return mixed
     */
    public function getAdditionalNames()
    {
        return $this->additionalNames;
    }

    /**
     * @return mixed
     */
    public function getFamilyNames()
    {
        return $this->familyNames;
    }

    /**
     * @return mixed
     */
    public function getHonorificSuffix()
    {
        return $this->honorificSuffix;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty(array_filter([
            $this->honorificPrefix,
            $this->givenNames,
            $this->additionalNames,
            $this->familyNames,
            $this->honorificPrefix
        ]));
    }

}
