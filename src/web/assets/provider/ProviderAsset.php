<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\web\assets\provider;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class ProviderAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@enupal/socializer/web/assets/provider';

        // define the dependencies
        $this->depends = [
            CpAsset::class
        ];

        $this->js = [
            'dist/TableRowAdditionalInfoIcon.js'
        ];

        parent::init();
    }
}