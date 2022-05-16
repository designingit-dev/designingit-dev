<?php

namespace keiser\stripe\models\forms;

use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\PaymentSource;

class Payment extends BasePaymentForm {

    public $paymentIntent;

    // Public methods
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);
        if (isset($values['paymentIntent'])) {
            $this->paymentIntent = $values['paymentIntent'];
        }
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [[['paymentIntent'], 'required']];
    }

    /**
     * @inheritdoc
     */
    public function populateFromPaymentSource(PaymentSource $paymentSource)
    {
        $this->paymentIntent = $paymentSource->paymentIntent;
    }

}
