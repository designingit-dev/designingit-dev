<?php

namespace keiser\commercehelpers\console\controllers;

use Craft;

use craft\commerce\elements\Order;
use craft\helpers\Console;
use yii\console\ExitCode;

use keiser\commercehelpers\Plugin;
use keiser\commercehelpers\jobs\OrderShippingInfoJob;
use keiser\commercehelpers\jobs\WinMagiPushJob;

class WinmagiController extends \yii\console\Controller
{
    public function actionRetryWinmagiPush($orderIds = null)
    {
        if (!$orderIds) {
            $orderIds = [];
            $orderQueryModel = \craft\commerce\elements\Order::find();
            $orderQueryModel->isCompleted = true;
            $orderQueryModel->orderPushedToWinmagi = false;
            $orderQueryModel->limit = null;
            $orders = $orderQueryModel->all();
            foreach ($orders as $order) {
                $orderIds[] = $order->id;
            }
        } else {
            $orderIds = explode(',', $orderIds);
        }
        echo 'Order IDs being repushed: ' . implode(',', $orderIds) . "\n";
        foreach ($orderIds as $orderId) {
            Craft::$app->queue->push(
                new WinMagiPushJob([
                    'orderId' => (int) $orderId,
                ])
            );
        }
    }

    public function actionMarkOlderOrdersAsPushed()
    {
        $orderIds = (new craft\db\Query())
            ->select(['orders.id as id'])
            ->from(['orders' => '{{%commerce_orders}}'])
            ->where(['=', 'orders.isCompleted', 1])
            ->where(['not', ['orders.email' => null]])
            ->andWhere('[[orders.dateOrdered]] <= :lastSaturday', [
                'lastSaturday' => '2021-11-13 01:00:00',
            ])
            ->all();
        $orderIdList = [];
        foreach ($orderIds as $order) {
            $orderIdList[] = $order['id'];
        }
        try {
            $command = Craft::$app->getDb()->createCommand();
            $command->update(
                'content',
                ['field_orderPushedToWinmagi' => 1],
                ['in', 'elementId', $orderIdList]
            );
            var_dump($command->execute());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Fetches shipment data from the WinMagi api for any 'orderProcessed' orders. If any
     * orders have been shipped, tracking data will be added to the entry, and the
     * status will be set to shipped. This will trigger a shipment email notifcation.
     *
     * Shipments are grouped into batches of 5 to allow the API time to process the
     * request.
     *
     * Note if $ids are passed only those orders will be fetched instead of all orders
     * with the 'orderProcessed' status.
     *
     * @param string $ids a comment seperated list of order ids to update
     */
    public function actionUpdateOrdersShipping(string $ids = null)
    {
        $ordersQuery = Order::find();
        if (!$ids) {
            $ordersQuery->orderStatus('orderProcessed');
            $ids = $ordersQuery->ids();
        } else {
            $ids = explode(',', $ids);
        }

        if (empty($ids)) {
            $this->stdout('No orders to update.' . PHP_EOL, Console::FG_GREEN);
            return ExitCode::OK;
        }

        try {
            $batches = array_chunk($ids, 15);
            foreach ($batches as $batch) {
                Craft::$app->queue->push(
                    new OrderShippingInfoJob(['orderIds' => $batch])
                );
            }

            $jobs = count($batches);
            $this->stdout("Queued {$jobs} jobs." . PHP_EOL, Console::FG_GREEN);

            return ExitCode::OK;
        } catch (\Throwable $e) {
            Craft::$app->getErrorHandler()->logException($e);
            $this->stderr(
                'error: ' . $e->getMessage() . PHP_EOL,
                Console::FG_RED
            );
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}
