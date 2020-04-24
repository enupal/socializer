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
        $user = new User();
        $user->email = $userProfile->email;
        $user->username = $userProfile->email;
        $user->firstName = $userProfile->firstName;
        $user->lastName = $userProfile->lastName;

        // validate populate
        $user = $this->populateUserModel($user, $provider, $userProfile);

        if (!Craft::$app->elements->saveElement($user)){
            Craft::error("Unable to create user: ".json_encode($user->getErrors()));
            throw new \Exception("Something went wrong while creating the user");
        }

        return $user;
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