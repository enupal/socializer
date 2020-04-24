<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\web\assets\fieldmapping;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class FieldMappingAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@enupal/socializer/web/assets/fieldmapping';

        $this->js = [
            'dist/js/fieldmapping.min.js'
        ];

        parent::init();
    }
}