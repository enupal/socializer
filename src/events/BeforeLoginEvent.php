<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\events;


use craft\elements\User;
use craft\events\CancelableEvent;
use enupal\socializer\elements\Provider;
use Hybridauth\User\Profile;

/**
 * BeforeLoginEvent class.
 */
class BeforeLoginEvent extends CancelableEvent
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
