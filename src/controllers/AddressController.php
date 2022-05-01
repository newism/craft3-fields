<?php

namespace newism\fields\controllers;

use Craft;
use craft\web\Controller;
use newism\fields\models\AddressModel;
use yii\web\Response;

class AddressController extends Controller
{
    protected array|int|bool $allowAnonymous = [];

    public function actionRefreshCountry(): Response
    {
        $this->requireAcceptsJson();

        $handle = Craft::$app->request->post('handle');
        $context = Craft::$app->request->post('context');
        $addressField = Craft::$app->fields->getFieldByHandle($handle, $context);

        $addressModel = new AddressModel([
                'countryCode' => Craft::$app->request->post('countryCode'),
        ]);

        $response = [
            'html' => Craft::$app->getView()->namespaceInputs($addressField->renderFormFields($addressModel), Craft::$app->request->post('namespace')),
        ];

        return $this->asJson($response);
    }

}
