<?php

namespace keiser\commercehelpers\console\controllers;

use Craft;
use craft\commerce\elements\Order;
use keiser\commercehelpers\jobs\OrderEmailJob;
use keiser\commercehelpers\models\KeiserCommerceShippingEmailLog;

class OrderEmailController extends \yii\console\Controller
{
    public function actionSend($orderShortNumbers)
    {
        $orderShortNumbers = explode(',', $orderShortNumbers);
        foreach($orderShortNumbers as $orderShortNumber){
            $orderQuery = Order::find();
            $orderQuery->shortNumber = $orderShortNumber;
            if($order = $orderQuery->one()){
                $existingShippingRecord = KeiserCommerceShippingEmailLog::find()
                    ->where([
                        'orderId' => $order->getId()
                    ])
                    ->one();
                if($existingShippingRecord){
                    $existingShippingRecord->delete();
                }
                Craft::$app->queue->push(new OrderEmailJob([
                    'orderId' => (int)$order->id
                ]));
            }
        }
    }
}
