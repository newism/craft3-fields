<?php
/**
 * NSM Fields plugin for Craft CMS 3.x
 *
 * Various fields for CraftCMS
 *
 * @link      http://newism.com.au
 * @copyright Copyright (c) 2017 Leevi Graham
 */

namespace newism\fields\assetbundles\personnamefield;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * PersonNameFieldAsset AssetBundle
 *
 * AssetBundle represents a collection of asset files, such as CSS, JS, images.
 *
 * Each asset bundle has a unique name that globally identifies it among all asset bundles used in an application.
 * The name is the [fully qualified class name](http://php.net/manual/en/language.namespaces.rules.php)
 * of the class representing it.
 *
 * An asset bundle can depend on other asset bundles. When registering an asset bundle
 * with a view, all its dependent asset bundles will be automatically registered.
 *
 * http://www.yiiframework.com/doc-2.0/guide-structure-assets.html
 *
 * @author    Leevi Graham
 * @package   NsmFields
 * @since     1.0.0
 */
class PersonNameFieldAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = "@newism/fields/assetbundles/personnamefield/dist";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'css/PersonName.css',
        ];

        parent::init();
    }
}
