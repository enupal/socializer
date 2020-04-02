<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\variables;

use enupal\socializer\elements\db\ProvidersQuery;
use enupal\socializer\elements\Provider;
use Craft;
use enupal\socializer\Socializer;
use yii\base\Behavior;

/**
 * Socializer provides an API for accessing information about stripe buttons. It is accessible from templates via `craft.socializer`.
 *
 */
class SocializerVariable extends Behavior
{
    /**
     * @var Provider
     */
    public $providers;

    /**
     * Returns a new OrderQuery instance.
     *
     * @param mixed $criteria
     * @return ProvidersQuery
     */
    public function providers($criteria = null): ProvidersQuery
    {
        $query = Provider::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    }

    public function getProvidersAsOptions()
    {
        Socializer::$app->providers->getProviderTypesAsOptions();
    }
}