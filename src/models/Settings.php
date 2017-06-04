<?php
/**
 * Created by PhpStorm.
 * User: leevigraham
 * Date: 25/2/17
 * Time: 20:52
 */

namespace newism\fields\models;


class Settings extends \craft\base\Model
{
    public $googleApiKey;
    public $addressDefaultFormat;

    public function rules()
    {
        return [];
    }
}
