<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * Sign in, Register and share
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal
 */

namespace enupal\socializer;


use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;

use yii\base\Event;

/**
 * Class Socializer
 *
 * @author    Enupal
 * @package   Socializer
 * @since     1.0.0
 *
 */
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

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'socializer',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
