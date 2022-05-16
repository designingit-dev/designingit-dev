<?php

namespace keiser\logviewer;

use craft\web\assets\cp\CpAsset;

class AssetBundle extends \craft\web\AssetBundle {

    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@keiser/logviewer/resources';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/LogViewer_Script.js',
        ];

        $this->css = [
            'css/LogViewer_Style.css',
        ];

        parent::init();
    }

}