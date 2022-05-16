<?php

namespace keiser\commercehelpers\jobs;

use Craft;
use craft\commerce\elements\Order;
use keiser\commercehelpers\errors\EnvironmentVariableNotFound;
use keiser\commercehelpers\errors\OrderNotFoundException;

class KeiserDataServerPushJob extends \craft\queue\BaseJob {

    public $orderId;

    public function execute($queue){
        if(
            getenv('KEISER_DATA_SERVER_URL') &&
            getenv('KEISER_DATA_SERVER_API_KEY')){

            $order = Order::findOne($this->orderId);
            if($order) {
                if (!$order->orderPushedToKeiserDataServer) {
                    $headers = [
                        'Content-Type:application/json',
                        'Authorization:Bearer ' . getenv('KEISER_DATA_SERVER_API_KEY')
                    ];
                    $billingStateAbbr = 'UK';
                    if (isset($order->billingAddress->state->abbreviation)) {
                        $billingStateAbbr = $order->billingAddress->state->abbreviation;
                    }
                    $shippingStateAbbr = 'UK';
                    if (isset($order->shippingAddress->state->abbreviation)) {
                        $shippingStateAbbr = $order->shippingAddress->state->abbreviation;
                    }
                    $data = [
                        'customerBillingCity' => $order->billingAddress->city,
                        'customerBillingState' => $billingStateAbbr,
                        'customerBillingCountry' => $order->billingAddress->country->iso,
                        'customerShippingCity' => $order->shippingAddress->city,
                        'customerShippingState' => $shippingStateAbbr,
                        'customerShippingCountry' => $order->shippingAddress->country->iso,
                        'salesChannelOrderId' => $order->number,
                        'purchaseDate' => $order->dateOrdered->format(\DateTime::ATOM),
                        'orderStatus' => 'received',
                        'shippingCharged' => $order->getTotalShippingCost(),
                        'taxCharged' => $order->getTotalTax(),
                        'grandTotalCharged' => $order->getTotalPrice(),
                        'paymentTermsMet' => $order->getIsPaid(),
                        'salesLineItems' => []
                    ];
                    foreach ($order->getLineItems() as $lineItem) {
                        $productSellersNotes = '';
                        $itemOptions = $lineItem->getOptions();
                        if (!empty($itemOptions)) {
                            foreach ($itemOptions as $key => $val) {
                                if($key == 'productSellersNotes'){
                                    $productSellersNotes = $val;
                                }
                            }
                        }
                        $price = $lineItem->price;
                        if ($lineItem->onSale) {
                            $price = $lineItem->salePrice;
                        }
                        $data['salesLineItems'][] = [
                            'itemPN' => $lineItem->sku,
                            'itemDescription' => $productSellersNotes,
                            'itemQuantity' => (int)$lineItem->qty,
                            'itemPrice' => (float)$price,
                        ];
                    }
                    $ch = curl_init(getenv('KEISER_DATA_SERVER_URL') . 'orders');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                    $result = curl_exec($ch);
                    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $pushed = true;
                    if (curl_error($ch)) {
                        Craft::error('Keiser Data Server Error: ' . curl_error($ch) . ' ' . $result);
                        $pushed = false;
                    }
                    curl_close($ch);
                    if (!in_array($responseCode, [201, 409])) {
                        Craft::error('Keiser Data Server Error: ' . $responseCode . ' ' . $result);
                        $pushed = false;
                    }
                    if ($pushed) {
                        try {
                            $command = Craft::$app->getDb()->createCommand();
                            $command->update(
                                'content',
                                ['field_orderPushedToKeiserDataServer' => 1],
                                'elementId=:id', [':id' => $order->id]);
                            $command->execute();
                        } catch (\Exception $e){
                            throw $e;
                        }
                    }
                }
            } else {
                    throw new OrderNotFoundException();
                }
        } else {
            throw new EnvironmentVariableNotFound();
        }
    }

    protected function defaultDescription(){
        return "Pushing Order #{$this->orderId} to Keiser Data Server";
    }

}
