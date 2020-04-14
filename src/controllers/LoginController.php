<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */


namespace enupal\socializer\controllers;

use Craft;
use enupal\socializer\Socializer;
use enupal\stripe\controllers\FrontEndController;
use Hybridauth\HttpClient\Util;

class LoginController extends FrontEndController
{
    /**
     * @return \yii\web\Response
     * @throws \craft\errors\MissingComponentException
     */
    public function actionLogin()
    {
        $providerHandle = Craft::$app->getRequest()->getParam('provider');
        $provider = Socializer::$app->providers->getProviderByHandle($providerHandle);

        if (is_null($provider)) {
            throw new \Exception(Craft::t('enupal-socializer','Provider not found or disabled'));
        }

        $redirectUrl = Craft::$app->getRequest()->referrer;
        Craft::$app->getSession()->set('socializer.redirectUrl', Craft::$app->getRequest()->referrer);
        Craft::$app->getSession()->set('socializer.providerHandle', $providerHandle);
        $adapter = $provider->getAdapter();

        try {
            if ($adapter->authenticate()){
                $adapter->disconnect();
            }
        }
        catch (\Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
            throw new \Exception($e->getMessage());
        }

        return $this->redirect($redirectUrl);
    }

    public function actionCallback()
    {
        $redirectUrl = Craft::$app->getSession()->get('socializer.redirectUrl');
        $providerHandle = Craft::$app->getSession()->get('socializer.providerHandle');

        $provider = Socializer::$app->providers->getProviderByHandle($providerHandle);

        if (is_null($provider)){
            throw new \Exception(Craft::t('enupal-socializer','Provider not found or disabled'));
        }

        $adapter = $provider->getAdapter();

        if (!$adapter->authenticate()){
            throw new \Exception(Craft::t('enupal-socializer','Unable to Authenticate'));
        }


        Socializer::$app->providers->loginOrRegisterUser($adapter, $provider);

        return $this->redirect($redirectUrl);
    }
}