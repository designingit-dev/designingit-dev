<?php

namespace keiser\stripe\gateways;

use craft\commerce\base\RequestResponseInterface;
use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\PaymentSource;
use craft\commerce\models\Transaction;
use craft\commerce\Plugin as Commerce;
use craft\web\Response as WebResponse;
use Craft;
use keiser\stripe\errors\StripeException;
use keiser\stripe\models\forms\Payment;
use keiser\stripe\responses\PaymentResponse;

class Gateway extends \craft\commerce\base\Gateway {

    public static function displayName(): string
    {
        return Craft::t('keiser-stripe', 'Keiser Stripe SCA Compliant');
    }

    public function purchase(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface
    {
        return $this->_validatePaymentIntent($transaction, $form->paymentIntent);
    }

    private function _validatePaymentIntent(Transaction $transaction, $paymentIntentId){
        \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));
        $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
        if($paymentIntent->status == 'succeeded'){
            \Stripe\PaymentIntent::update($paymentIntentId, [
                'metadata' => [
                    'order_id' => $transaction->getOrder()->id,
                    'order_number' => $transaction->getOrder()->number,
                    'transaction_reference' => $transaction->hash
                ]
            ]);
            return new PaymentResponse([
                'status' => 'success',
                'code' => 200,
                'id' => $paymentIntentId,
                'message' => $paymentIntent
            ]);
        } else {
            return new PaymentResponse([
                'status' => 'fail',
                'code' => 400,
                'message' => $paymentIntent->status
            ]);
        }
    }

    public function capture(Transaction $transaction, string $reference): RequestResponseInterface {}

    public function authorize(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface {}

    public function refund(Transaction $transaction): RequestResponseInterface{}

    public function getPaymentFormModel(): BasePaymentForm
    {
        return new Payment();
    }

    public function getPaymentFormHtml(array $params){}

    public function supportsPurchase(): bool
    {
        return true;
    }

    public function supportsAuthorize(): bool
    {
        return false;
    }

    public function supportsCapture(): bool
    {
        return false;
    }

    public function supportsRefund(): bool
    {
        return false;
    }

    public function supportsPartialRefund(): bool
    {
        return false;
    }

    public function supportsCompleteAuthorize(): bool
    {
       return false;
    }

    public function supportsPaymentSources(): bool
    {
        return false;
    }

    public function supportsWebhooks(): bool
    {
        return false;
    }

    public function supportsCompletePurchase(): bool
    {
        return false;
    }

    public function createPaymentSource(BasePaymentForm $sourceData, int $userId): PaymentSource {}

    public function deletePaymentSource($token): bool {}

    public function completeAuthorize(Transaction $transaction): RequestResponseInterface {}

    public function processWebHook(): WebResponse {}

    public function completePurchase(Transaction $transaction): RequestResponseInterface {}

}
