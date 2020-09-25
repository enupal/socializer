<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\validators;

use enupal\socializer\Socializer;
use Hybridauth\Provider\Apple;
use yii\validators\Validator;

use Craft;

class EnabledValidator extends Validator
{
    public $skipOnEmpty = false;

    /**
     * Enabled Provider validation
     *
     * @param $object
     * @param $attribute
     */
    public function validateAttribute($object, $attribute)
    {
        if ($object->type != Apple::class) {
            if ($object->enabled && !$object->clientId) {
                $this->addError($object, $attribute, Craft::t('enupal-socializer','Client ID cannot be blank'));
            }

            if ($object->enabled && !$object->clientSecret) {
                $this->addError($object, 'clientSecret', Craft::t('enupal-socializer','Client Secret cannot be blank'));
            }
        }else if ($object->type === Apple::class && $object->enabled) {
            if (!Socializer::$app->settings->validateAppleSettings()) {
                $this->addError($object, 'enabled', Craft::t('enupal-socializer','Unable to process the Apple settings from the config file'));
            }
        }

    }
}
