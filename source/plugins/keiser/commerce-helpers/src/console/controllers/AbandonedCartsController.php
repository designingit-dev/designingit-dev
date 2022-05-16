<?php

namespace keiser\commercehelpers\console\controllers;

use yii\console\Controller;
use \DrewM\MailChimp\MailChimp;

class AbandonedCartsController extends Controller {

    public function actionSendAbandonedCartEmail(){
        if(getenv('MAILCHIMP_ABANDONED_CARTS_LIST_ID')){
            /**
             * @var $keiserCommerceHelpersService \keiser\commercehelpers\services\KeiserCommerceHelpersService
             */
            $keiserCommerceHelpersService = \keiser\commercehelpers\Plugin::getInstance()->keiserCommerceHelpers;
            $abandonedCarts = $keiserCommerceHelpersService->getAbandonedCarts('P2D');
            foreach($abandonedCarts as $key => $abandonedCart){
                if($abandonedCart['email'] == 'guest'){
                    unset($abandonedCarts[$key]);
                }
            }
            $mailchimp = new MailChimp(getenv('MAILCHIMP_API_KEY'));
            $subscriptionBatchJob = $mailchimp->new_batch();
            $counter = 0;
            foreach($abandonedCarts as $abandonedCart){
                $subscriptionBatchJob->post(
                    'op' . $counter++,
                    '/lists/' . getenv('MAILCHIMP_ABANDONED_CARTS_LIST_ID') . '/members',
                    [
                        'email_address' => $abandonedCart['email'],
                        'merge_fields' => [
                            'FNAME' => $abandonedCart['firstName'],
                            'LNAME' => $abandonedCart['lastName'],
                            'CSHORTNUM' => $abandonedCart['shortNumber'],
                            'CSKULIST' => $abandonedCart['sku'],
                            'CTOTAL' => $abandonedCart['total']
                        ],
                        'status' => 'subscribed'
                    ]);
            }

            $existingCustomers = $keiserCommerceHelpersService->getExistingCustomersEmailList('P60D');
            foreach ($existingCustomers as $customerEmail){
                $subscriptionBatchJob->post(
                    'op' . $counter++,
                    '/lists/' . getenv('MAILCHIMP_ABANDONED_CARTS_LIST_ID') . '/members',
                    [
                        'email_address' => $customerEmail,
                        'status' => 'unsubscribed'
                    ]);
            }
            $batchJobID = $subscriptionBatchJob->execute();
            print_r($batchJobID);
        }
    }

    public function actionCheckBatchCallStatus($batchId){
        $mailchimp = new MailChimp(getenv('MAILCHIMP_API_KEY'));
        $subscriptionBatchJon = $mailchimp->new_batch($batchId);
        print_r($subscriptionBatchJon->check_status());
    }

}