<?php

namespace newism\fields\validators;

use yii\validators\Validator;

class JsonValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        json_decode($model->$attribute);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->addError($model, $attribute, sprintf('The JSON string supplied is not valid: %s</code>', json_last_error_msg()));
        }
    }
}
