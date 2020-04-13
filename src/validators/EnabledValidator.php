<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\validators;

use yii\validators\Validator;
use enupal\backup\Backup;

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
        if ($object->enabled && !$object->clientId) {
            $this->addError($object, $attribute, Backup::t('Client ID cannot be blank'));
        }

        if ($object->enabled && !$object->clientSecret) {
            $this->addError($object, 'clientSecret', Backup::t('Client Secret cannot be blank'));
        }
    }
}
