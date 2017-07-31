<?php
/**
 * NSM Fields plugin for Craft CMS 3.x
 *
 * Various fields for CraftCMS
 *
 * @link      http://newism.com.au
 * @copyright Copyright (c) 2017 Leevi Graham
 */

namespace newism\fields;

use Craft;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Fields;
use craft\services\Plugins;
use craft\web\UrlManager;
use newism\fields\fields\Address as AddressField;
use newism\fields\fields\Email as EmailField;
use newism\fields\fields\Embed as EmbedField;
use newism\fields\fields\Gender;
use newism\fields\fields\Telephone as TelephoneField;
use newism\fields\fields\PersonName as PersonNameField;
use newism\fields\models\Settings;
use newism\fields\services\Embed;
use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Leevi Graham
 * @package   NsmFields
 * @since     1.0.0
 */
class NsmFields extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * NsmFields::$plugin
     *
     * @var static
     */
    public static $plugin;


    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * NsmFields::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Register our fields
        Event::on(
            Fields::className(),
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = TelephoneField::class;
                $event->types[] = AddressField::class;
                $event->types[] = EmailField::class;
                $event->types[] = EmbedField::class;
                $event->types[] = PersonNameField::class;
                $event->types[] = Gender::class;
            }
        );

        $this->setComponents(
            [
                'embed' => Embed::class,
            ]
        );

        // Register our CP routes
        Event::on(
            UrlManager::className(),
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['cpActionTrigger1'] = 'nsm-fields/embed/parse';
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::className(),
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
            }
        );

        Craft::info(
            'NsmFields '.Craft::t('nsm-fields', 'plugin loaded'),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return Settings
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @return string
     * @throws \yii\base\Exception
     * @throws \Twig_Error_Loader
     * @throws \RuntimeException
     */
    protected function settingsHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate(
            'nsm-fields/settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }
}
