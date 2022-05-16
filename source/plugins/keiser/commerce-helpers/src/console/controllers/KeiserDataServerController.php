<?php

namespace keiser\commercehelpers\console\controllers;

use Craft;

use keiser\commercehelpers\jobs\KeiserDataServerPushJob;

class KeiserDataServerController extends \yii\console\Controller
{

    public function actionRetryKeiserDataServerPush($orderIds = null){
        if(!$orderIds){
            $orderIds = [];
            $orderQueryModel = \craft\commerce\elements\Order::find();
            $orderQueryModel->isCompleted = true;
            $orderQueryModel->orderPushedToKeiserDataServer = false;
            $orderQueryModel->limit = null;
            $orders = $orderQueryModel->all();
            foreach($orders as $order){
                $orderIds[] = $order->id;
            }
        } else {
            $orderIds = explode(',', $orderIds);
        }
        echo "Order IDs being repushed: " . implode(',', $orderIds) . "\n";
        foreach($orderIds as $orderId) {
            Craft::$app->queue->push(new KeiserDataServerPushJob([
                'orderId' => (int)$orderId
            ]));
        }
    }
}
