<?php
namespace keiser\downloadassets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class DownloadAssetsBundle extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@keiser/downloadassets/assetbundles/download-assets/dist';

        $this->depends = [
            CpAsset::class
        ];

        $this->js = [
            'js/app.min.js'
        ];

        $this->css = [
            'css/app.min.css'
        ];

        parent::init();
    }
}
