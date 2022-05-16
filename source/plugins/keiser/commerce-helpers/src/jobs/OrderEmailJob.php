<?php

namespace keiser\commercehelpers\jobs;

use craft\commerce\elements\Order;
use craft\queue\BaseJob;
use keiser\commercehelpers\errors\OrderNotFoundException;

class OrderEmailJob extends BaseJob {

    public $orderId;

    public function execute($queue)
    {
        $order = Order::findOne($this->orderId);
        if($order){
            $plugin = \keiser\commercehelpers\Plugin::getInstance();
            $plugin->keiserCommerceHelpers->sendOrderConfirmationEmail($order);
            $plugin->keiserCommerceHelpers->sendOrderShippedEmail($order);
        } else {
            throw new OrderNotFoundException();
        }
    }

    public function defaultDescription()
    {
        return "Sending order emails for Order {$this->orderId}";
    }

}
