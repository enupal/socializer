<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @dedicado Al amor de mi vida, mi Sara **).
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\models;

use craft\base\Model;
use enupal\socializer\Socializer;

class Settings extends Model
{
    public $userGroupId = null;
    public $enableUserSignUp = 1;
    public $enableFieldMapping = 1;
    public $enableFieldMappingPerProvider = 0;
    public $fieldMapping;
    public $enableCp = 1;
}