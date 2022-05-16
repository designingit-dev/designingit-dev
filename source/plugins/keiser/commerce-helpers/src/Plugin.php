<?php

namespace keiser\commercehelpers;

use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\base\Element;
use craft\commerce\adjusters\Discount;
use craft\commerce\elements\Order;
use craft\commerce\events\AddressEvent;
use craft\commerce\events\DiscountAdjustmentsEvent;
use craft\commerce\events\LineItemEvent;
use craft\commerce\events\OrderStatusEvent;
use craft\commerce\models\LineItem;
use craft\commerce\models\OrderHistory;
use craft\elements\Asset;
use craft\events\AssetEvent;
use craft\events\ElementEvent;
use craft\events\ModelEvent;
use craft\events\ReplaceAssetEvent;
use craft\events\SetAssetFilenameEvent;
use craft\helpers\Assets as AssetsHelper;
use craft\helpers\Image;
use craft\services\Assets;
use craft\web\twig\variables\CraftVariable;
use keiser\commercehelpers\jobs\KeiserDataServerPushJob;
use keiser\commercehelpers\services\KeiserCommerceHelpersService;
use keiser\commercehelpers\services\WinmagiShippingDataService;
use keiser\commercehelpers\web\twig\KeiserCommerceHelpersExtension;
use yii\base\Event;
use craft\mail\Message;
use keiser\commercehelpers\jobs\WinMagiPushJob;
use keiser\commercehelpers\jobs\MailchimpSubscribeJob;
use keiser\commercehelpers\integrations\sproutforms\fields\KeiserLightswitchField;
use keiser\commercehelpers\integrations\sproutforms\fields\KeiserRichTextField;

class Plugin extends \craft\base\Plugin {

    public function init()
    {
        parent::init();

        $this->setComponents([
            'keiserCommerceHelpers' => \keiser\commercehelpers\services\KeiserCommerceHelpersService::class,
            'winmagiShippingData'   => \keiser\commercehelpers\services\WinmagiShippingDataService::class
        ]);
        $this->_addTwigExtensions();

        Event::on(\craft\commerce\elements\Order::class, \craft\commerce\elements\Order::EVENT_AFTER_COMPLETE_ORDER, function(Event $event){
            $order = $event->sender;
            if ($order->email) {
                Craft::$app->queue->push(new MailchimpSubscribeJob([
                    'orderId' => $order->id
                ]));
            }
            Craft::$app->queue->push(new WinMagiPushJob([
                'orderId' => $order->id
            ]));
            Craft::$app->queue->push(new KeiserDataServerPushJob([
                'orderId' => $order->id
            ]));
        });

        Event::on(\craft\commerce\services\OrderHistories::class, \craft\commerce\services\OrderHistories::EVENT_ORDER_STATUS_CHANGE, function(OrderStatusEvent $event){
            /**
             * @var Order $order
             */
            $order = $event->order;
            /**
             * @var OrderHistory $orderHistory
             */
            $orderHistory = $event->orderHistory;
            if($order->email && $orderHistory->newStatus->handle == 'shipped' && !empty($order->shipmentTrackingNumber)){
                $this->keiserCommerceHelpers->mailChimpSubscribe($order, $order->shipmentTrackingNumber);
            }
        });

        Event::on(\craft\services\Elements::class, \craft\services\Elements::EVENT_AFTER_SAVE_ELEMENT, function(ElementEvent $event){
            $element = $event->element;
            if(get_class($element) === 'craft\commerce\elements\Order') {
                $order = $element;
                $this->keiserCommerceHelpers->sendOrderShippedEmail($order);
                $this->keiserCommerceHelpers->checkIfAdminHasPushedOrderToWinmagi($order);
            }
            return true;
        });

        Event::on(\barrelstrength\sproutforms\elements\Entry::class, \barrelstrength\sproutforms\elements\Entry::EVENT_AFTER_SAVE, function(Event $event){
            /**
             * @var \barrelstrength\sproutforms\elements\Entry $sproutFormsEntry
             */
            $sproutFormsEntry = $event->sender;
            if($sproutFormsEntry){
                $form = $sproutFormsEntry->getForm();
                if($form->handle == 'productReview'){
                    if (Craft::$app->request->isSiteRequest){
                        $this->keiserCommerceHelpers->updateVerifiedPurchase($sproutFormsEntry);
                    }
                    $this->keiserCommerceHelpers->updateVerifiedPurchase($sproutFormsEntry);
                    $this->keiserCommerceHelpers->updateAuthToken($sproutFormsEntry);
                    $this->keiserCommerceHelpers->saveProductReviewToNetworkedWebsites($sproutFormsEntry);
                    $this->keiserCommerceHelpers->notifyCustomerAboutReviewPublished($sproutFormsEntry);
                }
            }
            return true;
        });

        Event::on(\craft\commerce\elements\Order::class, \craft\commerce\elements\Order::EVENT_BEFORE_COMPLETE_ORDER, function(Event $event){
            /**
             * @var Order $order
             */
            $order = $event->sender;
            $order->scheduledShipDate = $this->keiserCommerceHelpers->getScheduledShipDate($order);
            $orderHasNotes = false;
            foreach($order->getLineItems() as $lineItem){
                if(!empty($lineItem->note)){
                    $orderHasNotes = true;
                    break;
                }
            }
            if($order->requiresManualReview && $orderHasNotes){
                $notesAddressReviewStatus = \craft\commerce\Plugin::getInstance()->orderStatuses->getOrderStatusByHandle('notesAddressReviewRequired');
                $order->orderStatusId = $notesAddressReviewStatus->id;
            } else if ($order->requiresManualReview){
                $manualReviewStatus = \craft\commerce\Plugin::getInstance()->orderStatuses->getOrderStatusByHandle('manualReviewRequired');
                $order->orderStatusId = $manualReviewStatus->id;
            } else if ($orderHasNotes) {
                $notesReviewStatus = \craft\commerce\Plugin::getInstance()->orderStatuses->getOrderStatusByHandle('notesReviewRequired');
                $order->orderStatusId = $notesReviewStatus->id;
            }
            Craft::$app->getElements()->saveElement($order, true);
        });

        Event::on(\craft\commerce\services\Addresses::class, \craft\commerce\services\Addresses::EVENT_BEFORE_SAVE_ADDRESS, function(AddressEvent $event){
            $this->keiserCommerceHelpers->onBeforeSaveAddress($event);
        });

        Event::on(\craft\web\twig\variables\CraftVariable::class, CraftVariable::EVENT_INIT, function($event){
            $variable = $event->sender;
            $variable->set('keiserCommerceHelpers', \keiser\commercehelpers\services\KeiserCommerceHelpersService::class);
        });

        Event::on(Discount::class, Discount::EVENT_AFTER_DISCOUNT_ADJUSTMENTS_CREATED, function(DiscountAdjustmentsEvent $e) {
            $queryModel = \craft\elements\Entry::find();
            $queryModel->section = 'bundleDiscounts';
            $entry = $queryModel->one();
            if($entry){
                $bundleOfferDiscountIds = [];
                foreach($entry->bundleDiscountIds as $block){
                    $bundleOfferDiscountIds[] = $block->discountId;
                }
                if(in_array($e->discount->id , $bundleOfferDiscountIds)){
                    //Check whether ALL of the products mentioned in the discount condition are present in cart
                    $lineItemPurchasableIds = [];
                    foreach($e->order->getLineItems() as $lineItem){
                        $lineItemPurchasableIds[] = $lineItem->purchasableId;
                    }
                    if(count(array_intersect($e->discount->getPurchasableIds(), $lineItemPurchasableIds)) !== count($e->discount->getPurchasableIds())){
                        $e->isValid = false;
                        return;
                    }
                    // Apply the base discount value in proportion to the count of bundles present in cart
                    // If discount creates multiple types of adjustments we cannot determine which adjustment was the base amount adjustment
                    if($e->discount->baseDiscount !== null && $e->discount->baseDiscount != 0 && count($e->adjustments) === 1){
                        $itemQuantities = [];
                        foreach($e->order->getLineItems() as $lineItem){
                            if(in_array($lineItem->purchasableId, $e->discount->getPurchasableIds())){
                                $itemQuantities[] = $lineItem->qty;
                            }
                        }
                        $numBundles = min($itemQuantities);
                        $e->adjustments[0]->amount = $e->discount->baseDiscount * $numBundles;
                    }
                }
            }
        });

        $fileTarget = new \craft\log\FileTarget([
            'logFile' => Craft::getAlias('@storage/logs/keisercommercehelpers.log'),
            'categories' => ['keiser-commerce-helpers']
        ]);
        Craft::getLogger()->dispatcher->targets[] = $fileTarget;

        /* Ensures image optimisation for the full size image when adding or replacing an image.
          The ImageOptimize third party plugin only works with transforms so it handles optimising the transforms
          while the below does the optimisation for full size versions
        */
        Event::on(Asset::class, Asset::EVENT_BEFORE_HANDLE_FILE, function(AssetEvent $event){
            if (!$event->asset->id && (!$event->asset->title || $event->asset->title === Craft::t('app', 'New Element')) && $event->asset->filename !== null ) {
                $filename = pathinfo($event->asset->filename, PATHINFO_FILENAME);
                $event->asset->title = AssetsHelper::filename2Title(preg_replace('/-cinv-[0-9]*/', '', $filename));
            }
            $mutableMimeTypes = [
                'image/jpeg',
                'image/gif',
            ];
            if(in_array($event->asset->getMimeType(), $mutableMimeTypes)){
                $this->keiserCommerceHelpers->optimizeImage($event->asset->tempFilePath);
            }
        });

        Event::on(Asset::class, Assets::EVENT_AFTER_REPLACE_ASSET, function(ReplaceAssetEvent $event){
            if(Image::canManipulateAsImage(pathinfo($event->asset->filename, PATHINFO_EXTENSION))){
                $transforms = Craft::$app->getAssetTransforms();
                $transforms->deleteCreatedTransformsForAsset($event->asset);
                $transforms->deleteTransformIndexDataByAssetId($event->asset->id);
            }
        });

        //Add versioning for filenames so as to not run into any issues with cache validations on S3/CloudFront
        Event::on(\craft\helpers\Assets::class, \craft\helpers\Assets::EVENT_SET_FILENAME, function(SetAssetFilenameEvent $event){
            if($event->filename !== null){
                $filename = $event->filename;
                //Prevent multiple cache invalidation suffix if one already exists in the filename (Eg: during replace file)
                $cleanFilename = preg_replace('/-cinv-[0-9]*/', '', $filename);
                $event->filename = $cleanFilename . '-cinv-' . time() . rand(1,1000000);
            }
        });
    }

    public function registerSproutFormsFields()
    {
        $basePath = craft()->path->getPluginsPath() . 'keisercommercehelpers/integrations/sproutforms/fields/';
        require_once $basePath . 'KeiserRichTextField.php';
        require_once $basePath . 'KeiserLightswitchField.php';

        return array(
            new KeiserRichTextField(),
            new KeiserLightswitchField()
        );
    }

    public static function log($message){
        Craft::getLogger()->log($message, \yii\log\Logger::LEVEL_INFO, 'keiser-commerce-helpers');
    }

    /**
     * Register Commerce’s twig extensions
     */
    private function _addTwigExtensions()
    {
        Craft::$app->view->registerTwigExtension(new KeiserCommerceHelpersExtension);
    }

}
