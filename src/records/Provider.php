<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\records;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;
use craft\records\Element;
use craft\db\SoftDeleteTrait;

/**
 * Class Provider record.
 * @property int    $id
 * @property string $name
 * @property string $type
 * @property string $handle
 * @property string $clientId
 * @property string $clientSecret
 * @property string $fieldMapping
 */

class Provider extends ActiveRecord
{
    use SoftDeleteTrait;

    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%enupalsocializer_providers}}';
    }

    /**
     * Returns the entry’s element.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }
}