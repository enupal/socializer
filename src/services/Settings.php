<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\services;

use Craft;
use craft\db\Query;
use yii\base\Component;
use enupal\socializer\models\Settings as SettingsModel;
use enupal\socializer\Socializer;
use yii\db\Exception;

class Settings extends Component
{
    /**
     * Saves Settings
     *
     * @param $scenario
     * @param $settings SettingsModel
     *
     * @return bool
     */
    public function saveSettings(SettingsModel $settings, $scenario = null): bool
    {
        $plugin = $this->getPlugin();

        if (!is_null($scenario)) {
            $settings->setScenario($scenario);
        }

        // Validate them, now that it's a model
        if ($settings->validate() === false) {
            return false;
        }

        $success = Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->getAttributes());

        return $success;
    }

    /**
     * @return SettingsModel
     */
    public function getSettings()
    {
        /** @var SettingsModel $settings */
        $settings = $this->getPlugin()->getSettings();

        return $settings;
    }

    /**
     * @return \craft\base\PluginInterface|null
     */
    public function getPlugin()
    {
        return Craft::$app->getPlugins()->getPlugin('enupal-socializer');
    }

    /**
     * @return string
     */
    public function getPublishableKey()
    {
        $settings = $this->getSettings();
        $publishableKey = $settings->testMode ? $settings->testPublishableKey : $settings->livePublishableKey;

        return $publishableKey;
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        $settings = $this->getSettings();

        $secretKey = $settings->testMode ? $settings->testSecretKey : $settings->liveSecretKey;

        return $secretKey;
    }

    /**
     * @throws \Exception
     */
    public function initializeStripe()
    {
        $privateKey = $this->getPrivateKey();

        if ($privateKey) {
            Stripe::setAppInfo('Craft CMS - '.StripePlugin::getInstance()->name, StripePlugin::getInstance()->version, StripePlugin::getInstance()->documentationUrl, self::STRIPE_PARTNER_ID);
            Stripe::setApiKey($privateKey);
            Stripe::setApiVersion('2019-09-09');
        }
        else{
            throw new \Exception(Craft::t('enupal-stripe','Unable to get the stripe keys.'));
        }
    }

    /**
     * @return array|null
     */
    public function getConfigSettings()
    {
        return Craft::$app->config->getGeneral()->stripePayments ?? null;
    }

    /**
     * @return string|null
     */
    public function getPluginUid()
    {
        $plugin = (new Query())
            ->select(['uid'])
            ->from('{{%plugins}}')
            ->where(["handle" => 'enupal-stripe'])
            ->one();

        return $plugin['uid'] ?? null;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getFieldContext()
    {
        $pluginUid = $this->getPluginUid();

        if (is_null($pluginUid)){
            throw new Exception('Unable to get the plugin Uid');
        }

        $context = 'enupalStripe:'.$pluginUid;

        return $context;
    }
}
