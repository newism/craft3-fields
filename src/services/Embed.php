<?php
/**
 * test plugin for Craft CMS 3.x
 *
 * test
 *
 * @link      http://newism.com.au
 * @copyright Copyright (c) 2017 Newism
 */

namespace newism\fields\services;

use craft\base\Component;

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
 * @package   Test
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
        $info = \Embed\Embed::create($url);
        $result = array();
        $result['title'] = $info->title;
        $result['description'] = $info->description;
        $result['url'] = $info->url;
        $result['type'] = $info->type;
        $result['tags'] = $info->tags;
        $result['images'] = $info->images;
        $result['image'] = $info->image;
        $result['imageWidth'] = $info->imageWidth;
        $result['imageHeight'] = $info->imageHeight;
        $result['code'] = $info->code;
        $result['source'] = $info->source;
        $result['width'] = $info->width;
        $result['height'] = $info->height;
        $result['aspectRatio'] = $info->aspectRatio;
        $result['authorName'] = $info->authorName;
        $result['authorUrl'] = $info->authorUrl;
        $result['providerName'] = $info->providerName;
        $result['providerUrl'] = $info->providerUrl;
        $result['providerIcon'] = $info->providerIcon;
        $result['providerIcons'] = $info->providerIcons;
        $result['publishedDate'] = $info->publishedDate;
        $result['publishedTime'] = $info->publishedTime;
        $result['license'] = $info->license;
        $result['linkedData'] = $info->linkedData;

        return $result;
    }
}
