<?php

namespace keiser\affirm\models\forms;

use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\PaymentSource;

class Payment extends BasePaymentForm {

    public $token;

    // Public methods
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);
        if (isset($values['token'])) {
            $this->token = $values['token'];
        }
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [[['token'], 'required']];
    }

    /**
     * @inheritdoc
     */
    public function populateFromPaymentSource(PaymentSource $paymentSource)
    {
        $this->token = $paymentSource->token;
    }

}
