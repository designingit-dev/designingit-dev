<?php

namespace keiser\freight\services;

use Craft;
use craft\base\Component;
use craft\commerce\elements\Order;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\commerce\models\Address;
use craft\helpers\DateTimeHelper;
use keiser\formhelpers\Plugin;

class KeiserFreightRatesService extends Component
{

    private $_shipmentsBySignature;

    public function init()
    {
        $this->_shipmentsBySignature = [];
    }

    public function getRates(Order $order)
    {
        $rates = $this->_getShipment($order);

        return $rates;
    }

    private function _getShipment(Order $order)
    {
        $signature = $this->_getSignature($order);

        // Do we already have it on this request?
        if (isset($this->_shipmentsBySignature[$signature]) && $this->_shipmentsBySignature[$signature] != false)
        {
            return $this->_shipmentsBySignature[$signature];
        }

        $cacheKey = 'keiserfreight-shipment-'.$signature;
        // Is it in the cache? if not, get it from the api.
        $shipment = Craft::$app->getCache()->get($cacheKey);

        if (!$shipment || Craft::$app->config->general->devMode)
        {
            $shipment = $this->_createShipment($order);
            $this->_shipmentsBySignature[$signature] = Craft::$app->getCache()->set($cacheKey, $shipment);
        }

        $this->_shipmentsBySignature[$signature] = $shipment;

        return $this->_shipmentsBySignature[$signature];
    }

    private function _createShipment(Order $order)
    {
        /** @var Address $shippingAddress */
        $shippingAddress = $order->shippingAddress;

        if (!$shippingAddress)
        {
            return false;
        }

        $totalFreightRate = '0.00';

        $nextDayAvailable = true;
        $totalNextDayRate = '0.00';
        $twoDayAvailable = true;
        $totalTwoDayRate = '0.00';
        $threeDayAvailable = true;
        $totalThreeDayRate = '0.00';
        $fullyAssembledAvailable = true;
        $totalFullyAssembledRate = '0.00';
        $whiteGloveDeliveryFees = 0;
        $whiteGloveDeliveryAvailable = false;
        $whiteGloveDeliveryInCart = false;
        $carrier = "Keiser";

        $productRates = $this->_getProductRates();

        $shippingZone = $this->_addressZone($shippingAddress);

        $equipmentInCart = false;
        foreach($order->lineItems as $purchasable){
            if($purchasable->shippingCategory->handle == 'equipmentMSeries'){
                $equipmentInCart = true;
                break;
            }
        }

        $totalFreightRateAccessoryShippingChargeAdded = false;
        $totalNextDayRateAccessoryShippingChargeAdded = false;
        $totalTwoDayRateAccessoryShippingChargeAdded = false;
        $totalThreeDayRateAccessoryShippingChargeAdded = false;
        $totalFullyAssembledRateShippingChargeAdded = false;

        foreach($order->lineItems as $purchasable) {
            if($purchasable->shippingCategory->handle == 'accessoriesMSeries' && $equipmentInCart){
                continue;
            }
            if ($productRate = array_key_exists($purchasable->sku, $productRates)) {
                foreach ($productRates[$purchasable->sku] as $zone) {
                    if ($zone['zoneId'] == $shippingZone) {

                        if($purchasable->shippingCategory->handle == 'accessoriesMSeries'){
                            if(!$totalFreightRateAccessoryShippingChargeAdded && (float)$zone['rate'] > 0){
                                $totalFreightRate += number_format($zone['rate'], 2, '.', '');
                                $totalFreightRateAccessoryShippingChargeAdded = true;
                            }
                        } else {
                            $totalFreightRate += (number_format($zone['rate'], 2, '.', '') * $purchasable->qty);
                        }

                        if (isset($zone['nextDay']) && strlen($zone['nextDay']) > 0) {
                            if($purchasable->shippingCategory->handle == 'accessoriesMSeries'){
                                if(!$totalNextDayRateAccessoryShippingChargeAdded && (float)$zone['nextDay'] > 0){
                                    $totalNextDayRate += number_format($zone['nextDay'], 2, '.', '') ;
                                    $totalNextDayRateAccessoryShippingChargeAdded = true;
                                }
                            } else {
                                $totalNextDayRate += (number_format($zone['nextDay'], 2, '.', '') * $purchasable->qty);
                            }
                        } else {
                            $nextDayAvailable = false;
                        }

                        if (isset($zone['twoDay']) && strlen($zone['twoDay']) > 0) {
                            if($purchasable->shippingCategory->handle == 'accessoriesMSeries'){
                                if(!$totalTwoDayRateAccessoryShippingChargeAdded && (float)$zone['twoDay'] > 0){
                                    $totalTwoDayRate += number_format($zone['twoDay'], 2, '.', '');
                                    $totalTwoDayRateAccessoryShippingChargeAdded = true;
                                }
                            } else {
                                $totalTwoDayRate += (number_format($zone['twoDay'], 2, '.', '') * $purchasable->qty);
                            }
                        } else {
                            $twoDayAvailable = false;
                        }

                        if (isset($zone['threeDay']) && strlen($zone['threeDay']) > 0) {
                            if($purchasable->shippingCategory->handle == 'accessoriesMSeries'){
                                if(!$totalThreeDayRateAccessoryShippingChargeAdded && (float)$zone['threeDay'] > 0){
                                    $totalThreeDayRate += number_format($zone['threeDay'], 2, '.', '');
                                    $totalThreeDayRateAccessoryShippingChargeAdded = true;
                                }
                            } else {
                                $totalThreeDayRate += (number_format($zone['threeDay'], 2, '.', '') * $purchasable->qty);
                            }

                        } else {
                            $threeDayAvailable = false;
                        }

                        if (isset($zone['fullyAssembled']) && strlen($zone['fullyAssembled']) > 0) {
                            if($purchasable->shippingCategory->handle == 'accessoriesMSeries'){
                                if(!$totalFullyAssembledRateShippingChargeAdded && (float)$zone['fullyAssembled'] > 0){
                                    $totalFullyAssembledRate += number_format($zone['fullyAssembled'], 2, '.', '');
                                    $totalFullyAssembledRateShippingChargeAdded = true;
                                }
                            } else {
                                $totalFullyAssembledRate += (number_format($zone['fullyAssembled'], 2, '.', '') * $purchasable->qty);
                            }

                        } else {
                            $fullyAssembledAvailable = false;
                        }
                    }
                }
            }
        }

        $service = 'Freight Shipping';
        $lineItems = $order->getLineItems();
        if ($totalFreightRate < 1) {
            $service = 'Free Freight Shipping';
        }

        $rates = [];

        if( getenv('WHITEGLOVEDELIVERY_AVAILABLE') && (bool)getenv('WHITEGLOVEDELIVERY_AVAILABLE')) {
            if ($shippingAddress->getCountry()->iso == 'US' && !in_array($shippingAddress->getState()->abbreviation, ['AK', 'HI'])) {
                $whiteGloveDeliveryAvailable = true;
            }
            foreach ($lineItems as $lineItem) {
                $options = $lineItem->getOptions();
                if (isset($options['whiteGloveDelivery']) && $options['whiteGloveDelivery']) {
                    $whiteGloveDeliveryInCart = true;
                    $queryModel = Variant::find();
                    $queryModel->sku = $lineItem->getPurchasable()->getSku();
                    $variant = $queryModel->one();
                    $product = $variant->getProduct();
                    $whiteGloveDeliveryFees += ($product->whiteGloveDeliveryFee * $lineItem->qty);
                }
            }
            if (!Craft::$app->request->isConsoleRequest && Craft::$app->getRequest()->getParam('action', '') == '/keiser-contact-helpers/keiser-contact-helpers/get-available-shipping-methods' && $whiteGloveDeliveryInCart && !$whiteGloveDeliveryAvailable) {
                \keiser\commercehelpers\Plugin::getInstance()->keiserCommerceHelpers->removeWhiteGloveDeliveryFromCart();
                Craft::$app->getSession()->setFlash('whiteGloveRemoved', true);
            } else if($whiteGloveDeliveryInCart && $whiteGloveDeliveryAvailable){
                $totalFreightRate += $whiteGloveDeliveryFees;
                $totalNextDayRate += $whiteGloveDeliveryFees;
                $totalTwoDayRate += $whiteGloveDeliveryFees;
                $totalThreeDayRate += $whiteGloveDeliveryFees;
                $totalFullyAssembledRate += $whiteGloveDeliveryFees;
            }
        }

        $rates[] = (object)[
            "id" => "freight",
            "object" => "Rate",
            "service" => $service,
            "rate" => number_format($totalFreightRate, 2, '.', ''),
            "carrier" => $carrier,
        ];

        if ($totalNextDayRate > 0 && $nextDayAvailable) {
            $rates[] = (object)[
                "id" => "NextDay",
                "object" => "Rate",
                "service" => "Next Day",
                "rate" => number_format($totalNextDayRate, 2, '.', ''),
                "carrier" => $carrier,
            ];
        }

        if ($totalTwoDayRate > 0 && $twoDayAvailable) {
            $rates[] = (object)[
                "id" => "TwoDay",
                "object" => "Rate",
                "service" => "2-Day",
                "rate" => number_format($totalTwoDayRate, 2, '.', ''),
                "carrier" => $carrier,
            ];
        }

        if ($totalThreeDayRate > 0 && $threeDayAvailable) {
            $rates[] = (object)[
                "id" => "ThreeDay",
                "object" => "Rate",
                "service" => "3-Day",
                "rate" => number_format($totalThreeDayRate, 2, '.', ''),
                "carrier" => $carrier,
            ];
        }

        if ($totalFullyAssembledRate > 0 && $fullyAssembledAvailable) {
            $rates[] = (object)[
                "id" => "FullyAssembled",
                "object" => "Rate",
                "service" => "White Glove Fully Assembled",
                "rate" => number_format($totalFullyAssembledRate, 2, '.', ''),
                "carrier" => $carrier,
            ];
        }

        // Convert rates array to object
        $rates = (object)$rates;
        return $rates;
    }

    private function _getSignature(Order $order)
    {
        $totalQty = $order->getTotalQty();
        $totalWeight = $order->getTotalWeight();
        $totalWidth = $this->getAttributeTotalForOrder($order, 'width');
        $totalHeight = $this->getAttributeTotalForOrder($order, 'height');
        $totalLength = $this->getAttributeTotalForOrder($order, 'length');
        $updated = time();
        return md5($totalQty.$totalWeight.$totalWidth.$totalHeight.$totalLength.$updated);
    }

    private function _getProductRates()
    {
        $queryModel = Product::find();
        $queryModel->shippingRates = ':notempty:';
        $productsWithShippingRates = $queryModel->all();

        $rates = [];

        foreach ($productsWithShippingRates as $product) {
            $rates[$product->variants[0]->product->defaultSku] = json_decode($product->shippingRates, true);
        }

        return $rates;
    }

    private function _addressZone(Address $shippingAddress)
    {
        $allShippingZones = \craft\commerce\Plugin::getInstance()->shippingZones->getAllShippingZones();

        foreach ($allShippingZones as $zone) {
            if (!$zone->getIsCountryBased())
            {
                $states = [];
                $countries = [];
                foreach ($zone->states as $state)
                {
                    $states[] = $state->id;
                    $countries[] = $state->countryId;
                }

                $countryAndStateMatch = (bool) (in_array($shippingAddress->countryId, $countries) && in_array($shippingAddress->stateId, $states));
                $countryAndStateNameMatch = (bool) (in_array($shippingAddress->countryId, $countries) && strcasecmp($state->name, $shippingAddress->getStateText()) == 0);
                $countryAndStateAbbrMatch = (bool) (in_array($shippingAddress->countryId, $countries) && strcasecmp($state->abbreviation, $shippingAddress->getStateText()) == 0);

                if (($countryAndStateMatch || $countryAndStateNameMatch || $countryAndStateAbbrMatch))
                {
                    return $zone->id;
                }
            } else {
                $countries = [];
                foreach($zone->countries as $country){
                    $countries[] = $country->id;
                }
                if(in_array($shippingAddress->countryId, $countries)){
                    return $zone->id;
                }
            }
        }
    }

    private function getAttributeTotalForOrder(Order $order, $attribute)
    {
        $total = 0;
        foreach ($order->getLineItems() as $item) {
            $total += ($item->qty * $item->$attribute);
        }

        return $total;
    }
}
