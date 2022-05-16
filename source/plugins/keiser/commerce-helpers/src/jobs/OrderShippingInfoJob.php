<?php

namespace keiser\commercehelpers\jobs;

use craft\commerce\elements\Order;
use craft\queue\BaseJob;

use keiser\commercehelpers\Plugin;

class OrderShippingInfoJob extends BaseJob
{
    public $orderIds;

    public function execute($queue)
    {
        $plugin = Plugin::getInstance();
        $orders = $this->_getOrdersByReference();
        $shipments = $plugin->winmagiShippingData->getShipments(
            array_keys($orders)
        );

        if (empty($shipments)) {
            return;
        }

        $total = count($shipments);

        foreach ($shipments as $i => $shipment) {
            $this->setProgress(
                $queue,
                $i / $total,
                \Craft::t('app', '{step, number} of {total, number}', [
                    'step' => $i + 1,
                    'total' => $total,
                ])
            );

            try {
                $referenceId = $shipment['purchaseOrderNum'];
                $plugin->winmagiShippingData->addShipmentTracking(
                    $orders[$referenceId],
                    $shipment['shipments']
                );
            } catch (\Throwable $e) {
                // Don’t let an exception block the queue
                \Craft::warning(
                    "Something went wrong: {$e->getMessage()}",
                    __METHOD__
                );
            }
        }
    }

    public function defaultDescription()
    {
        $message = implode($this->orderIds, ', ');
        return "Updating shipment tracking for orders {$message}.";
    }

    /**
     * Returns an array of orders keyed by the order reference Id.
     *
     * @return Array
     */
    private function _getOrdersByReference()
    {
        $orders = Order::find()
            ->id($this->orderIds)
            ->all();

        $ordersByReference = [];
        foreach ($orders as $order) {
            $ordersByReference[$order->reference] = $order;
        }

        return $ordersByReference;
    }
}
