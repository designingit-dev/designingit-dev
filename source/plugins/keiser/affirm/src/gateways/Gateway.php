<?php

namespace keiser\affirm\gateways;

use craft\commerce\base\RequestResponseInterface;
use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\PaymentSource;
use craft\commerce\models\Transaction;
use craft\commerce\Plugin as Commerce;
use craft\web\Response as WebResponse;
use Craft;
use keiser\affirm\errors\AffirmException;
use keiser\affirm\models\forms\Payment;
use keiser\affirm\responses\PaymentResponse;

class Gateway extends \craft\commerce\base\Gateway {

    public static function displayName(): string
    {
        return 'Affirm';
    }

    public function purchase(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface
    {
        $authResponse = $this->authorize($transaction, $form);
        return $this->capture($transaction, $authResponse->getTransactionReference());
    }

    public function capture(Transaction $transaction, string $reference): RequestResponseInterface
    {
        try {
            $this->_request('charges/' . $reference . '/capture', 'POST', '');
            return new PaymentResponse([
                'id' => $reference,
                'status' => 'success',
                'code' => 200
            ]);
        } catch (AffirmException $e){
            return $this->_handleFailure($e);
        }
    }

    public function authorize(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface
    {
        $data = [
            'checkout_token' => $form->token
        ];
        try {
            $response = $this->_request('charges/', 'POST', $data);
            return $this->_handleSuccess($response);
        } catch (AffirmException $e){
            return $this->_handleFailure($e);
        }
    }

    public function refund(Transaction $transaction): RequestResponseInterface
    {
        $currency = Commerce::getInstance()->getCurrencies()->getCurrencyByIso($transaction->paymentCurrency);
        $data = [
            'charge_id' => $transaction->reference,
            'amount' => $transaction->paymentAmount * (10 ** $currency->minorUnit)
        ];
        \keiser\affirm\Plugin::log($data);
        try {
            $response = $this->_request('charges/' . $transaction->reference . '/refund', 'POST', $data);
            return $this->_handleSuccess($response);
        } catch (AffirmException $e){
            return $this->_handleFailure($e);
        }
    }

    public function getPaymentFormModel(): BasePaymentForm
    {
        return new Payment();
    }

    public function getPaymentFormHtml(array $params)
    {
        // TODO: Implement getPaymentFormHtml() method.
    }

    public function supportsPurchase(): bool
    {
        return true;
    }

    public function supportsAuthorize(): bool
    {
        return true;
    }

    public function supportsCapture(): bool
    {
        return true;
    }

    public function supportsRefund(): bool
    {
        return true;
    }

    public function supportsPartialRefund(): bool
    {
        return true;
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

    private function _request($path, $method, $data){
        $data = json_encode($data);
        $headers = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ];
        $keyPair = getenv('AFFIRM_PUBLIC_KEY') . ':' . getenv('AFFIRM_PRIVATE_KEY');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, getenv('AFFIRM_API_URL') . $path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $keyPair);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $response = json_decode($response, true);
        if($status === 200){
            return $response;
        }
        \keiser\affirm\Plugin::log($response);
        $field = (isset($response['field']) ? $response['field'] : '');
        $type = (isset($response['type']) ? $response['type'] : '');
        throw new AffirmException($response['message'], $response['code'], $field, $type);
        return false;
    }

    private function _handleFailure(AffirmException $e): PaymentResponse {
        return new PaymentResponse([
            'status' => 'fail',
            'code' => $e->code,
            'message' => $e->message
        ]);
    }

    private function _handleSuccess($response): PaymentResponse{
        return new PaymentResponse([
            'id' => $response['id'],
            'status' => 'success',
            'code' => 200
        ]);
    }

}
