<?php

namespace newism\fields\models;

use yii\base\Model;

class EmbedModel extends Model
{
    public ?string $rawInput;
    public ?array $embedData;

    public function isEmpty(): bool
    {
        return empty($this->rawInput);
    }

    public function __toString()
    {
        return empty($this->embedData) ? $this->rawInput : $this->embedData['title'];
    }
}
