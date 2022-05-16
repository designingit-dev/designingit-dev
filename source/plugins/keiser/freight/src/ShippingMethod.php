<?php
namespace keiser\freight;

use craft\commerce\base\Model;
use craft\commerce\base\ShippingMethodInterface;
use craft\commerce\base\ShippingRuleInterface;
use craft\commerce\elements\Order;
use DateTime;

/**
 * Shipping method model.
 *
 * @property string $cpEditUrl the control panel URL to manage this method and its rules
 * @property bool $isEnabled whether the shipping method is enabled for listing and selection by customers
 * @property array|ShippingRule[] $shippingRules rules that meet the `ShippingRules` interface
 * @property string $type the type of Shipping Method
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class ShippingMethod extends Model implements ShippingMethodInterface
{
    private $_rate;
    private $_handle;
    private $_name;
    private $_carrier;
    private $_service;
    private $_order;
    /**
     * @var DateTime|null
     * @since 3.4
     */
    public $dateCreated;

    /**
     * @var DateTime|null
     * @since 3.4
     */
    public $dateUpdated;

    /**
     * @var bool Is this the shipping method for the lite edition.
     */
    public $isLite = false;

    public function __construct($carrier, $service, $rate = null, $order = null)
    {
        $this->_rate = $rate;
        $this->_carrier = $carrier;
        $this->_service = $service;
        $this->_order = $order;
        $this->_handle = $carrier->id.$service['handle'];
        $this->_name = $service['name'];
        $this->id = $carrier->id;
        $this->name = $service['name'];
        $this->handle = $carrier->id.$service['handle'];
        $this->enabled = true;
    }
    // Properties
    // =========================================================================

    /**
     * @var int ID
     */
    public $id;

    /**
     * @var string Name
     */
    public $name;

    /**
     * @var string Handle
     */
    public $handle;

    /**
     * @var bool Enabled
     */
    public $enabled;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return "Keiser Freight";
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return (string)$this->name;
    }

    /**
     * @inheritdoc
     */
    public function getHandle(): string
    {
        return (string)$this->handle;
    }

    /**
     * @inheritdoc
     */
    public function getShippingRules(): array
    {
        return [new \keiser\freight\ShippingRule($this->_carrier, $this->_service, $this->_rate, $this->_order)];
    }

    /**
     * @inheritdoc
     */
    public function getIsEnabled(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl(): string
    {
        return "";
    }

    /**
     * @param Order $order
     * @return float
     */
    public function getPriceForOrder(Order $order)
    {
        $shippingRule = $this->getMatchingShippingRule($order);
        $lineItems = $order->getLineItems();

        if (!$shippingRule) {
            return 0;
        }

        $nonShippableItems = [];

        foreach ($lineItems as $item) {
            $purchasable = $item->getPurchasable();
            if($purchasable && !$purchasable->getIsShippable())
            {
                $nonShippableItems[$item->id] = $item->id;
            }
        }

        // Are all line items non shippable items? No shipping cost.
        if(count($lineItems) == count($nonShippableItems))
        {
            return 0;
        }

        $amount = $shippingRule->getBaseRate();

        return $amount;
    }

    /**
     * The first matching shipping rule for this shipping method
     *
     * @param Order $order
     * @return null|ShippingRuleInterface
     */
    public function getMatchingShippingRule(Order $order)
    {
        foreach ($this->getShippingRules() as $rule) {
            /** @var ShippingRuleInterface $rule */
            if ($rule->matchOrder($order)) {
                return $rule;
            }
        }

        return null;
    }

    /**
     * Is this shipping method available to the order?
     *
     * @param Order $order
     * @return bool
     */
    public function matchOrder(Order $order): bool
    {
        /** @var ShippingRuleInterface $rule */
        foreach ($this->getShippingRules() as $rule) {
            if ($rule->matchOrder($order)) {
                return true;
            }
        }

        return false;
    }
}
