<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */


namespace enupal\socializer\controllers;

use Craft;
use craft\helpers\UrlHelper;
use enupal\socializer\Socializer;
use enupal\socializer\controllers\FrontEndController;

class LoginController extends FrontEndController
{
    const SESSION_REDIRECT_URL = "socializer.redirectUrl";
    const SESSION_PROVIDER_HANDLE = "socializer.providerHandle";

    /**
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \craft\errors\MissingComponentException
     */
    public function actionLogin()
    {
        $providerHandle = Craft::$app->getRequest()->getParam('provider');
        $provider = Socializer::$app->providers->getProviderByHandle($providerHandle);
        $redirect = $this->getRedirectUrl();

        if (is_null($provider)) {
            throw new \Exception(Craft::t('enupal-socializer','Provider not found or disabled'));
        }

        $redirectUrl = $redirect ?? Craft::$app->getRequest()->referrer;
        Craft::$app->getSession()->set(self::SESSION_REDIRECT_URL, $redirectUrl);
        Craft::$app->getSession()->set(self::SESSION_PROVIDER_HANDLE, $providerHandle);
        $adapter = $provider->getAdapter();

        try {
            if ($adapter->authenticate()){
                if (!Socializer::$app->providers->loginOrRegisterUser($provider)){
                    Craft::$app->getSession()->setError(Craft::t('enupal-socializer', "Unable to authenticate user"));
                }
            }
        }
        catch (\Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
            throw new \Exception($e->getMessage());
        }

        return $this->handleRedirect();
    }

    /**
     * @return mixed|string
     * @throws \yii\base\Exception
     */
    private function getRedirectUrl()
    {
        $redirect = Craft::$app->getRequest()->getParam('redirect');

        if ($redirect){
            $redirect = UrlHelper::siteUrl($redirect);
        }

        return $redirect;
    }

    /**
     * @return \yii\web\Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\SyntaxError
     * @throws \craft\errors\MissingComponentException
     */
    private function handleRedirect()
    {
        $redirectUrl = Craft::$app->getSession()->get(self::SESSION_REDIRECT_URL);
        $user = Craft::$app->getUser()->getIdentity();

        if ($user){
            $variables['user'] = $user;
            $redirectUrl = Craft::$app->getView()->renderString($redirectUrl, $variables);
        }

        $this->restoreSession();

        return $this->redirect($redirectUrl);
    }

    /**
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \craft\errors\MissingComponentException
     * @throws \craft\errors\WrongEditionException
     * @throws \yii\base\Exception
     */
    public function actionCallback()
    {
        $providerHandle = Craft::$app->getSession()->get(self::SESSION_PROVIDER_HANDLE);
        $provider = Socializer::$app->providers->getProviderByHandle($providerHandle);

        if (is_null($provider)){
            throw new \Exception(Craft::t('enupal-socializer','Provider not found or disabled'));
        }

        if (!Socializer::$app->providers->loginOrRegisterUser($provider)){
            Craft::$app->getSession()->setError(Craft::t('enupal-socializer', "Unable to authenticate user"));
        }

        return $this->handleRedirect();
    }

    /**
     * @throws \craft\errors\MissingComponentException
     */
    private function restoreSession()
    {
        Craft::$app->getSession()->remove(self::SESSION_REDIRECT_URL);
        Craft::$app->getSession()->remove(self::SESSION_PROVIDER_HANDLE);
    }
}