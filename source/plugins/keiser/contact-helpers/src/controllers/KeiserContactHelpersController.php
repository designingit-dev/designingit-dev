<?php

namespace keiser\contacthelpers\controllers;

use Craft;
use craft\commerce\controllers\CartController;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\web\Controller;
use barrelstrength\sproutforms\SproutForms;
use yii\web\Cookie;

class KeiserContactHelpersController extends Controller
{
    public $enableCsrfValidation = false;

    protected $allowAnonymous = [
        'find-keiser-rep',
        'validate-u-s-zip-code',
        'get-keiser-rep',
        'save-product-review',
        'get-available-shipping-methods',
        'get-available-payment-methods',
        'remove-white-glove-delivery-from-cart',
        'add-white-glove-delivery-to-cart',
        'close-banner',
        'get-asset-link',
        'get-sentry-last-event-id',
        'get-visitor-customisation-data',
        'check-order-validity'
    ];

    public function actionFindKeiserRep()
    {
        if(Craft::$app->getRequest()->getAcceptsJson()){
            $countryISO = Craft::$app->getRequest()->getParam('countryISO');
            if($countryISO){
                $institutionType = Craft::$app->getRequest()->getParam('institutionType');
                $interestedProducts = Craft::$app->getRequest()->getParam('interestedProducts');
                $zip = Craft::$app->getRequest()->getParam('zip');
                $representative = \keiser\contacthelpers\Plugin::getInstance()->service->findKeiserRep($countryISO, $zip, $institutionType, $interestedProducts);
                $formName = null;
                if(Craft::$app->getRequest()->getParam('formName', null)){
                    $formName = Craft::$app->getRequest()->getParam('formName');
                }
                $formAnchor = null;
                if(Craft::$app->getRequest()->getParam('formAnchor', null)){
                    $formAnchor = Craft::$app->getRequest()->getParam('formAnchor');
                }
                if($representative){
                    return $this->asJson($this->buildRepDetails($representative, $formName, $formAnchor));
                }
            }
            return $this->asJson([
                'status' => 'error'
            ]);
        }
        return false;
    }

    public function actionValidateUSZipCode(){
        if(Craft::$app->getRequest()->getAcceptsJson()){
            $zip = Craft::$app->getRequest()->getParam('zip');
            if($zip){
                $allZipCodes = file_get_contents(__DIR__ . '/../resources/us_zip_codes.csv');
                $allZipCodes = explode("\n", $allZipCodes);
                if(in_array($zip, $allZipCodes)){
                    return $this->asJson([
                        'status' => 'success'
                    ]);
                }
            }
            return $this->asJson([
                'status' => 'error'
            ]);
        }
        return false;
    }

    private function buildRepDetails($representative, $formName = null, $formAnchor = null){
        $result = [
            'status' => 'success',
            'repName' => $representative->title,
            'repContact' => $representative->contactBlock->getParsedContent(),
            'repEmail' => $representative->email,
            'repDesignation' => $representative->repDesignation,
            'repPhone' => $representative->repPhone,
            'repTollFree' => $representative->repTollFreeNumber
        ];
        if(isset($representative->repWebsite) && $representative->repWebsite){
            $result['repWebsite'] = $representative->repWebsite;
        }
        if(isset($representative->repImage) && $representative->repImage->first()){
            $result['repImage'] = $representative->repImage->one()->getUrl('xsmall');
        }
        if(isset($representative->repRoundedCornersImage) && $representative->repRoundedCornersImage->first()){
            $result['repRoundedCornersImage'] = $representative->repRoundedCornersImage->one()->getUrl();
        }
        if(isset($representative->repVP[0])){
            $result['repVPEmail'] = $representative->repVP[0]->email;
        }
        if(!$formName){
            $formName = 'Contact Us (Sales)';
        }
        $redirectUrl = '/about-us/contact-us/thank-you?formName='. urlencode($formName);
        if($formAnchor){
            $redirectUrl .= '&formAnchor=' . urlencode($formAnchor);
        }
        $result['redirect'] = Craft::$app->getSecurity()->hashData($redirectUrl);
        return $result;
    }

    public function actionGetKeiserRep(){
        $this->requireAcceptsJson();
        $rep = \keiser\contacthelpers\Plugin::getInstance()->service->getKeiserRep(Craft::$app->getRequest()->getParam('repHandle'));
        if($rep){
            return $this->asJson($this->buildRepDetails($rep));
        }
        return $this->asJson([
            'status' => 'error'
        ]);
    }

    public function actionSaveProductReview(){
        $form = SproutForms::$app->forms->getFormByHandle('productReview');
        $fields = Craft::$app->getRequest()->getParam('fields');
        if($form){
            $queryModel = \barrelstrength\sproutforms\elements\Entry::find();
            // It is required to call this method first and set the form handle so that system will know which
            // content table is required for lookup and then we can add any field values for searching as desired
            $queryModel->formHandle($form->handle);
            $queryModel->originEntryId = $fields['originEntryId'];
            $entryExists = $queryModel->one();
            if($entryExists){
                Craft::$app->elements->deleteElementById($entryExists->id);
            }
            $entry = new \barrelstrength\sproutforms\elements\Entry();
            Craft::$app->getContent()->populateElementContent($entry);
            $entry->formId = $form->id;
            $entry->formHandle = $form->handle;
            $entry->formGroupId = "0";
            $entry->formName = $form->name;
            $entry->fieldLayoutId = $form->getFieldLayout()->id;
            $entry->setAttributes([
                'statusId' => 1,
                'ipAddress' => Craft::$app->getRequest()->getUserIP(),
                'userAgent' => Craft::$app->getRequest()->getUserAgent(),
                'statusHandle' => 'unread',
                'enabled' => '1',
                'archived' => '0',
                'enabledForSite' => '1',
                'title' => Craft::$app->getRequest()->getParam('title'),
                'status' => 'blue',
            ]);
            $fieldsLocation = Craft::$app->getRequest()->getBodyParam('fieldsLocation', 'fields');
            $entry->setFieldValuesFromRequest($fieldsLocation);
            $entry->setFieldParamNamespace($fieldsLocation);
            $entry->statusId = $entry->statusId != null
                ? $entry->statusId
                : SproutForms::$app->entries->getDefaultEntryStatusId();

            // Render the Entry Title
            try {
                $entry->title = Craft::$app->getView()->renderObjectTemplate($this->form->titleFormat, $entry);
            } catch (\Exception $e) {
                SproutForms::error('Title format error: '.$e->getMessage());
            }
            $db = Craft::$app->getDb();
            $transaction = $db->beginTransaction();
            $success = Craft::$app->getElements()->saveElement($entry);
            if (!$success) {
                SproutForms::error('Couldn’t save Element on saveEntry service.');
                $transaction->rollBack();
                return false;
            }
            SproutForms::info('Form Entry Element Saved.');
            $transaction->commit();
            return $this->asJson([
                'status' => $success
            ]);
        }
    }

    public function actionGetAvailableShippingMethods(){
        if($this->checkIfCartIsEmpty()){
            return $this->asErrorJson('Cart is empty');
        }
        $response = Craft::$app->runAction('commerce/cart/update-cart');
        if(isset($response->data['error'])){
            return $this->asErrorJson($response->data['error']);
        }
        $cart = \craft\commerce\Plugin::getInstance()->getCarts()->cart;
        $stateName = '';
        if(isset($cart->shippingAddress->state) && isset($cart->shippingAddress->state->name)){
            $stateName = $cart->shippingAddress->state->name;
        }
        $response = [
            'status' => 'success',
            'shippingOptions' => Craft::$app->view->renderTemplate('checkout/_shipping.html', [
                'cart' => $cart
            ]),
            'cart' => Craft::$app->view->renderTemplate('checkout/_reviewTableCondensed.html', [
                'cart' => $cart,
                'showTotals' => false,
                'showShippingSubtotal' => true
            ]),
            'whiteGloveRemoved' => Craft::$app->getSession()->getFlash('whiteGloveRemoved', false),
            'nonWhiteGloveFeesAdded' => Craft::$app->getSession()->getFlash('nonWhiteGloveFeesAdded', false),
            'nonWhiteGloveFeesAddedContent' => Craft::$app->getSession()->getFlash('nonWhiteGloveFeesAddedContent', ''),
            'rudderAnalyticsIdentify' => [
                'userId' => hash('sha256', $cart->email),
                'traits' => [
                    'email' => $cart->email,
                    'first_name' => $cart->shippingAddress->firstName,
                    'last_name' => $cart->shippingAddress->lastName,
                    'fullName' => $cart->shippingAddress->firstName . ' ' . $cart->shippingAddress->lastName,
                    'phoneNumber' => $cart->shippingAddress->phone,
                    'city' => $cart->shippingAddress->city,
                    'state' => $stateName,
                    'countryISO' => $cart->shippingAddress->country->iso,
                    'zip' => $cart->shippingAddress->zipCode,
                    'isB2C' =>  true
                ]
            ]
        ];
        return $this->asJson($response);
    }

    public function actionGetAvailablePaymentMethods(){
        if($this->checkIfCartIsEmpty()){
            return $this->asErrorJson('Cart is empty');
        }
        $response = Craft::$app->runAction('commerce/cart/update-cart');
        if(isset($response->data['error'])){
            return $this->asErrorJson($response->data['error']);
        }
        $cart = \craft\commerce\Plugin::getInstance()->getCarts()->cart;
        $response = [
            'status' => 'success',
            'paymentOptions' => Craft::$app->view->renderPageTemplate('checkout/_payment.html', [
                'cart' => $cart
            ]),
            'cart' => Craft::$app->view->renderTemplate('checkout/_reviewTableCondensed.html', [
                'cart' => $cart,
                'showTotals' => true,
                'showShippingSubtotal' => false
            ]),
        ];
        Craft::$app->response->setStatusCode(200);
        return $this->asJson($response);
    }

    public function actionAddWhiteGloveDeliveryToCart(){
        return $this->asJson(
            \keiser\commercehelpers\Plugin::getInstance()->keiserCommerceHelpers->addWhiteGloveDeliveryToCart(Craft::$app->getRequest()->getParam('lineItemId'))
        );
    }

    public function actionRemoveWhiteGloveDeliveryFromCart(){
        return $this->asJson(
            \keiser\commercehelpers\Plugin::getInstance()->keiserCommerceHelpers->removeWhiteGloveDeliveryFromCart(Craft::$app->getRequest()->getParam('lineItemId'))
        );
    }

    public function actionCloseBanner(){
        if($bannerId = Craft::$app->getRequest()->getParam('bannerId')){
            $cookieName = 'closedBanners';
            $closedBanners = [];
            $cookieCollection = Craft::$app->getRequest()->getCookies();
            if($cookieCollection->has($cookieName)) {
                $closedBanners = $cookieCollection[$cookieName]->value;
            }
            $closedBanners[] = $bannerId;
            $cookie = new Cookie([
                'name' => $cookieName,
                'value' => $closedBanners,
                'expire' => time() + 60 * 60 * 24 * 365,
                'secure' => true,
                'httpOnly' => true,
            ]);
            Craft::$app->response->getCookies()->add($cookie);
            return $this->asJson([
                'success' => true
            ]);
        }
        return $this->asJson([
            'success' => false,
            'error' => 'Banner not found'
        ]);
    }

    public function actionGetAssetLink(){
        if(Craft::$app->getRequest()->getAcceptsJson()) {
            $assetQueryModel = Asset::find();
            $assetQueryModel->id = Craft::$app->getRequest()->getParam('assetId');
            $asset = $assetQueryModel->one();
            if($asset){
                return $this->asJson([
                    'success' => true,
                    'url' => $asset->getUrl()
                ]);
            }
            return $this->asJson([
                'success' => false
            ]);
        }
    }

    private function checkIfCartIsEmpty(){
        try {
            if(\craft\commerce\Plugin::getInstance()->getCarts()->getCart()->getTotalPrice() <= 0){
                return true;
            }
        } catch(\Exception $e){
            return false;
        } catch (\Throwable $e){
            return false;
        }
        return false;
    }

    public function actionGetSentryLastEventId(){
        $sentryLastEventId = Craft::$app->getSession()->getFlash('sentryLastEventId');
        $id = $sentryLastEventId;
        if(is_array($sentryLastEventId)){
            foreach($sentryLastEventId as $eventId){
                $id = $eventId;
            }
        }
        if($id){
            return $this->asJson([
                'success' => true,
                'sentryLastEventId' => $id
            ]);
        }
        return $this->asJson([
            'success' => false
        ]);
    }

    public function actionGetVisitorCustomisationData(){
        $contactHelpersService = \keiser\contacthelpers\Plugin::getInstance()->service;
         return $this->asJson([
             'status' => 'success',
             'geolocation' => $contactHelpersService->getVisitorGeolocation(),
             'isForeignVisitor' => $contactHelpersService->isForeignVisitor(),
             'campaignParameters' => $contactHelpersService->getCampaignParameters(Craft::$app->getRequest()->getParam('url')),
             'numItemsInCart' => count(\craft\commerce\Plugin::getInstance()->getCarts()->getCart()->getLineItems()),
             'banners' => $contactHelpersService->getBanners(Craft::$app->getRequest()->getParam('path'))
         ]);
    }

    public function actionCheckOrderValidity(){
        $orderValid = true;
        try {
            $lineItems = \craft\commerce\Plugin::getInstance()->getCarts()->getCart()->getLineItems();
            foreach($lineItems as $lineItem){
                if(!$lineItem->getPurchasable()->hasStock()){
                    $orderValid = false;
                }
            }
        } catch(\Exception $e){
            return $this->asJson([
                'status' => 'error',
                'error' => 'Cart is empty'
            ]);
        }
        return $this->asJson([
            'status' => 'success',
            'orderValid' => $orderValid
        ]);
    }
}