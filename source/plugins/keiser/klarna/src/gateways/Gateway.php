<?php

namespace keiser\klarna\gateways;

use craft\commerce\base\RequestResponseInterface;
use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\PaymentSource;
use craft\commerce\models\Transaction;
use craft\commerce\Plugin as Commerce;
use craft\web\Response as WebResponse;
use Craft;
use keiser\klarna\errors\KlarnaException;
use keiser\klarna\models\forms\Payment;
use keiser\klarna\Plugin;
use keiser\klarna\responses\PaymentResponse;

class Gateway extends \craft\commerce\base\Gateway {

    public static function displayName(): string
    {
        return 'Keiser Klarna Gateway';
    }

    public function purchase(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface
    {
        $order = $transaction->getOrder();
        $data = Plugin::getInstance()->getService()->getOrderDataForSessionRequest($order);
        $data['auto_capture'] = true;
        try {
            $response = \keiser\klarna\Plugin::getInstance()->getService()->request("payments/v1/authorizations/{$form->token}/order", 'POST', $data);
            return $this->_handleSuccess($response);
        } catch (KlarnaException $e){
            return $this->_handleFailure($e);
        }
    }

    public function authorize(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface{}

    public function capture(Transaction $transaction, string $reference): RequestResponseInterface{}

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

    private function _handleFailure(KlarnaException $e): PaymentResponse {
        return new PaymentResponse([
            'status' => 'fail',
            'code' => $e->code,
            'message' => $e->message
        ]);
    }

    private function _handleSuccess($response): PaymentResponse{
        return new PaymentResponse([
            'id' => $response['order_id'],
            'status' => 'success',
            'code' => 200
        ]);
    }

}
