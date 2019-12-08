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

class ProvidersQuery extends ElementQuery
{

    // General - Properties
    // =========================================================================
    public $id;
    public $dateCreated;
    public $name;
    public $provider;
    public $clientId;
    public $clientSecret;
    public $fieldMapping;

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
    public function provider($value)
    {
        $this->provider = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @inheritdoc
     */
    public function name($value)
    {
        $this->name = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function __construct($elementType, array $config = [])
    {
        // Default orderBy
        if (!isset($config['orderBy'])) {
            $config['orderBy'] = 'enupalsocializer_providers.dateCreated';
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
        $this->joinElementTable('enupalsocializer_providers');

        if (is_null($this->query)){
            return false;
        }

        $this->query->select([
            'enupalsocializer_providers.id',
            'enupalsocializer_providers.name',
            'enupalsocializer_providers.provider',
            'enupalsocializer_providers.clientId',
            'enupalsocializer_providers.clientSecret',
            'enupalsocializer_providers.fieldMapping'
        ]);

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam(
                'enupalsocializer_providers.name', $this->name)
            );
        }

        if ($this->provider) {
            $this->subQuery->andWhere(Db::parseParam(
                'enupalsocializer_providers.provider', $this->provider)
            );
        }

        if ($this->orderBy !== null && empty($this->orderBy) && !$this->structureId && !$this->fixedOrder) {
            $this->orderBy = 'dateCreated desc';
        }

        return parent::beforePrepare();
    }
}
