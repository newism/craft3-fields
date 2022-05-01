<?php

namespace newism\fields\controllers;

use Craft;
use craft\web\Controller;
use Exception;
use newism\fields\NsmFields;

class EmbedController extends Controller
{
    protected array|int|bool $allowAnonymous = [];

    public function actionParse(): string
    {
        $rawInput = Craft::$app->request->get('url');
        $value = null;
        $embedData = null;

        if ($rawInput) {
            try {
                $embedData = NsmFields::getInstance()->embed->parse(urldecode($rawInput));
            } catch (Exception $exception) {

            }

            $value = [
                'rawInput' => $rawInput,
                'embedData' => $embedData,
            ];
        }

        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Embed/inputEmbed.twig', [
                'name' => Craft::$app->request->get('name'),
                'value' => $value,
            ]
        );
    }

}
