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
use Hybridauth\Provider\Facebook;
use Hybridauth\Provider\LinkedIn;
use Hybridauth\Provider\Twitter;
use Hybridauth\Provider\Google;
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
            LinkedIn::class
        ];
    }

    /**
     * @return array
     */
    public function getProviderTypesAsOptions()
    {
        $providers = $this->getAllProviderTypes();
        $asOptions = [];
        foreach ($providers as $provider) {
            $option = [
                "label" => get_class($provider)
            ];
            $asOptions[] = $option;
        }

        return $asOptions;
    }
}