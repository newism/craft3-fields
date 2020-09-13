<?php
/**
 * NSM Fields for Craft CMS 3.x
 *
 * nsm-fields
 *
 * @link      http://newism.com.au
 * @copyright Copyright (c) 2017 newism
 */

namespace newism\fields\controllers;

use Craft;
use craft\web\Controller;
use newism\fields\models\AddressModel;
use Twig_Error_Loader;
use yii\web\Response;

/**
 * Address Controller
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
 * @package   nsm-fields
 * @since     1.0.0
 */
class AddressController extends Controller
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
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionRefreshCountry(): Response
    {
        $this->requireAcceptsJson();

        $address = Craft::$app->fields->getFieldByHandle(Craft::$app->request->post('handle'));

        $addressModel = new AddressModel(
            [
                'countryCode' => Craft::$app->request->post('countryCode'),
            ]
        );

        $response = [
            'html' => Craft::$app->getView()->namespaceInputs($address->renderFormFields($addressModel), Craft::$app->request->post('namespace')),
        ];

        return $this->asJson($response);
    }

}
