<?php

namespace keiser\tax\adjusters;

use craft\commerce\elements\Order;
use craft\commerce\models\OrderAdjustment;
use craft\commerce\base\AdjusterInterface;

class KeiserTaxAdjuster implements AdjusterInterface {

    public function adjust(Order $order): array
    {
        if($order->shippingAddress !== NULL && sizeof($order->lineItems) > 0){
            /**
             * @var \keiser\tax\services\Service $taxService
             */
            $taxService = \keiser\tax\Plugin::getInstance()->salesTaxService;
            $salesTax = $taxService->getTaxForOrder($order);
            if($salesTax){
                $taxAdjuster = new OrderAdjustment();
                $taxAdjuster->type = "tax";
                $taxAdjuster->name = "Taxes";
                $taxAdjuster->description = '';
                $taxAdjuster->amount = abs($salesTax);
                $taxAdjuster->orderId = $order->id;
                $taxAdjuster->included = false;
                return [$taxAdjuster];
            }
        }
        return [];
    }
}
