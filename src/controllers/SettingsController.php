<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\controllers;

use Craft;
use craft\web\Controller as BaseController;
use enupal\socializer\Socializer;

class SettingsController extends BaseController
{
    /**
     * Save Plugin Settings
     *
     * @return \yii\web\Response
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveSettings()
    {
        $this->requirePostRequest();
        $settings = Craft::$app->getRequest()->getBodyParam('settings');
        $message = Craft::t('enupal-socializer','Settings saved.');

        $plugin = Socializer::$app->settings->getPlugin();
        $settingsModel = $plugin->getSettings();

        $settingsModel->setAttributes($settings, false);

        if (!Socializer::$app->settings->saveSettings($settingsModel)) {

            Craft::$app->getSession()->setError(Craft::t('enupal-socializer','Couldn’t save settings.'));

            // Send the settings back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'settings' => $settingsModel
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice($message);

        return $this->redirectToPostedUrl();
    }
}
