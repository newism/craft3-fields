<?php

namespace newism\fields\models;

use yii\base\Model;

class EmbedModel extends Model
{
    public $rawInput;
    public $embedData;

    public function isEmpty(): bool
    {
        return empty($this->rawInput);
    }
}
