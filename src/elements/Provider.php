<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\elements;

use Craft;
use craft\base\Element;
use craft\behaviors\FieldLayoutBehavior;
use craft\elements\actions\Restore;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\UrlHelper;

use enupal\socializer\elements\actions\Delete;
use enupal\socializer\records\Provider as ProviderRecord;
use craft\validators\UniqueValidator;
use enupal\socializer\elements\db\ProvidersQuery;
use enupal\socializer\Socializer;
use enupal\socializer\validators\EnabledValidator;
use Hybridauth\Adapter\AdapterInterface;
use Hybridauth\Provider\Apple;
use yii\base\Model;

/**
 * Provider represents a provider element.
 *
 */
class Provider extends Element
{
    /**
     * @var string Name.
     */
    public $name;

    /**
     * @var string Handle.
     */
    public $handle;

    /**
     * @var string Type.
     */
    public $type;

    /**
     * @var string Client ID.
     */
    public $clientId;

    /**
     * @var string Client Secret.
     */
    public $clientSecret;

    /**
     * @var string Field Mapping
     */
    public $fieldMapping;

    /**
     * @inheritdoc
     */
    public function canView(User $user): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'fieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => self::class
            ],
        ]);
    }

    public function init(): void
    {
        parent::init();

        $this->setScenario(Model::SCENARIO_DEFAULT);

        if ($this->fieldMapping && is_string($this->fieldMapping)) {
            $this->fieldMapping = json_decode($this->fieldMapping, true);
        }
    }

    /**
     * Returns the field context this element's content uses.
     *
     * @access protected
     * @return string
     */
    public function getFieldContext(): string
    {
        return 'enupalSocializer:' . $this->id;
    }

    /**
     * Returns the element type name.
     *
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('enupal-socializer','Socializer');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle(): ?string
    {
        return 'socializer-providers';
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout(): ?\craft\models\FieldLayout
    {
        $behaviors = $this->getBehaviors();
        $fieldLayout = $behaviors['fieldLayout'];

        return $fieldLayout->getFieldLayout();
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl(): ?string
    {
        return UrlHelper::cpUrl(
            'enupal-socializer/providers/edit/' . $this->id
        );
    }

    /**
     * Use the name as the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     *
     * @return ProvidersQuery The newly created [[ProvidersQuery]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new ProvidersQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('enupal-socializer','All Providers'),
            ]
        ];

        // @todo add groups

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        // Delete
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => Delete::class
        ]);

        $actions[] = Craft::$app->getElements()->createAction([
            'type' => Restore::class,
            'successMessage' => 'Providers restored'
        ]);

        return $actions;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['name', 'type'];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        $attributes = [
            'elements.dateCreated' => Craft::t('enupal-socializer','Date Created'),
            'name' => Craft::t('enupal-socializer','Name')
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [];
        $attributes['name'] = ['label' => Craft::t('enupal-socializer','Name')];
        $attributes['handle'] = ['label' => Craft::t('enupal-socializer','Handle')];
        $attributes['dateCreated'] = ['label' => Craft::t('enupal-socializer','Date Created')];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        $attributes = ['name', 'handle' , 'dateCreated'];

        return $attributes;
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidConfigException
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'dateCreated':
                {
                    return $this->dateCreated->/** @scrutinizer ignore-call */ format("Y-m-d H:i");
                }
        }

        return parent::tableAttributeHtml($attribute);
    }

    /**
     * @inheritdoc
     */
    public function datetimeAttributes(): array
    {
        $attributes = parent::datetimeAttributes();
        $attributes[] = 'dateCreated';
        return $attributes;
    }

    /**
     * @inheritdoc
     * @param bool $isNew
     * @throws \Exception
     */
    public function afterSave(bool $isNew): void
    {
        $record = new ProviderRecord();

        if (!$isNew) {
            $record = ProviderRecord::findOne($this->id);

            if (!$record) {
                throw new \Exception('Invalid Provider ID: ' . $this->id);
            }
        } else {
            $record->id = $this->id;
        }

        $record->name = $this->name;
        $record->type = $this->type;
        $record->handle = $this->handle;

        $record->clientId = $this->clientId;
        $record->clientSecret = $this->clientSecret;
        $record->fieldMapping = $this->fieldMapping;

        if (is_array($record->fieldMapping)){
            $record->fieldMapping = json_encode($record->fieldMapping);
        };

        $record->save(false);

        parent::afterSave($isNew);
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return new $this->type($this->getProviderConfig());
    }

    /**
     * @return array
     */
    private function getProviderConfig()
    {
        // @todo add event to give a chance to update default config
        $config = [
            'callback' => Socializer::$app->settings->getCallbackUrl(),
            'keys' => [
                'id' => $this->getClientId(),
                'secret' => $this->getClientSecret()
            ],
            'includeEmail' => true
        ];

        if ($this->type === Apple::class && Socializer::$app->settings->validateAppleSettings()) {
            $configSettings = Socializer::$app->settings->getConfigSettings();

            if (isset($configSettings['apple'])) {
                $config = $configSettings['apple'];
                $config['callback'] = Socializer::$app->settings->getCallbackUrl();
            }
        }

        return $config;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return Craft::parseEnv($this->clientId);
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return Craft::parseEnv($this->clientSecret);
    }

    /**
     * @return array|null
     */
    public function getCurrentUserToken()
    {
        return Socializer::$app->tokens->getCurrentUserToken($this->id);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['name', 'type'], 'required'];
        $rules[] = [['name', 'type'], 'string', 'max' => 255];
        $rules[] = [['name', 'type'], UniqueValidator::class, 'targetClass' => ProviderRecord::class];
        $rules[] = [['name', 'type'], 'required'];
        $rules[] = [['clientId'], EnabledValidator::class];

        return $rules;
    }

    public function isAppleProvider()
    {
        return $this->type === Apple::class;
    }
}