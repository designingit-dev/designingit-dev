<?php

namespace keiser\commercehelpers\console\controllers;

use Craft;
use craft\elements\Asset;
use craft\helpers\App;

use keiser\commercehelpers\jobs\WinMagiPushJob;
use keiser\commercehelpers\Plugin;

class CloudinaryController extends \yii\console\Controller
{
    public function actionOptimize($fileName)
    {
        Plugin::getInstance()->keiserCommerceHelpers->optimizeImage($fileName);
    }

    public function actionOptimizeVolume($volumeHandle){
        App::maxPowerCaptain();
        $volumes = Craft::$app->getVolumes();
        $volume = $volumes->getVolumeByHandle($volumeHandle);

        Craft::$app->getTemplateCaches()->deleteCachesByElementType(Asset::class);

        $allowedMimeTypes = [
            'image/jpeg',
            'image/gif',
            'image/png'
        ];

        $query = Asset::find();
        $criteria = [
           'siteId' => Craft::$app->getSites()->getPrimarySite()->id,
           'volumeId' => $volume->id
        ];
        Craft::configure($query, $criteria);
        $query
            ->offset(null)
            ->limit(null)
            ->orderBy('id desc');

        $totalElements = $query->count();

        echo $totalElements . PHP_EOL;
        $assets = Craft::$app->getAssets();
        /** @var Asset $asset */
        foreach ($query->each() as $asset) {
            if(in_array($asset->getMimeType(), $allowedMimeTypes)){
                $optimized = Plugin::getInstance()->keiserCommerceHelpers->optimizeCloudImage($asset->getUrl(), $asset->getFilename());
                if($optimized){
                    try {
                        $assets->replaceAssetFile($asset, getenv('CLOUDINARY_TEMP_FILE_PATH') . '/' . $asset->getFilename(), $asset->getFilename());
                    } catch(\Exception $e){
                        Craft::error($e->getMessage());
                    }
                }
            }
        }
    }

    public function actionOptimizeCloudImage($fileUrl){
        Plugin::getInstance()->keiserCommerceHelpers->optimizeCloudImage($fileUrl, 'optimisedimage.jpg');
    }

    public function actionRetryWinmagiPush($orderIds){
        $orderIds = explode(',', $orderIds);
        foreach($orderIds as $orderId)
        Craft::$app->queue->push(new WinMagiPushJob([
            'orderId' => (int)$orderId
        ]));
    }
}
