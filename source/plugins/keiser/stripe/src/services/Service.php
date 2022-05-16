<?php

namespace keiser\stripe\services;

use yii\base\Component;

class Service extends Component {

    public function createPaymentIntent($orderId, $amount, $currency){
        $order = \craft\commerce\elements\Order::findOne($orderId);
        if($order && !$order->getIsPaid()){
            \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

            $intent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => $currency,
                'description' => 'Order #'  . $orderId
            ]);

            return $intent;
        }
        return null;
    }

}
