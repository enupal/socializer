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
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;

use enupal\socializer\records\Provider as ProviderRecord;
use craft\validators\UniqueValidator;
use enupal\socializer\elements\db\ProvidersQuery;
use yii\base\Model;

/**
 * Provider represents a provider element.
 *
 * @property $enupalMultiplePlans
 * @property $singlePlanInfo
 */
class Provider extends Element
{
    /**
     * @inheritdoc
     */
    public $id;

    /**
     * @var string Name.
     */
    public $name;

    /**
     * @var string Provider.
     */
    public $provider;

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
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'fieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => self::class
            ],
        ]);
    }

    public function init()
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
    public static function refHandle()
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
        return true;
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
    public function getFieldLayout()
    {
        $behaviors = $this->getBehaviors();
        $fieldLayout = $behaviors['fieldLayout'];

        return $fieldLayout->getFieldLayout();
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
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
    public function __toString()
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

        return $actions;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['name', 'provider'];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        $attributes = [
            'elements.dateCreated' => Craft::t('enupal-socializer','Date Created'),
            'name' => Craft::t('enupal-socializer','Name'),
            'provider' => Craft::t('enupal-socializer','Provider')
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
        $attributes['provider'] = ['label' => Craft::t('enupal-socializer','Provider')];
        $attributes['dateCreated'] = ['label' => Craft::t('enupal-socializer','Date Created')];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        $attributes = ['name', 'provider', 'dateCreated'];

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
    public function afterSave(bool $isNew)
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
        $record->provider = $this->provider;

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
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [];
        $rules[] = [['name', 'provider'], 'required'];
        $rules[] = [['name', 'provider'], 'string', 'max' => 255];
        $rules[] = [['name', 'provider'], UniqueValidator::class, 'targetClass' => ProviderRecord::class];
        $rules[] = [['name', 'provider'], 'required'];

        return $rules;
    }
}