<?php

namespace keiser\commercehelpers\web\twig;

use craft\elements\MatrixBlock as MatrixBlockElement;
use craft\commerce\elements\Variant;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class KeiserCommerceHelpersExtension
 */
class KeiserCommerceHelpersExtension extends AbstractExtension
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Keiser Commerce Helpers Twig Extension';
    }

    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('shipmentTrackingLink', [
                $this,
                'shipmentTrackingLinkFunction',
            ]),
            new TwigFunction('allowedQuantity', [
                $this,
                'allowedQuantityFunction',
            ]),
        ];
    }

    /**
     * Returns the tracking url for a given shipment
     *
     * @param MatrixBlockElement $shipment
     * @return string Tracking url or an empty string.
     */
    public function shipmentTrackingLinkFunction(MatrixBlockElement $shipment)
    {
        if (!$shipment instanceof MatrixBlockElement) {
            return '';
        }

        $fieldValues = $shipment->getSerializedFieldValues();
        $shipmentTrackingNumber = urlencode(
            $fieldValues['shipmentTrackingNumber']
        );

        $carrier = $fieldValues['carrier'];
        $urls = [
            'fedex' => "https://www.fedex.com/apps/fedextrack/?tracknumbers={$shipmentTrackingNumber}",
            'oldDominion' => "https://www.odfl.com/Trace/standard.faces/?pro={$shipmentTrackingNumber}",
            'ups' => "https://www.ups.com/track?loc=null&tracknum={$shipmentTrackingNumber}&requester=WT/trackdetails",
            'estes' => 'https://www.estes-express.com/myestes/tracking/',
        ];

        return $urls[$carrier] ?? '';
    }


    /**
     * Returns the maximum allowed quantity for an item.
     * Maximum allowed quantity is either the avaiable stock or max quantity
     * for a product variant whichever is lesser.
     *
     * @param Variant $variant
     * @return int Maximum allowed quantity
     */
    public function allowedQuantityFunction(Variant $variant)
    {
        $useMaxQty = $variant->maxQty && ($variant->maxQty < $variant->stock || $variant->hasUnlimitedStock);
        return $useMaxQty ? $variant->maxQty : $variant->stock;
    }
}
