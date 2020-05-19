<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2018 Enupal LLC
 */

namespace enupal\socializer\controllers;

use craft\web\Controller as BaseController;

class FrontEndController extends BaseController
{
    // Disable CSRF validation for the entire controller
    public $enableCsrfValidation = false;

    protected $allowAnonymous = true;
}
