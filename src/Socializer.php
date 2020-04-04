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

use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use enupal\socializer\models\Settings;
use enupal\socializer\services\App;
use enupal\socializer\variables\SocializerVariable;
use yii\base\Event;


class Socializer extends Plugin
{
    /**
     * Enable use of Socializer::$app-> in place of Craft::$app->
     *
     * @var App
     */
    public static $app;

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
        self::$app = $this->get('app');

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('socializer', SocializerVariable::class);
            }
        );

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getCpUrlRules());
        }
        );
    }

    /**
     * @return array
     */
    private function getCpUrlRules()
    {
        return [
            'enupal-socializer/providers/new' =>
                'enupal-socializer/providers/edit-provider',

            'enupal-socializer/providers/edit/<providerId:\d+>' =>
                'enupal-socializer/providers/edit-provider'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem()
    {
        $parent = parent::getCpNavItem();
        return array_merge($parent, [
            'subnav' => [
                'providers' => [
                    "label" => Craft::t('enupal-socializer',"Providers"),
                    "url" => 'enupal-socializer/providers'
                ],
                'settings' => [
                    "label" => Craft::t('enupal-socializer',"Settings"),
                    "url" => 'enupal-socializer/settings'
                ]
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }
}
