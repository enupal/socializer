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
use craft\elements\db\ElementQueryInterface;

use craft\elements\User;
use enupal\socializer\records\Token as TokenRecord;
use enupal\socializer\elements\db\TokensQuery;
use yii\base\Model;

/**
 * Token represents a token element.
 *
 */
class Token extends Element
{
    /**
     * @var int
     */
    public $userId;

    /**
     * @var int
     */
    public $providerId;

    /**
     * @var string
     */
    public $accessToken;

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

        if ($this->accessToken && is_string($this->accessToken)) {
            $this->accessToken = json_decode($this->accessToken, true);
        }
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
        return false;
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
     *
     * @return TokensQuery The newly created [[TokensQuery]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new TokensQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['userId', 'providerId'];
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
        $record = new TokenRecord();

        if (!$isNew) {
            $record = TokenRecord::findOne($this->id);

            if (!$record) {
                throw new \Exception('Invalid Token ID: ' . $this->id);
            }
        } else {
            $record->id = $this->id;
        }

        $record->userId = $this->userId;
        $record->providerId = $this->providerId;
        $record->accessToken = is_array($this->accessToken) ? json_encode($this->accessToken) : $this->accessToken;

        $record->save(false);

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = [];
        $rules[] = [['userId', 'providerId'], 'required'];

        return $rules;
    }
}