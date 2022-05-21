<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class TokensQuery extends ElementQuery
{

    // General - Properties
    // =========================================================================
    public $userId;
    public $providerId;
    public $accessToken;

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        parent::__set($name, $value);
    }

    /**
     * @inheritdoc
     */
    public function userId($value)
    {
        $this->userId = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @inheritdoc
     */
    public function providerId($value)
    {
        $this->providerId = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProviderId()
    {
        return $this->providerId;
    }

    /**
     * @inheritdoc
     */
    public function __construct($elementType, array $config = [])
    {
        // Default orderBy
        if (!isset($config['orderBy'])) {
            $config['orderBy'] = 'enupalsocializer_tokens.dateCreated';
        }

        parent::__construct($elementType, $config);
    }


    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('enupalsocializer_tokens');

        if (is_null($this->query)){
            return false;
        }

        $this->query->select([
            'enupalsocializer_tokens.id',
            'enupalsocializer_tokens.userId',
            'enupalsocializer_tokens.accessToken',
            'enupalsocializer_tokens.providerId'
        ]);

        if ($this->userId) {
            $this->subQuery->andWhere(Db::parseParam(
                'enupalsocializer_tokens.userId', $this->userId)
            );
        }

        if ($this->providerId) {
            $this->subQuery->andWhere(Db::parseParam(
                'enupalsocializer_tokens.providerId', $this->providerId)
            );
        }

        if ($this->orderBy !== null && empty($this->orderBy) && !$this->structureId && !$this->fixedOrder) {
            $this->orderBy = 'dateCreated desc';
        }

        return parent::beforePrepare();
    }
}
