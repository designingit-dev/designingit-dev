<?php

namespace keiser\tax\services;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\elements\Variant;
use \Httpful\Request;
use keiser\commercehelpers\errors\EnvironmentVariableNotFound;
use yii\base\Exception;
use yii\web\HttpException;

class Service extends \craft\base\Component
{
    /**
     * @param Order $order
     * @return bool
     * @throws EnvironmentVariableNotFound
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getTaxForOrder(Order $order)
    {
        $settings = \keiser\tax\Plugin::getInstance()->getSettings();

        if($settings->keiserTaxApiKey && getenv('TAX_API_URL'))
        {
            $requiredParams = [
                'address1',
                'city',
                'state',
                'zipCode',
                'country'
            ];
            foreach($requiredParams as $param){
                if(!isset($order->shippingAddress->$param) || !$order->shippingAddress->$param){
                    throw new Exception('Shipping Address ' . $param . ' not found');
                }
            }
            $data =  [
                'customerCompanyName' => $order->shippingAddress->firstName . ' ' . $order->shippingAddress->lastName,
                'customerShippingAddressLine1' => $order->shippingAddress->address1,
                'customerShippingAddressLine2' => $order->shippingAddress->address2,
                'customerShippingCity' => $order->shippingAddress->city,
                'customerShippingState' => $order->shippingAddress->state->abbreviation,
                'customerShippingPostalCode' => $order->shippingAddress->zipCode,
                'customerShippingCountry' => $order->shippingAddress->country->iso,
                'purchaseDate' => (new \DateTime())->format(\DateTime::ATOM),
                'shippingTotal' => $order->getTotalShippingCost(),
                'grandTotalCharged' => ($order->getTotalPrice() - $order->getTotalTax()),
                'installLaborTotal' => 0,
                'salesLineItems' => []
            ];
            foreach($order->getLineItems() as $lineItem){
                $itemOptions = $lineItem->getOptions();
                if(!empty($itemOptions)){
                    foreach($itemOptions as $key => $val){
                        switch($key){
                            case 'promoItem':
                                $data['salesLineItems'][] = [
                                    'itemPN' => $val,
                                    'itemQuantity' => 1,
                                    'itemPrice' => 0,
                                ];
                                break;
                            case 'Will this Dumbbell Holder be installed on a Bike purchased before May 2016?':
                                if($val == 'yes'){
                                    $data['salesLineItems'][] = [
                                        'itemPN' => '550879B',
                                        'itemQuantity' => 1,
                                        'itemPrice' => 0,
                                    ];
                                }
                                break;
                            case 'whiteGloveDelivery':
                                if($val){
                                    $queryModel = Variant::find();
                                    $queryModel->sku = $lineItem->getPurchasable()->getSku();
                                    $variant = $queryModel->one();
                                    $product = $variant->getProduct();
                                    $data['installLaborTotal'] += ($product->whiteGloveDeliveryFee * $lineItem->qty);
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
                    'itemQuantity' => $lineItem->qty,
                    'itemPrice' => $price
                ];
            }
            //Discount adjustment total is stored as a negative number in Craft
            if($order->getTotalDiscount() < 0){
                $discountAdjustment = null;
                foreach($order->getAdjustments() as $adjustment){
                    if($adjustment->type == 'discount'){
                        $discountAdjustment = $adjustment;
                    }
                }
                $data['salesLineItems'][] = [
                    'itemPN' => 'DISCOUNT',
                    'itemQuantity' => 1,
                    'itemPrice' => $order->getTotalDiscount(),
                ];
            }

            // Create a new client
            $headers = [
                'Content-Type:application/json',
                "x-api-key:{$settings->keiserTaxApiKey}"
            ];
            $ch = curl_init(getenv('TAX_API_URL'));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            $result = curl_exec($ch);
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if(curl_error($ch) || $responseCode !==  200){
                $this->logError($order, $data, $responseCode, $result);
            }
            curl_close($ch);
            $result = json_decode($result);
            if (!isset($result->totalTaxCalculated)) {
                $this->logError($order, $data, $responseCode, $result);
            } else {
                return $result->totalTaxCalculated;
            }
            return false;
        } else {
            throw new EnvironmentVariableNotFound();
        }
        return false;
    }

    private function logError($order, $postData, $responseCode, $response){
        \keiser\tax\Plugin::log('Request to AWS Keiser Tax API failed');
        \keiser\tax\Plugin::log('Response Code: ' . $responseCode);
        \keiser\tax\Plugin::log("Response Body: {$response}");
        \keiser\tax\Plugin::log('Query: ' . json_encode($postData));
        throw new Exception("There was an error determining tax rate for Order ID {$order->id}");
    }

}
