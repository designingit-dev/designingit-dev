<?php

namespace keiser\commercehelpers\jobs;

use craft\commerce\elements\Order;
use craft\queue\BaseJob;
use keiser\commercehelpers\errors\OrderNotFoundException;

class MailchimpSubscribeJob extends BaseJob {

    public $orderId;

    public function execute($queue)
    {
        $order = Order::findOne($this->orderId);
        if($order){
            \keiser\commercehelpers\Plugin::getInstance()->keiserCommerceHelpers->mailChimpSubscribe($order);
        } else {
            throw new OrderNotFoundException();
        }
    }

    public function defaultDescription()
    {
        return "Subscribing email for Order {$this->orderId} to MailChimp";
    }

}
