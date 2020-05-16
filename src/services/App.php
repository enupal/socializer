<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\services;

use craft\base\Component;

class App extends Component
{
    /**
     * @var Settings
     */
    public $settings;

    /**
     * @var Providers
     */
    public $providers;

    /**
     * @var Tokens
     */
    public $tokens;

    public function init()
    {
        $this->settings = new Settings();
        $this->providers = new Providers();
        $this->tokens = new Tokens();
    }
}