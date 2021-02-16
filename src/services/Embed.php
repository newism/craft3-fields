<?php
/**
 * NSM Fieldsfor Craft CMS 3.x
 *
 * nsm-fields
 *
 * @link      http://newism.com.au
 * @copyright Copyright (c) 2017 Newism
 */

namespace newism\fields\services;

use craft\base\Component;
use newism\fields\NsmFields;

/**
 * Embed Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Newism
 * @package   nsm-fields
 * @since     1.0.0
 */
class Embed extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     NsmFields::$plugin->embed->parse()
     *
     * @return mixed
     */
    public function parse($url)
    {
        $pluginSettings = NsmFields::getInstance()->getSettings();
        $embed = new \Embed\Embed();
        $embed->setSettings([
            'facebook:token' => $pluginSettings->facebookToken,  //Required to embed content from Facebook
            'instagram:token' => $pluginSettings->instagramToken, //Required to embed content from Instagram
        ]);
        $info = $embed->get($url);
        $result = [];

        $result['authorName'] = $info->authorName;
        $result['authorUrl'] = $info->authorUrl;
        $result['cms'] = $info->cms;
        $result['code'] = $info->code;
        $result['description'] = $info->description;
        $result['favicon'] = $info->favicon;
        $result['feeds'] = $info->feeds;
        $result['icon'] = $info->icon;
        $result['image'] = $info->image;
        $result['keywords'] = $info->keywords;
        $result['language'] = $info->language;
        $result['languages'] = $info->languages;
        $result['license'] = $info->license;
        $result['providerName'] = $info->providerName;
        $result['providerUrl'] = $info->providerUrl;
        $result['publishedTime'] = $info->publishedTime;
        $result['redirect'] = $info->redirect;
        $result['title'] = $info->title;
        $result['url'] = $info->url;
        $result['ombed'] =$info->getOEmbed()->all();
        $result['linkedData'] =$info->getLinkedData()->all();

        return $result;
    }
}
