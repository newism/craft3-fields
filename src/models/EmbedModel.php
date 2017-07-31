<?php

namespace newism\fields\models;

use CommerceGuys\Addressing\Model\AddressInterface;
use yii\base\Model;

class EmbedModel extends Model implements \JsonSerializable
{
    public $rawInput;
    public $embedData;

    /**
     * json serialize countryCode and phoneNumber for DB storage
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'rawInput' => $this->rawInput,
            'embedData' => $this->embedData
        ];
    }

    public function isEmpty(): bool {
        return empty($this->rawInput);
    }
}
