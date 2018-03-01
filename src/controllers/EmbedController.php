<?php
/**
 * test plugin for Craft CMS 3.x
 *
 * test
 *
 * @link      http://newism.com.au
 * @copyright Copyright (c) 2017 newism
 */

namespace newism\fields\controllers;

use Craft;
use craft\web\Controller;
use newism\fields\NsmFields;

/**
 * Embed Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    newism
 * @package   Test
 * @since     1.0.0
 */
class EmbedController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function actionParse(): string
    {
        $rawInput = Craft::$app->request->get('url');
        $value = null;
        $embedData = null;

        if($rawInput) {
            try{
                $embedData = NsmFields::getInstance()->embed->parse(urldecode($rawInput));
            } catch (\Exception $exception) {

            }

            $value = [
                'rawInput' => $rawInput,
                'embedData' => $embedData,
            ];
        }

        return Craft::$app->getView()->renderTemplate(
            'nsm-fields/_components/fieldtypes/Embed/inputEmbed.twig',
            [
                'name' => Craft::$app->request->get('name'),
                'value' => $value,
            ]
        );
    }

}
