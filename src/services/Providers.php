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
use craft\elements\User;
use enupal\socializer\elements\Provider;
use enupal\socializer\records\Provider as ProviderRecord;
use Hybridauth\Provider\Discord;
use Hybridauth\Provider\Facebook;
use Hybridauth\Provider\LinkedIn;
use Hybridauth\Provider\Twitter;
use Hybridauth\Provider\Google;
use Hybridauth\User\Profile;
use yii\base\Component;
use enupal\socializer\Socializer;
use yii\db\Exception;

class Providers extends Component
{
    /**
     * @return array
     */
    public function getAllProviderTypes()
    {
        return [
            Facebook::class,
            Twitter::class,
            Google::class,
            LinkedIn::class,
            Discord::class
        ];
    }

    /**
     * @param bool $excludeCreated
     * @return array
     * @throws \ReflectionException
     */
    public function getProviderTypesAsOptions($excludeCreated = true)
    {
        $providers = $this->getAllProviderTypes();

        if ($excludeCreated){
            $providers = $this->getExcludeCreatedProviders();
        }

        $asOptions = [];
        foreach ($providers as $provider) {
            $option = [
                "label" => $this->getClassNameFromNamespace($provider),
                "value" => $provider
            ];
            $asOptions[] = $option;
        }

        return $asOptions;
    }

    /**
     * @return array
     */
    public function getExcludeCreatedProviders()
    {
        $providerTypes = $this->getAllProviderTypes();
        $providers = (new Query())
            ->select(['type'])
            ->from(["{{%enupalsocializer_providers}}"])
            ->all();

        foreach ($providers as $provider) {
            if (($key = array_search($provider["type"], $providerTypes)) !== false) {
                unset($providerTypes[$key]);
            }
        }

        return $providerTypes;
    }

    /**
     * @param $class
     * @return string
     * @throws \ReflectionException
     */
    public function getClassNameFromNamespace($class)
    {
        return (new \ReflectionClass($class))->getShortName();
    }

    /**
     * Returns a Provider model if one is found in the database by id
     *
     * @param int $id
     * @param int $siteId
     *
     * @return null|Provider
     */
    public function getProviderById(int $id, int $siteId = null)
    {
        /** @var Provider $provider */
        $provider = Craft::$app->getElements()->getElementById($id, Provider::class, $siteId);

        return $provider;
    }

    /**
     * @param string $handle
     * @param int|null $siteId
     * @return array|Provider|null
     */
    public function getProviderByHandle(string $handle, int $siteId = null)
    {
        $query = Provider::find();
        $query->handle($handle);
        $query->siteId($siteId);

        return $query->one();
    }

    /**
     * @param string $type
     * @param int|null $siteId
     * @return array|Provider|null
     */
    public function getProviderByType(string $type, int $siteId = null)
    {
        $query = Provider::find();
        $query->type($type);
        $query->siteId($siteId);

        return $query->one();
    }

    /**
     * @param string $name
     * @param string $handle
     * @param string $type
     *
     * @return Provider
     * @throws \Exception
     * @throws \Throwable
     */
    public function createNewProvider(string $name, string $handle, string $type): Provider
    {
        $provider = new Provider();

        $provider->name = $name;
        $provider->handle = $handle;
        $provider->type = $type;
        $provider->enabled = 0;

        $this->saveProvider($provider);

        return $provider;
    }

    /**
     * @param Provider $provider
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \craft\errors\MissingComponentException
     * @throws \craft\errors\WrongEditionException
     * @throws \yii\base\Exception
     */
    public function loginOrRegisterUser(Provider $provider)
    {
        $adapter = $provider->getAdapter();
        $adapter->authenticate();

        $userProfile = $adapter->getUserProfile();
        $user = Craft::$app->getUser()->getIdentity();

        if (!$user){
            $user = $this->retrieveUser($userProfile, $provider);
        }

        Socializer::$app->tokens->registerToken($user, $provider);

        if (!Craft::$app->getUser()->login($user)) {
            Craft::error("Something went wrong while login craft user", __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * Register or get existing user
     * @param Profile $userProfile
     * @param Provider $provider
     * @return User
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \craft\errors\WrongEditionException
     * @throws \yii\base\Exception
     */
    private function retrieveUser(Profile $userProfile, Provider $provider): User
    {
        if (is_null($userProfile->email)){
            throw new \Exception("Email address is not provided, please check the settings of your application");
        }

        $user = Craft::$app->users->getUserByUsernameOrEmail($userProfile->email);

        if ($user) {
            return $user;
        }

        Craft::$app->requireEdition(Craft::Pro);
        // @todo add field mapping and use provider passed as param
        $user = new User();
        $user->email = $userProfile->email;
        $user->username = $userProfile->email;
        $user->firstName = $userProfile->firstName;
        $user->lastName = $userProfile->lastName;

        if (!Craft::$app->elements->saveElement($user)){
            Craft::error("Unable to create user: ".json_encode($user->getErrors()));
            throw new \Exception("Something went wrong while creating the user");
        }

        return $user;
    }

    /**
     * @param $provider Provider
     *
     * @return bool
     * @throws Exception
     * @throws \Throwable
     */
    public function saveProvider(Provider $provider)
    {
        if ($provider->id) {
            $providerRecord = ProviderRecord::findOne($provider->id);

            if (!$providerRecord) {
                throw new Exception(Craft::t("enupal-socializer",'No Provider exists with the ID “{id}”', ['id' => $provider->id]));
            }
        }

        if (!$provider->validate()) {
            return false;
        }

        $transaction = Craft::$app->db->beginTransaction();

        try {
            // Set the field context
            Craft::$app->content->fieldContext = $provider->getFieldContext();

            if (Craft::$app->elements->saveElement($provider)) {
                $transaction->commit();
            }
        } catch (\Exception $e) {
            $transaction->rollback();

            throw $e;
        }

        return true;
    }

    /**
     * @param Provider $provider
     *
     * @return Provider
     */
    public function populateProviderFromPost(Provider $provider)
    {
        $request = Craft::$app->getRequest();

        $postFields = $request->getBodyParam('fields');

        $provider->setAttributes(/** @scrutinizer ignore-type */
            $postFields, false);

        return $provider;
    }
}