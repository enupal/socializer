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
use enupal\socializer\models\Settings;

class Socializer extends Plugin
{
    /**
     * @var Socializer
     */
    public static $plugin;

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    public $hasCpSection = true;
    public $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }
}
