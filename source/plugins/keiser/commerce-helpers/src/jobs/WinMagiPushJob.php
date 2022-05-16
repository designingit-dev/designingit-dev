<?php

namespace keiser\commercehelpers\jobs;

use Craft;
use craft\commerce\elements\Order;
use keiser\commercehelpers\errors\EnvironmentVariableNotFound;
use keiser\commercehelpers\errors\OrderNotFoundException;

class WinMagiPushJob extends \craft\queue\BaseJob {

    public $orderId;

    public function execute($queue){
        if(
            getenv('WINMAGI_API_KEY') &&
            getenv('WINMAGI_API_URL') &&
            getenv('WINMAGI_AUTHORIZATION')){

            $order = Order::findOne($this->orderId);
            if($order){
                $headers = [
                    'Content-Type:application/json',
                    'Authorization:basic ' . getenv('WINMAGI_AUTHORIZATION'),
                    'Apikey:' .getenv('WINMAGI_API_KEY')
                ];
                $billingStateAbbr = 'UK';
                if(isset($order->billingAddress->state->abbreviation)){
                    $billingStateAbbr = $order->billingAddress->state->abbreviation;
                }
                $shippingStateAbbr = 'UK';
                if(isset($order->shippingAddress->state->abbreviation)){
                    $shippingStateAbbr = $order->shippingAddress->state->abbreviation;
                }
                /**
                 * @var \DateTime $scheduledShipDate
                 */
                $scheduledShipDate = $order->scheduledShipDate;
                $scheduledShipDate->setTime($order->dateOrdered->format('H'),$order->dateOrdered->format('i'),$order->dateOrdered->format('s'));
                $data =  [
                    'customerCompanyName' => $order->billingAddress->firstName . ' ' . $order->billingAddress->lastName,
                    'customerContactPhone' => $order->billingAddress->phone,
                    'customerContactEmail' => $order->getEmail(),
                    'customerBillingName' => $order->billingAddress->firstName . ' ' . $order->billingAddress->lastName,
                    'customerBillingAddressLine1' => $order->billingAddress->address1,
                    'customerBillingAddressLine2' => $order->billingAddress->address2,
                    'customerBillingCity' => $order->billingAddress->city,
                    'customerBillingState' => $billingStateAbbr,
                    'customerBillingPostalCode' => $order->billingAddress->zipCode,
                    'customerBillingCountry' => $order->billingAddress->country->iso,
                    'customerShippingName' => $order->shippingAddress->firstName . ' ' . $order->shippingAddress->lastName,
                    'customerShippingAddressLine1' => $order->shippingAddress->address1,
                    'customerShippingAddressLine2' => $order->shippingAddress->address2,
                    'customerShippingCity' => $order->shippingAddress->city,
                    'customerShippingState' => $shippingStateAbbr,
                    'customerShippingPostalCode' => $order->shippingAddress->zipCode,
                    'customerShippingCountry' => $order->shippingAddress->country->iso,
                    'purchaseOrderNum' => $order->number,
                    'purchaseDate' => $order->dateOrdered->format(\DateTime::ATOM),
                    'lastModifiedDate' => $order->dateUpdated->format(\DateTime::ATOM),
                    'shipByDate' => $order->scheduledShipDate->format(\DateTime::ATOM),
                    'orderStatus' => 'received',
                    'shippingCharged' => $order->getAdjustmentsTotalByType("shipping"),
                    'taxCharged' => $order->getAdjustmentsTotalByType("tax"),
                    'grandTotalCharged' => $order->getTotalPrice(),
                    'paymentTermsMet' => $order->getIsPaid(),
                    'salesLineItems' => []
                ];
                foreach($order->getLineItems() as $lineItem){
                    $productSellersNotes = '';
                    $note = $lineItem->note;
                    $itemOptions = $lineItem->getOptions();
                    if(!empty($itemOptions)){
                        foreach($itemOptions as $key => $val){
                            switch($key){
                                case 'promoItem':
                                    $data['salesLineItems'][] = [
                                        'itemPN' => $val,
                                        'itemDescription' => '',
                                        'itemQuantity' => 1,
                                        'itemPrice' => 0,
                                        'itemNotes' => ''
                                    ];
                                    break;
                                case 'tShirtSize':
                                    $note .= ' Free T-Shirt Size: ' . $val;
                                    break;
                                case 'productSellersNotes':
                                    $productSellersNotes = $val;
                                    break;
                                case 'Will this Dumbbell Holder be installed on a Bike purchased before May 2016?':
                                    if($val == 'yes'){
                                        $data['salesLineItems'][] = [
                                            'itemPN' => '550879B',
                                            'itemDescription' => '',
                                            'itemQuantity' => 1,
                                            'itemPrice' => 0,
                                            'itemNotes' => ''
                                        ];
                                    }
                                    break;

                            }
                        }
                    }
                    $price = $lineItem->price;
                    if($lineItem->onSale){
                        $price = $lineItem->salePrice;
                    }
                    $data['salesLineItems'][] = [
                        'itemPN' => $lineItem->sku,
                        'itemDescription' => $productSellersNotes,
                        'itemQuantity' => $lineItem->qty,
                        'itemPrice' => $price,
                        'itemNotes' => $note
                    ];
                }
                //Discount adjustment total is stored as a negative number in Craft
                if($order->getTotalDiscount() < 0){
                    foreach($order->getAdjustments() as $adjustment){
                        if($adjustment->type == 'discount'){;
                            $data['salesLineItems'][] = [
                                'itemPN' => 'DISCOUNT',
                                'itemDescription' => $adjustment->name,
                                'itemQuantity' => 1,
                                'itemPrice' => $adjustment->amount,
                                'itemNotes' => $adjustment->description
                            ];
                        }
                    }
                }
                if(in_array($order->getOrderStatus()->handle, ['manualReviewRequired', 'notesAddressReviewRequired'])){
                    $data['validationWarnings'] = [];
                    $data['validationWarnings'][] = [
                        'type' => 'address',
                        'description' => 'failed validation'
                    ];
                }
                $ch = curl_init(getenv('WINMAGI_API_URL') . '/order');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 180);
                $result = curl_exec($ch);
                $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $pushed = true;
                if(curl_error($ch)){
                    Craft::error('WinMagi Error: ' . curl_error($ch) . ' ' . $result);
                    $pushed = false;
                }
                curl_close($ch);
                if($responseCode !==  200){
                    Craft::error('WinMagi Error: ' . $responseCode . ' ' . $result);
                    $pushed = false;
                }
                if($pushed){
                    try {
                        $command = Craft::$app->getDb()->createCommand();
                        $command->update(
                            'content',
                            ['field_orderPushedToWinmagi' => 1],
                            'elementId=:id', [':id' => $order->id]);
                        $command->execute();
                    } catch (\Exception $e){
                        throw $e;
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
        return "Pushing Order #{$this->orderId} to WinMagi";
    }

}
