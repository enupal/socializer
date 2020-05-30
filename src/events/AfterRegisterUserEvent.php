<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\events;


use craft\elements\User;
use enupal\socializer\elements\Provider;
use Hybridauth\User\Profile;
use yii\base\Event;

/**
 * AfterRegisterUserEvent class.
 */
class AfterRegisterUserEvent extends Event
{
    /**
     * @var Provider
     */
    public $provider;

    /**
     * @var User
     */
    public $user;

    /**
     * @var Profile
     */
    public $userProfile;
}
