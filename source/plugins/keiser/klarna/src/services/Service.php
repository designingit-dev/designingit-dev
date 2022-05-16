<?php

namespace keiser\klarna\services;

use Craft;
use craft\base\Component;
use craft\commerce\elements\Order;
use craft\commerce\models\Address;
use craft\commerce\models\LineItem;
use keiser\klarna\errors\KlarnaException;

class Service extends Component {

    /**
     * @return false|string Returns the client_token or false on failure
     */
    public function createSession(){
        try {
            $klarnaSessionId = Craft::$app->getSession()->get(\keiser\klarna\Plugin::$klarnaSessionIdKey);
            if($klarnaSessionId){
                try {
                    $response = $this->request("/payments/v1/sessions/{$klarnaSessionId}", 'GET', []);
                    if($response['status'] == 'complete') {
                        return $this->_createNewSession();
                    }
                    $cart = \craft\commerce\Plugin::getInstance()->getCarts()->getCart();
                    $response = $this->request("/payments/v1/sessions/{$klarnaSessionId}", 'POST', $this->getOrderDataForSessionRequest($cart));
                    return $response['client_token'];
                } catch (\Exception $e) {
                    return $this->_createNewSession();
                }
            } else {
                return $this->_createNewSession();
            }
        } catch (\Exception $e){
            \keiser\klarna\Plugin::log($e->getCode() . ': ' . $e->getMessage());
            return false;
        } catch (\Throwable $e){
            \keiser\klarna\Plugin::log($e->getCode() . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param $cart Order
     * @return array
     */
    public function getOrderLines(Order $cart){
        $lineItems = [];
        $lineItemTotalTax = 0;
        foreach($cart->getLineItems() as $lineItem){
            $price = $lineItem->getPrice();
            if($lineItem->getOnSale()){
                $price = $lineItem->getSalePrice();
            }
            $lineItemData = [
                'type' => 'physical',
                'reference' => $lineItem->getSku(),
                'name' => $lineItem->getDescription(),
                'quantity' => $lineItem->qty,
                'unit_price' => $this->_convertAmountToSubunit($price + $lineItem->getTax()),
                'total_amount' => $this->_convertAmountToSubunit($lineItem->getTotal()),
                'total_discount_amount' => $this->_convertAmountToSubunit(abs($lineItem->getDiscount())),
            ];
            if(getenv('SITE_LOCALE') == 'en-gb'){
                $lineItemData['tax_rate'] = $this->_convertAmountToSubunit(($lineItem->getTax()/$price) * 100);
                $lineItemData['total_tax_amount'] = $this->_convertAmountToSubunit($lineItem->getTax());
                $lineItemTotalTax += $lineItem->getTax();
            }
            if(isset($lineItem->getPurchasable()->product->image) && $lineItem->getPurchasable()->product->image->one()){
                $lineItemData['image_url'] = $lineItem->getPurchasable()->product->image->one()->getUrl();
                $lineItemData['product_url'] = $lineItem->getPurchasable()->product->getUrl();
            }
            $lineItems[] = $lineItemData;
        }
        if($cart->getTotalShippingCost() > 0){
            $lineItemData = [
                'type' => 'shipping_fee',
                'name' => 'Shipping Fee',
                'quantity' => 1,
                'unit_price' => $this->_convertAmountToSubunit($cart->getTotalShippingCost()),
                'total_amount' => $this->_convertAmountToSubunit($cart->getTotalShippingCost()),
            ];
            if(getenv('SITE_LOCALE') == 'en-gb'){
                $shippingTaxAmount = $cart->getTotalTax() - $lineItemTotalTax;
                $lineItemData['tax_rate'] = $this->_convertAmountToSubunit(($shippingTaxAmount/$cart->getTotalShippingCost()) * 100);
                $lineItemData['total_tax_amount'] = $this->_convertAmountToSubunit($shippingTaxAmount);
            }
            $lineItems[] = $lineItemData;
        }
        if(getenv('SITE_LOCALE') == 'en-us'){
            $lineItems[] = [
                'type' => 'sales_tax',
                'name' => 'Tax',
                'quantity' => 1,
                'unit_price' => $this->_convertAmountToSubunit($cart->getTotalTax()),
                'total_amount' => $this->_convertAmountToSubunit($cart->getTotalTax()),
            ];
        }
        return $lineItems;
    }

    /**
     * @param $path string
     * @param $method string
     * @param $data array
     * @return array
     * @throws KlarnaException
     */
    public function request($path, $method, $data){
        $data = json_encode($data);
        $headers = [
            'Content-Type: application/json'
        ];
        $keyPair = getenv('KLARNA_API_USERNAME') . ':' . getenv('KLARNA_API_PASSWORD');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, getenv('KLARNA_API_URL') . $path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $keyPair);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        \keiser\klarna\Plugin::log($response);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        \keiser\klarna\Plugin::log($status);
        curl_close($ch);
        $response = json_decode($response, true);
        if($status === 200){
            return $response;
        }
        \keiser\klarna\Plugin::log($response);
        $errorMessage = '';
        if(isset($response['correlation_id'])){
            $errorMessage .= "Correlation ID: {$response['correlation_id']}\n";
        }
        if(isset($response['error_code'])){
            $errorMessage .= "Error Code: {$response['error_code']}\n";
        }
        if(isset($response['error_messages']) && !empty($response['error_messages'])){
            $errorMessage .= "Error Message: ";
            foreach($response['error_messages'] as $message){
                $errorMessage .= $message . "\n";
            }
        }
        throw new KlarnaException($errorMessage , $status);
        return false;
    }

    /**
     * @return false|string
     */
    private function _createNewSession(){
        $cart = \craft\commerce\Plugin::getInstance()->getCarts()->getCart();
        $response = $this->request('payments/v1/sessions', 'POST', $this->getOrderDataForSessionRequest($cart));
        Craft::$app->getSession()->set(\keiser\klarna\Plugin::$klarnaSessionIdKey, $response['session_id']);
        return $response['client_token'];
    }

    /**
     * @param $amount float
     * @return float|int
     */
    private function _convertAmountToSubunit($amount){
        return round($amount, 2) * 100;
    }

    /**
     * @return array
     * @throws \Throwable
     * @throws \craft\commerce\errors\CurrencyException
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getOrderDataForSessionRequest(Order $cart){
        return [
            'billing_address' => $this->_getBillingAddress($cart),
            'purchase_country' => $cart->getBillingAddress()->countryIso,
            'purchase_currency' => $cart->getPaymentCurrency(),
            'locale' => getenv('SITE_LOCALE'),
            'order_amount' => $this->_convertAmountToSubunit($cart->getTotal()),
            'order_tax_amount' => $this->_convertAmountToSubunit($cart->getTotalTax()),
            'order_lines' => $this->getOrderLines($cart),
            'shipping_address' => $this->_getShippingAddress($cart)
        ];
    }

    /**
     * @param Order $cart
     * @return array
     */
    private function _getBillingAddress(Order $cart){
        return $this->_buildAddressObject($cart->getBillingAddress(), $cart);
    }

    /**
     * @param Order $cart
     * @return array
     */
    private function _getShippingAddress(Order $cart){
        return $this->_buildAddressObject($cart->getShippingAddress(), $cart);
    }

    /**
     * @param Address $address
     * @param Order $cart
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function _buildAddressObject(Address $address, Order $cart){
        //Region is not collected in Keiser UK orders and is optional for Klarna UK. Klarna US requires state abbr.
        $region = '';
        if($address->getState() && $address->getState()->abbreviation){
            $region = $address->getState()->abbreviation;
        }
        return [
            'city' => $address->city,
            'country' => $address->countryIso,
            'email' => $cart->getEmail(),
            'family_name' => $address->lastName,
            'given_name' => $address->firstName,
            'phone' => $address->phone,
            'postal_code' => $address->zipCode,
            'region' => $region,
            'street_address' => $address->address1,
            'street_address_2' => $address->address2
        ];
    }
}