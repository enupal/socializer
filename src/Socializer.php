<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @dedicado Al amor de mi vida, mi Sara **).
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */
namespace enupal\socializer;

use Craft;
use craft\base\Plugin;

class Socializer extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Socializer
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

    }

}
