<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */


namespace enupal\socializer\controllers;

use Craft;
use craft\elements\Asset;
use craft\helpers\UrlHelper;
use craft\web\Controller as BaseController;
use enupal\socializer\elements\Provider as ProviderElement;
use enupal\socializer\Socializer;
use yii\web\NotFoundHttpException;

class ProvidersController extends BaseController
{
    /**
     * Save a Provider
     *
     * @return null|\yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveProvider()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $provider = new ProviderElement;

        $providerId = $request->getBodyParam('providerId');

        if ($providerId) {
            $provider = Socializer::$app->providers->getProviderById($providerId);
        }

        $provider = Socializer::$app->providers->populateProviderFromPost($provider);

        // Save it
        if (!Socializer::$app->providers->saveProvider($provider)) {
            Craft::$app->getSession()->setError(Craft::t('enupal-socializer','Couldn’t save provider'));

            Craft::$app->getUrlManager()->setRouteParams([
                    'provider' => $provider
                ]
            );

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('enupal-socializer','Provider saved.'));

        return $this->redirectToPostedUrl($provider);
    }

    /**
     * Edit a Provider
     *
     * @param int|null $providerId The button's ID, if editing an existing button.
     * @param ProviderElement|null $provider   The provider send back by setRouteParams if any errors on saveProvider
     *
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionEditProvider(int $providerId = null, ProviderElement $provider = null)
    {
        // Immediately create a new Provider
        if ($providerId === null) {
            $request = Craft::$app->getRequest();
            $providerType = $request->getRequiredBodyParam("providerType");
            $provider = Socializer::$app->providers->getProviderByType($providerType);
            if ($provider){
                throw new \Exception(Craft::t('enupal-socializer','Provider '.$provider->name.' already exists'));
            }
            $providerName = Socializer::$app->providers->getClassNameFromNamespace($providerType);
            $providerHandle = lcfirst($providerName);
            $provider = Socializer::$app->providers->createNewProvider($providerName, $providerHandle, $providerType);

            if ($provider->id) {
                $url = UrlHelper::cpUrl('enupal-socializer/providers/edit/'.$provider->id);
                return $this->redirect($url);
            } else {
                $errors = $provider->getErrors();
                throw new \Exception(Craft::t('enupal-socializer','Error creating the Provider'.json_encode($errors)));
            }
        } else {
            if ($providerId !== null) {
                if ($provider === null) {
                    // Get the provider
                    $provider = Socializer::$app->providers->getProviderById($providerId);

                    if (!$provider) {
                        throw new NotFoundHttpException(Craft::t('enupal-socializer','Provider not found'));
                    }
                }
            }
        }

        $variables['providerId'] = $providerId;
        $variables['provider'] = $provider;

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = 'enupal-socializer/providers/edit/{id}';

        $variables['settings'] = Socializer::$app->settings->getSettings();
        $variables['apiDocumentation'] = $this->getApiDocumentation($provider->type);

        return $this->renderTemplate('enupal-socializer/providers/_edit', $variables);
    }

    /**
     * Delete a Provider.
     *
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteProvider()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $providerId = $request->getRequiredBodyParam('providerId');
        $provider = Socializer::$app->providers->getProviderById($providerId);

        // @TODO - handle errors
        Socializer::$app->providers->deleteProvider($provider);

        Craft::$app->getSession()->setNotice(Craft::t('enupal-socializer','Provider deleted.'));

        return $this->redirectToPostedUrl($provider);
    }

    /**
     * @param $providerType
     * @return mixed
     * @throws \ReflectionException
     */
    private function getApiDocumentation($providerType)
    {
        $reflection = new \ReflectionClass($providerType);
        $property = $reflection->getProperty('apiDocumentation');
        $property->setAccessible(true);
        $obj = new $providerType(['callback' => 'https://example.com/path/to/script.php',"keys"=>["key" => "ads", "secret"=>"test"]]);

        return $property->getValue($obj);
    }
}
