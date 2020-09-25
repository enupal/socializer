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
use craft\fields\Dropdown;
use craft\fields\Email;
use craft\fields\Number;
use craft\fields\PlainText;
use craft\helpers\UrlHelper;
use enupal\socializer\elements\Provider;
use enupal\socializer\events\AfterLoginEvent;
use enupal\socializer\events\AfterRegisterUserEvent;
use enupal\socializer\events\BeforeLoginEvent;
use enupal\socializer\events\BeforeRegisterUserEvent;
use enupal\socializer\records\Provider as ProviderRecord;
use Hybridauth\Provider\Amazon;
use Hybridauth\Provider\Apple;
use Hybridauth\Provider\Authentiq;
use Hybridauth\Provider\BitBucket;
use Hybridauth\Provider\Blizzard;
use Hybridauth\Provider\Discord;
use Hybridauth\Provider\Disqus;
use Hybridauth\Provider\Dribbble;
use Hybridauth\Provider\Facebook;
use Hybridauth\Provider\Foursquare;
use Hybridauth\Provider\GitHub;
use Hybridauth\Provider\GitLab;
use Hybridauth\Provider\Instagram;
use Hybridauth\Provider\LinkedIn;
use Hybridauth\Provider\Mailru;
use Hybridauth\Provider\MicrosoftGraph;
use Hybridauth\Provider\Odnoklassniki;
use Hybridauth\Provider\ORCID;
use Hybridauth\Provider\QQ;
use Hybridauth\Provider\Reddit;
use Hybridauth\Provider\Slack;
use Hybridauth\Provider\Spotify;
use Hybridauth\Provider\StackExchange;
use Hybridauth\Provider\Steam;
use Hybridauth\Provider\SteemConnect;
use Hybridauth\Provider\Strava;
use Hybridauth\Provider\Telegram;
use Hybridauth\Provider\Tumblr;
use Hybridauth\Provider\TwitchTV;
use Hybridauth\Provider\Twitter;
use Hybridauth\Provider\Google;
use Hybridauth\Provider\Vkontakte;
use Hybridauth\Provider\WeChat;
use Hybridauth\Provider\WindowsLive;
use Hybridauth\Provider\WordPress;
use Hybridauth\Provider\Yahoo;
use Hybridauth\Provider\Yandex;
use Hybridauth\User\Profile;
use yii\base\Component;
use enupal\socializer\Socializer;
use yii\base\NotSupportedException;
use yii\db\Exception;

class Providers extends Component
{
    /**
     * @event BeforeLogin The event that is triggered before a user is logged in
     *
     * ```php
     * use enupal\socializer\events\BeforeLoginEvent;
     * use enupal\socializer\services\Providers;
     * use yii\base\Event;
     *
     * Event::on(Providers::class, Providers::EVENT_BEFORE_LOGIN, function(BeforeLoginEvent $e) {
     *      $user = $e->user;
     *      $userProfile = $e->userProfile;
     *      $provider = $e->provider;
     *      // set to false to cancel this action
     *      $e->isValid = true;
     *     // Do something
     * });
     * ```
     */
    const EVENT_BEFORE_LOGIN = 'beforeLoginUser';

    /**
     * @event AfterLogin The event that is triggered after a user is logged in
     *
     * ```php
     * use enupal\socializer\events\AfterLoginEvent;
     * use enupal\socializer\services\Providers;
     * use yii\base\Event;
     *
     * Event::on(Providers::class, Providers::EVENT_AFTER_LOGIN, function(AfterLoginEvent $e) {
     *      $user = $e->user;
     *      $userProfile = $e->userProfile;
     *      $provider = $e->provider;
     *     // Do something
     * });
     * ```
     */
    const EVENT_AFTER_LOGIN = 'afterLoginUser';

    /**
     * @event BeforeRegister The event that is triggered before a user is registered
     *
     * ```php
     * use enupal\socializer\events\BeforeRegisterUserEvent;
     * use enupal\socializer\services\Providers;
     * use yii\base\Event;
     *
     * Event::on(Providers::class, Providers::EVENT_BEFORE_REGISTER, function(BeforeRegisterUserEvent $e) {
     *      $user = $e->user;
     *      $userProfile = $e->userProfile;
     *      $provider = $e->provider;
     *      // set to false to cancel this action
     *      $e->isValid = true;
     *     // Do something
     * });
     * ```
     */
    const EVENT_BEFORE_REGISTER = 'beforeRegisterUser';

    /**
     * @event AfterLogin The event that is triggered after a user is registered
     *
     * ```php
     * use enupal\socializer\events\AfterRegisterUserEvent;
     * use enupal\socializer\services\Providers;
     * use yii\base\Event;
     *
     * Event::on(Providers::class, Providers::EVENT_AFTER_ORDER_COMPLETE, function(AfterRegisterUserEvent $e) {
     *      $user = $e->user;
     *      $user = $e->userProfile;
     *      $user = $e->provider;
     *     // Do something
     * });
     * ```
     */
    const EVENT_AFTER_REGISTER = 'afterRegisterUser';

    /**
     * @param $handle
     * @param $options
     * @return string
     * @throws NotSupportedException
     * @throws \yii\base\Exception
     */
    public function loginUrl($handle, $options = [])
    {
        $provider = $this->getProviderByHandle($handle);
        if (is_null($provider)){
            throw new NotSupportedException('Provider not found or disabled: '.$handle);
        }

        $options['provider'] = $handle;

        return UrlHelper::siteUrl('socializer/login', $options);
    }

    /**
     * @return array
     */
    public function getAllProviderTypes()
    {
        return [
            Apple::class,
            Amazon::class,
            Authentiq::class,
            BitBucket::class,
            Blizzard::class,
            Discord::class,
            Disqus::class,
            Dribbble::class,
            Facebook::class,
            Foursquare::class,
            GitHub::class,
            GitLab::class,
            Google::class,
            Instagram::class,
            LinkedIn::class,
            Mailru::class,
            MicrosoftGraph::class,
            Odnoklassniki::class,
            ORCID::class,
            Reddit::class,
            Slack::class,
            Spotify::class,
            StackExchange::class,
            Steam::class,
            Strava::class,
            SteemConnect::class,
            Telegram::class,
            Tumblr::class,
            TwitchTV::class,
            Twitter::class,
            Vkontakte::class,
            WeChat::class,
            WindowsLive::class,
            WordPress::class,
            Yandex::class,
            Yahoo::class,
            QQ::class
        ];
    }

    /**
     * @return array
     */
    public function getUserFieldsAsOptions()
    {
        $user = new User();
        $fields = $user->getFieldLayout()->getFields();
        $options = [[
            'label' => 'None',
            'value' => ''
        ]];

        foreach ($fields as $field) {
            if (!$this->validateFieldClass($field)){
                continue;
            }

            $option = [
                'label' => $field->name. ' ('.$field->handle.')',
                'value' => $field->handle
            ];

            $options[] = $option;
        }

        return $options;
    }

    /**
     * @param $field
     * @return bool
     */
    private function validateFieldClass($field)
    {
        $fieldClass = get_class($field);

        $supportedClasses = [
          PlainText::class => 1,
          Dropdown::class => 1
        ];

        if (isset($supportedClasses[$fieldClass])){
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getUserProfileFieldsAsOptions()
    {
        return [
            [
                'label' => 'Email',
                'value' => 'email',
                'compatibleCraftFields' => [
                    PlainText::class
                ]
            ],
            [
                'label' => 'Identifier',
                'value' => 'identifier',
                'compatibleCraftFields' => [
                    PlainText::class,
                    Dropdown::class
                ]
            ],
            [
                'label' => 'Profile URL',
                'value' => 'profileURL',
                'compatibleCraftFields' => [
                    PlainText::class
                ]
            ],
            [
                'label' => 'WebSite URL',
                'value' => 'webSiteURL',
                'compatibleCraftFields' => [
                    PlainText::class
                ]
            ],
            [
                'label' => 'Photo URL',
                'value' => 'photoURL',
                'compatibleCraftFields' => [
                ]
            ],
            [
                'label' => 'Display Name',
                'value' => 'displayName',
                'compatibleCraftFields' => [
                    PlainText::class,
                    Dropdown::class
                ]
            ],
            [
                'label' => 'Description',
                'value' => 'description',
                'compatibleCraftFields' => [
                    PlainText::class
                ]
            ],
            [
                'label' => 'First Name',
                'value' => 'firstName',
                'compatibleCraftFields' => [
                    PlainText::class
                ]
            ],
            [
                'label' => 'Last Name',
                'value' => 'lastName',
                'compatibleCraftFields' => [
                    PlainText::class
                ]
            ],
            [
                'label' => 'Gender',
                'value' => 'gender',
                'compatibleCraftFields' => [
                    PlainText::class,
                    Dropdown::class
                ]
            ],
            [
                'label' => 'Language',
                'value' => 'language',
                'compatibleCraftFields' => [
                    PlainText::class
                ]
            ],
            [
                'label' => 'Age',
                'value' => 'age',
                'compatibleCraftFields' => [
                    PlainText::class,
                    Number::class
                ]
            ],
            [
                'label' => 'Birth Day',
                'value' => 'birthDay',
                'compatibleCraftFields' => [
                    PlainText::class,
                    Number::class
                ]
            ],
            [
                'label' => 'Birth Month',
                'value' => 'birthMonth',
                'compatibleCraftFields' => [
                    PlainText::class,
                    Number::class
                ]
            ],
            [
                'label' => 'Birth Year',
                'value' => 'birthYear',
                'compatibleCraftFields' => [
                    PlainText::class,
                    Number::class
                ]
            ],
            [
                'label' => 'Email Verified',
                'value' => 'emailVerified',
                'compatibleCraftFields' => [
                    PlainText::class,
                    Email::class
                ]
            ],
            [
                'label' => 'Phone',
                'value' => 'phone',
                'compatibleCraftFields' => [
                    PlainText::class
                ]
            ],
            [
                'label' => 'Address',
                'value' => 'address',
                'compatibleCraftFields' => [
                    PlainText::class
                ]
            ],
            [
                'label' => 'Country',
                'value' => 'country',
                'compatibleCraftFields' => [
                    PlainText::class,
                    Dropdown::class
                ]
            ],
            [
                'label' => 'Region',
                'value' => 'region',
                'compatibleCraftFields' => [
                    PlainText::class,
                    Dropdown::class
                ]
            ],
            [
                'label' => 'City',
                'value' => 'city',
                'compatibleCraftFields' => [
                    PlainText::class,
                    Dropdown::class
                ]
            ],
            [
                'label' => 'Zip',
                'value' => 'zip',
                'compatibleCraftFields' => [
                    PlainText::class
                ]
            ],
            [
                'label' => 'Data (JSON)',
                'value' => 'data',
                'compatibleCraftFields' => [
                    PlainText::class
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getDefaultFieldMapping()
    {
        $userProfileFields = Socializer::$app->providers->getUserProfileFieldsAsOptions();
        $options = [];
        foreach ($userProfileFields as $item) {
            $option = [
                'sourceFormField' => $item['value'],
                'targetUserField' => ''
            ];
            $options[] = $option;
        }

        return $options;
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
     * Removes providers and related records from the database given the ids
     *
     * @param $providers
     *
     * @return bool
     * @throws \Throwable
     */
    public function deleteProviders($providers): bool
    {
        foreach ($providers as $key => $providerElement) {
            $provider = $this->getProviderById($providerElement->id);

            if ($provider) {
                $this->deleteProvider($provider);
            } else {
                Craft::error("Can't delete the payment form with id: {$providerElement->id}", __METHOD__);
            }
        }

        return true;
    }

    /**
     * @param Provider $provider
     *
     * @return bool
     * @throws \Throwable
     */
    public function deleteProvider(Provider $provider)
    {
        $transaction = Craft::$app->db->beginTransaction();

        try {
            // Delete the tokens
            $tokens = (new Query())
                ->select(['id'])
                ->from(["{{%enupalsocializer_tokens}}"])
                ->where(['providerId' => $provider->id])
                ->all();

            foreach ($tokens as $token) {
                Craft::$app->elements->deleteElementById($token['id']);
            }

            // Delete the Provider Element
            $success = Craft::$app->elements->deleteElementById($provider->id);

            if (!$success) {
                $transaction->rollback();
                Craft::error("Couldn’t delete Provider", __METHOD__);

                return false;
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();

            throw $e;
        }

        return true;
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
        try {
            $adapter->authenticate();
        } catch (\Exception $e){
            Craft::error("Unable to Authorize user", __METHOD__);
            return false;
        }

        $userProfile = $adapter->getUserProfile();
        $user = Craft::$app->getUser()->getIdentity();

        if (!$user){
            $user = $this->retrieveUser($userProfile, $provider);
        }

        if (!$user){
            Craft::error("Not user to login", __METHOD__);
            return false;
        }

        Socializer::$app->tokens->registerToken($user, $provider);

        $user = $this->triggerBeforeLoginUser($user, $provider, $userProfile);

        if (is_null($user)){
            Craft::error("User not valid to login on BeforeLoginEvent", __METHOD__);
            return false;
        }

        if (!Craft::$app->getUser()->login($user)) {
            Craft::error("Something went wrong while login craft user", __METHOD__);
            return false;
        }

        $this->triggerAfterLoginEvent($user, $provider, $userProfile);

        return true;
    }

    public function triggerBeforeLoginUser($user, $provider, $userProfile)
    {
        $event = new BeforeLoginEvent([
            'user' => $user,
            'provider' => $provider,
            'userProfile' => $userProfile,
        ]);

        $this->trigger(self::EVENT_BEFORE_LOGIN, $event);
        $user = $event->user;

        if (!$event->isValid) {
            return null;
        }

        return $user;
    }

    private function triggerAfterLoginEvent($user, $provider, $userProfile)
    {
        $event = new AfterLoginEvent([
            'user' => $user,
            'provider' => $provider,
            'userProfile' => $userProfile,
        ]);

        $this->trigger(self::EVENT_AFTER_LOGIN, $event);
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
        $settings = Socializer::$app->settings->getSettings();

        if (!$settings->enableUserSignUp){
            return null;
        }

        Craft::$app->requireEdition(Craft::Pro);

        $user = new User();
        $user->email = $userProfile->email;
        $user->username = $userProfile->email;
        $user->firstName = $userProfile->firstName;
        $user->lastName = $userProfile->lastName;

        // validate populate
        $user = $this->populateUserModel($user, $provider, $userProfile);

        $event = new BeforeRegisterUserEvent([
            'user' => $user,
            'provider' => $provider,
            'userProfile' => $userProfile,
        ]);

        $this->trigger(self::EVENT_BEFORE_REGISTER, $event);
        $user = $event->user;

        if (!$event->isValid) {
            return null;
        }

        if (!Craft::$app->elements->saveElement($user)){
            Craft::error("Unable to create user: ".json_encode($user->getErrors()));
            throw new \Exception("Something went wrong while creating the user");
        }

        if ($settings->userGroupId){
            $userGroup = Craft::$app->getUserGroups()->getGroupById($settings->userGroupId);
            if ($userGroup){
                Craft::$app->getUsers()->assignUserToGroups($user->id, [$userGroup->id]);
            }
        }

        $this->triggerAfterRegisterEvent($user, $provider, $userProfile);

        return $user;
    }

    private function triggerAfterRegisterEvent($user, $provider, $userProfile)
    {
        $event = new AfterRegisterUserEvent([
            'user' => $user,
            'provider' => $provider,
            'userProfile' => $userProfile,
        ]);

        $this->trigger(self::EVENT_AFTER_REGISTER, $event);
    }

    /**
     * @param User $user
     * @param Provider $provider
     * @param Profile $profile
     * @return User
     */
    public function populateUserModel(User $user, Provider $provider, Profile $profile)
    {
        $settings = Socializer::$app->settings->getSettings();

        if (!$settings->enableFieldMapping) {
            return $user;
        }

        $fieldMapping = Socializer::$app->settings->getGlobalFieldMapping();

        if ($settings->enableFieldMappingPerProvider) {
            $fieldMapping = $provider->fieldMapping ?? $fieldMapping;
        }

        foreach ($fieldMapping as $item) {
            if(isset($item['targetUserField']) && $item['targetUserField']){
                $profileValue = $profile->{$item['sourceFormField']};
                $field = $user->getFieldLayout()->getFieldByHandle($item['targetUserField']);
                if ($field){
                    $user->setFieldValue($item['targetUserField'], $profileValue);
                }
            }
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