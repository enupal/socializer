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
use enupal\socializer\services\Providers;
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

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getProvidersAsOptions()
    {
        return Socializer::$app->providers->getProviderTypesAsOptions();
    }

    /**
     * @return bool|\craft\base\Model|null
     */
    public function getSettings()
    {
        return Socializer::getInstance()->getSettings();
    }

    /**
     * @return \enupal\socializer\services\App
     */
    public function app()
    {
        return Socializer::$app;
    }

    /**
     * @param $handle
     * @param $options
     * @return string
     * @throws \yii\base\Exception
     * @throws \yii\base\NotSupportedException
     */
    public function loginUrl($handle, $options = [])
    {
        return Socializer::$app->providers->loginUrl($handle, $options);
    }
}