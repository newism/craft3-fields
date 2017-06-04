<?php
/**
 * NSM Fields plugin for Craft CMS 3.x
 *
 * Various fields for CraftCMS
 *
 * @link      http://newism.com.au
 * @copyright Copyright (c) 2017 Leevi Graham
 */

namespace newism\fields\fields;

use Craft;
use craft\fields\PlainText;

/**
 * Email Field
 *
 * Whenever someone creates a new field in Craft, they must specify what
 * type of field it is. The system comes with a handful of field types baked in,
 * and we’ve made it extremely easy for plugins to add new ones.
 *
 * https://craftcms.com/docs/plugins/field-types
 *
 * @author    Leevi Graham
 * @package   NsmFields
 * @since     1.0.0
 */
class Email extends PlainText
{
    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('nsmfields', 'NSM Email');
    }

    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'nsmfields/_components/fieldtypes/Email/settings',
            [
                'field' => $this,
            ]
        );
    }

    public function getElementValidationRules(): array
    {
        $rules = parent::getElementValidationRules();
        $rules[] = 'email';

        return $rules;
    }

}
