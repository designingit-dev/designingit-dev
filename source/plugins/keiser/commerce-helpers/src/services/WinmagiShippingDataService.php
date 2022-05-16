<?php

namespace keiser\commercehelpers\services;

use Craft;
use craft\base\Component;
use craft\commerce\Plugin as Commerce;
use craft\commerce\elements\Order;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\elements\MatrixBlock;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class WinmagiShippingDataService extends Component
{
    /**
     * Makes a call to the WinMagi API to retrieve order shipments.
     * Shipments are matched on Craft's referenceId value to WinMagi's purchaseOrderNum.
     * e.g.: $purchaseOrderNum == $order->reference
     *
     * @param array $referenceIds Array of order reference IDs.
     * @return Array
     */
    public function getShipments(array $referenceIds)
    {
        $client = new Client([
            'base_uri' => getenv('WINMAGI_API_URL'),
            'timeout' => 2 * 60.0,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => ['basic ' . getenv('WINMAGI_AUTHORIZATION')],
                'Apikey' => getenv('WINMAGI_API_KEY'),
            ],
        ]);

        try {
            $ordersQuery = array_map(function ($id) {
                /**
                 * Convert the $id to string since the order reference number can sometimes be an integer in Craft and
                 * would be rejected by the Magi API Eg: 8927718
                 */
                return ['purchaseOrderNum' => (string)$id];
            }, $referenceIds);

            $response = $client->request('POST', '/order/shipping/search', [
                'body' => json_encode([
                    'query' => $ordersQuery,
                ]),
            ]);

            $responseBody = json_decode($response->getBody(), true);

            // if ($responseBody['success'] !== true) {
            //   // Error handling?
            // }

            return $responseBody['data'] ?? [];
        } catch (BadResponseException $e) {
            // Trigger Craft's native exception logging for Sentry and expose repeated API failures.
            Craft::error(
                sprintf(
                    'Could not fetch WinMagi shipping data: [%s] - %s',
                    $e->getResponse()->getStatusCode(),
                    $e->getMessage()
                ),
                __METHOD__
            );

            // If the API call fails for any reason, we return an empty array.
            return [];
        }
    }

    /**
     * Adds shipment tracking data from WinMagi api to an order entry, and
     * sets the entry status to "shipped" which triggers a shipment email.
     * If a tracking number already exists, the adding shipment information will be skipped.
     *
     * @param Order $order The order entry to update.
     * @param array $shipments Shipments data to add.
     * @return Array
     */
    public function addShipmentTracking(Order $order, array $shipments)
    {
        if (empty($shipments)) {
            return;
        }

        $skus = [];
        foreach ($order->getLineItems() as $lineItem) {
            $skus[] = $lineItem->getPurchasable()->getSku();
        }

        $variants = Variant::find()->sku($skus);
        $products = Product::find()
            ->hasVariant($variants)
            ->ids();

        $carriers = [
            'DHL' => 'dhl',
            'FEDEX' => 'fedex',
            'OLD DOMINION' => 'oldDominion',
            'TROY' => 'troyTrucking',
            'UPS' => 'ups',
            'USPS' => 'usps',
            'XPO' => 'xpo',
        ];

        $shipmentsField = 'shipmentTrackingNumbers';

        $field = Craft::$app->getFields()->getFieldByHandle($shipmentsField);
        $fieldValue = $order->getFieldValue($shipmentsField);
        $serializedValue = $field->serializeValue($fieldValue, $order);

        $trackingNumbers = array_map(function ($block) {
            return $block['fields']['shipmentTrackingNumber'];
        }, $serializedValue);

        foreach ($shipments as $shipment) {
            if (in_array($shipment['trackingNo'], $trackingNumbers)) {
                continue;
            }

            $carrier = $shipment['carrier'];
            $serializedValue[] = [
                'type' => 'shipment',
                'fields' => [
                    'products' => $products,
                    'shipmentTrackingNumber' => $shipment['trackingNo'],
                    'carrier' => $carriers[$carrier] ?? '',
                ],
            ];
        }

        $order->setFieldValue($shipmentsField, $serializedValue);

        $shippedStatus = Commerce::getInstance()
            ->getOrderStatuses()
            ->getOrderStatusByHandle('shipped');

        $order->orderStatusId = $shippedStatus->id;

        Craft::$app->elements->saveElement($order);
    }
}
