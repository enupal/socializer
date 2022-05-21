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
use craft\services\Gc;

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
    public string $schemaVersion = '2.0.0';

    public bool $hasCpSection = true;

    /**
     * @inheritdoc
     */
    public bool $hasCpSettings = true;

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

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getSiteUrlRules());
        }
        );

        Event::on(Gc::class, Gc::EVENT_RUN, function() {
            Craft::$app->gc->hardDelete('{{%enupalsocializer_providers}}');
        });
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
    public function getCpNavItem(): ?array
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
     * @return array
     */
    private function getSiteUrlRules()
    {
        return [
            'socializer/login' =>
                'enupal-socializer/login/login',

            'socializer/login/callback' =>
                'enupal-socializer/login/callback'
        ];
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('enupal-socializer/settings/index');
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }
}
