<?php

namespace keiser\commercehelpers\services;

use craft\commerce\elements\Variant;
use craft\commerce\elements\Product;
use craft\commerce\events\AddressEvent;
use craft\elements\Entry;
use craft\helpers\App;
use \DrewM\MailChimp\MailChimp;

use Craft;
use \craft\base\Component;
use \craft\commerce\elements\Order;
use craft\mail\Message;
use keiser\commercehelpers\models\KeiserCommerceShippingEmailLog;
use keiser\commercehelpers\models\KeiserCommerceWinmagiPushLog;
use yii\web\Cookie;

class KeiserCommerceHelpersService extends Component {

    public function mailChimpSubscribe(Order $order, $shipmentTrackingNumber = null){
        $address = \craft\commerce\Plugin::getInstance()->addresses->getAddressById($order->shippingAddressId);
        $product = [];
        $wgaProduct = [];
        foreach($order->lineItems as $purchasable){
            $options = $purchasable->getOptions();
            if (isset($options['whiteGloveDelivery']) && $options['whiteGloveDelivery']) {
                $wgaProduct[] = $purchasable->sku;
            }
            $product[] = $purchasable->sku;
        }
        $mailChimpSubscriberExists = $this->mailChimpSubscriberExists($order->email);
        if($mailChimpSubscriberExists){
            if(isset($mailChimpSubscriberExists['SKU'])){
                $productsAlreadyPurchased = explode('|',$mailChimpSubscriberExists['SKU']);
                $product = array_unique(array_merge($product, $productsAlreadyPurchased));
            }
            if(isset($mailChimpSubscriberExists['WGASKU'])){
                $wgaProductsAlreadyPurchased = explode('|',$mailChimpSubscriberExists['WGASKU']);
                $wgaProduct = array_unique(array_merge($wgaProduct, $wgaProductsAlreadyPurchased));
            }
        }
        $product = implode('|', $product);
        $wgaProduct = implode('|', $wgaProduct);
        $contUS = 'True';
        if($address->getCountry()->iso != 'US' || in_array($address->getState()->abbreviation, ['AK', 'HI'])){
            $contUS = 'False';
        }
        $shipMethod = 'Freight';
        $mergeFields = [
            'FNAME' => $address->firstName,
            'LNAME' => $address->lastName,
            'SKU' => $product,
            'WGASKU' => $wgaProduct,
            'CONTUS' => $contUS,
            'SHIPMETHOD' => $shipMethod
        ];
        $state = '';
        if($address->getState()){
            $state = $address->getState()->abbreviation;
        }
        if($shipmentTrackingNumber){
            $address = [
                'addr1' => $address->address1,
                'addr2' => $address->address2,
                'city' => $address->city,
                'state' => $state,
                'zip' => $address->zipCode,
                'country' => $address->countryText
            ];
            $dateOrdered = $order->dateOrdered->format('M d, Y');
            $mergeFields = array_merge($mergeFields, [
                'SHIPPED' => 'TRUE',
                'ADDRESS' => $address,
                'ORDERNUM' => strtoupper($order->shortNumber),
                'ORDERDATE' => $dateOrdered,
                'SHIPTRACK' => $shipmentTrackingNumber,
                'ADDRESS1' => $address['addr1'],
                'ADDRESS3' => "{$address['city']}, {$address['state']} {$address['zip']}",
            ]);
            if($address['addr2']){
                $mergeFields['ADDRESS2'] = $address['addr2'];
            }
        }
        $this->mailChimpSubscriberUpdate($order->email, $mergeFields);
    }

    public function mailChimpSubscriberExists($email, $mailChimpListId = null){
        if (getenv('MAILCHIMP_API_KEY')) {
            $mailChimp = new MailChimp(getenv('MAILCHIMP_API_KEY'));
            $mailChimpSubscriber = $mailChimp->subscriberHash($email);
            if($mailChimpListId){
                $listId = $mailChimpListId;
            } else {
                $listId = getenv('MAILCHIMP_LIST_ID');
            }
            $result = $mailChimp->get("lists/{$listId}/members/{$mailChimpSubscriber}", [
                'fields' => 'merge_fields'
            ]);
            if($mailChimp->success()){
                return $result['merge_fields'];
            }
        }
        return false;
    }

    public function mailChimpSubscriberUpdate($email, $mergeFields = null, $mailChimpListId = null, $requireDoubleOptIn = false){
        if (getenv('MAILCHIMP_API_KEY')) {
            $mailChimp = new MailChimp(getenv('MAILCHIMP_API_KEY'));
            $listId = ($mailChimpListId ? $mailChimpListId : getenv('MAILCHIMP_LIST_ID'));
            $mailChimpSubscriber = $mailChimp->subscriberHash($email);
            $options = [
                'email_address' => $email,
            ];
            if($requireDoubleOptIn){
                $options['status_if_new'] = 'pending';
            } else {
                $options['status_if_new'] = 'subscribed';
            }
            if($mergeFields){
                $options['merge_fields'] = $mergeFields;
            }
            $mailChimp->put(
                "lists/{$listId}/members/{$mailChimpSubscriber}",
                $options
            );
            if (Craft::$app->config->general->devMode){
                if ($mailChimp->success()) {
                    \keiser\commercehelpers\Plugin::getInstance()::log("MailChimp Subscription: SUCCESS Email ID: {$email}");
                } else {
                    \keiser\commercehelpers\Plugin::getInstance()::log("MailChimp Subscription: ERROR Email ID: {$email} Message: " . $mailChimp->getLastError());
                }
            }
        }
    }

    public function getCommerceStatesByCountryISO($iso){
        $filteredStates = [];
        $commerce = \craft\commerce\Plugin::getInstance();
        $country = $commerce->countries->getCountryByIso($iso);
        if($country){
            $states = $commerce->states->getAllStates();
            foreach($states as $state){
                if($state->countryId === $country->id){
                    $filteredStates[] = $state;
                }
            }
        }
        return $filteredStates;
    }

    public function updateVerifiedPurchase(\barrelstrength\sproutforms\elements\Entry $sproutFormsEntry){
        $entryContent = $sproutFormsEntry->getAttributes();
        if($entryContent['email'] && $entryContent['productSlug'] && $entryContent['originCountry'] == getenv('HOME_COUNTRY') && !Craft::$app->getRequest()->getIsCpRequest()){
            $productQueryModel = \craft\commerce\elements\Product::find();
            $productQueryModel->slug = $entryContent['productSlug'];
            $product = $productQueryModel->one();
            $orderQueryModel = \craft\commerce\elements\Order::find();
            $orderQueryModel->isCompleted = true;
            $orderQueryModel->email = $entryContent['email'];
            $orderQueryModel->limit = null;
            $orderQueryModel->hasPurchasables = [
                $product->variants[0]
            ];
            $order = $orderQueryModel->one();
            if($order){
                $sproutFormsEntry->setAttributes(['isVerifiedPurchase' => true]);
            } else {
                $sproutFormsEntry->setAttributes(['isVerifiedPurchase' => false]);
            }
            $this->saveSproutFormsEntry($sproutFormsEntry);
        }
    }

    public function updateAuthToken(\barrelstrength\sproutforms\elements\Entry $sproutFormsEntry){
        $entryContent = $sproutFormsEntry->getAttributes();
        $authToken = time() + rand(1000000000, 9999999999);
        $sproutFormsEntry->setAttributes([
            'authToken' => $authToken,
            'editUrl' => Craft::$app->sites->getCurrentSite()->baseUrl . $entryContent['productSlug'] . '-review?reviewid=' . $entryContent['id'] .'&authtoken=' . $authToken
        ]);
        return $this->saveSproutFormsEntry($sproutFormsEntry);
    }

    private function saveSproutFormsEntry(\barrelstrength\sproutforms\elements\Entry $entry){
        try {
            $success = Craft::$app->getContent()->saveContent($entry);
            if(!$success){
                \keiser\formhelpers\Plugin::log("Couldn't save auto updated attributes in Sprout Forms Entry");
            }
        } catch(\Exception $e){
            \keiser\formhelpers\Plugin::log($e->getMessage());
        }
    }

    public function onBeforeSaveAddress(AddressEvent $event){
        $address = $event->address;

        if (empty($address->address1))
        {
            $address->addError('address1', Craft::t('keiser-commerce-helpers', 'Address 1 cannot be blank.'));
            $event->performAction = false;
        }

        if (empty($address->city))
        {
            $address->addError('city', Craft::t('keiser-commerce-helpers','City cannot be blank.'));
            $event->performAction = false;
        }

        if (empty($address->zipCode))
        {
            $address->addError('zipCode', Craft::t('keiser-commerce-helpers','Zip Code cannot be blank.'));
            $event->performAction = false;
        }

        if (empty($address->phone) && getenv('HOME_COUNTRY') == 'US')
        {
            $address->addError('phone', Craft::t('keiser-commerce-helpers','Phone cannot be blank.'));
            $event->performAction = false;
        }

        if (empty($address->countryId))
        {
            $address->addError('countryId', Craft::t('keiser-commerce-helpers','Country cannot be blank.'));
            $event->performAction = false;
        }

        if (empty($address->stateId) && getenv('HOME_COUNTRY') == 'US')
        {
            $address->addError('stateId', Craft::t('keiser-commerce-helpers','State cannot be blank.'));
            $event->performAction = false;
        }
    }

    public function getRatingStarsHtml($rating){
        $rating = (float) $rating;
        $html = '';
        $fullStars = (int) $rating;
        for($i = 1; $i <= $fullStars; $i++){
            $html .= '<img class="ratingStar" src="/assets/images/reviews/star-full.png" />';
        }
        $decimalPart = $rating - $fullStars;
        if($decimalPart > 0 && $decimalPart <= 0.25){
            $html .= '<img class="ratingStar" src="/assets/images/reviews/star-25.png" />';
        } else if($decimalPart > 0.25 && $decimalPart <= 0.5){
            $html .= '<img class="ratingStar" src="/assets/images/reviews/star-50.png" />';
        } else if($decimalPart > 0.5){
            $html .= '<img class="ratingStar" src="/assets/images/reviews/star-75.png" />';
        }
        $emptyStars = (int) (5 - $rating);
        for($i = 1; $i <= $emptyStars; $i++){
            $html .= '<img class="ratingStar" src="/assets/images/reviews/star-empty.png" />';
        }
        return $html;
    }

    public function wordlimit($text, $words = 200, $parent, $readMore = true){
        $text = nl2br($text);
        $exploded = explode(" ", $text);
        if(count($exploded) > $words){
            $html = implode(" ", array_splice($exploded, 0, $words)) . '...';
            if($readMore){
                $html .= '<br><div class="is--hidden commerceProductReview__readMoreContent">'. $text .'</div>
                <button class="button button--submitLink commerceProductReview__readMore" data-parent="'. $parent .'">Read More</button>';
            }
        } else {
            $html = $text;
        }
        return $html;
    }

    public function setError($message) {
        if (strlen($message) > 0) {
            Craft::$app->getSession()->setError(Craft::t('keiser-commerce-helpers', $message));
        }
    }

    public function getAllStateAbbreviations(){
        $states = \craft\commerce\Plugin::getInstance()->getStates()->getAllStates();
        $statesAbbr = [];
        foreach($states as $state){
            $statesAbbr[$state->id] = $state->abbreviation;
        }
        return $statesAbbr;
    }

    public function getAllCountryAbbreviations(){
        $countries = \craft\commerce\Plugin::getInstance()->getCountries()->getAllCountries();
        $countriesAbbr = [];
        foreach($countries as $country){
            $countriesAbbr[$country->id] = $country->iso;
        }
        return $countriesAbbr;
    }

    public function saveProductReviewToNetworkedWebsites(\barrelstrength\sproutforms\elements\Entry $sproutFormsEntry){
        $entryContent = $sproutFormsEntry->getAttributes();
        if($entryContent['moderationStatus']->value == 'published' && $entryContent['originCountry'] == getenv('HOME_COUNTRY')){
            $networkedWebsites = getenv('NETWORKED_WEBSITES');
            if($networkedWebsites){
                $networkedWebsites = explode(',', $networkedWebsites);
                foreach($networkedWebsites as $website){
                    $recommend = '';
                    foreach($entryContent['recommend'] as $option){
                        $recommend = $option->value;
                    }
                    $newsletterSignup = '';
                    foreach($entryContent['newsletterSignup'] as $option){
                        $newsletterSignup = $option->value;
                    }
                    $verifiedPurchase = 'false';
                    if($entryContent['isVerifiedPurchase']){
                        $verifiedPurchase = 'true';
                    }
                    $moderatorComments = '';
                    if($entryContent['moderatorComments']){
                        $moderatorComments = $entryContent['moderatorComments']->getRawContent();
                    }
                    $params = [
                        'handle' => 'productReview',
                        'fields[productSlug]' => $entryContent['productSlug'],
                        'fields[rating]' => $entryContent['rating'],
                        'fields[reviewHeadline]' => $entryContent['reviewHeadline'],
                        'fields[comments]' => $entryContent['comments'],
                        'fields[recommend]' => '',
                        'fields[recommend][]' => $recommend,
                        'fields[nickname]' => $entryContent['nickname'],
                        'fields[email]' => $entryContent['email'],
                        'fields[reviewImage]' => '',
                        'fields[city]' => $entryContent['city'],
                        'fields[state]' => $entryContent['state'],
                        'fields[age]' => $entryContent['age']->value,
                        'fields[gender]' => $entryContent['gender']->value,
                        'fields[termsConditions]' => '',
                        'fields[termsConditions][]' => 'agree',
                        'fields[newsletterSignup]' => '',
                        'fields[newsletterSignup][]' => $newsletterSignup,
                        'fields[moderatorComments]' => $moderatorComments,
                        'fields[isVerifiedPurchase]' => $verifiedPurchase,
                        'fields[moderationStatus]' => $entryContent['moderationStatus']->value,
                        'fields[originCountry]' => $entryContent['originCountry'],
                        'fields[originEntryId]' => $sproutFormsEntry->id,
                        'statusId' => '1',
                        'action' => 'keiser-contact-helpers/keiser-contact-helpers/save-product-review',
                        'title' => $entryContent['title']
                    ];
                    /**
                     * @var $entryContent['reviewImage'] \craft\elements\db\AssetQuery
                     */
                    if($entryContent['reviewImage']){
                        /**
                         * @var $reviewImage \craft\elements\Asset
                         */
                        $reviewImage = $entryContent['reviewImage']->one();
                        if($reviewImage){
                            $tempFile = $reviewImage->getCopyOfFile();
                            $params['fields[reviewImage]'] = curl_file_create($tempFile, $reviewImage->getMimeType(), $reviewImage->filename);
                        }
                    }
                    $ch = curl_init(getenv($website . '_WEBSITE_URL'));
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($ch);
                    if(curl_error($ch)){
                        \keiser\commercehelpers\Plugin::getInstance()::log(curl_error($ch));
                    }
                    curl_close($ch);
                }
            }
        }
    }

    public function optimizeImage($fileName){
        if($fileName){
            App::maxPowerCaptain();
            $originalFileSize = @filesize($fileName);
            $uploadResults = \Cloudinary\Uploader::upload($fileName, [
                'quality' => 'auto'
            ]);
            if(is_array($uploadResults) && isset($uploadResults['secure_url'])){
                $fh = fopen($fileName, 'w');
                $options = [
                    CURLOPT_FILE    => $fh,
                    CURLOPT_TIMEOUT =>  120,
                    CURLOPT_URL     => $uploadResults['secure_url'],
                ];

                $ch = curl_init();
                curl_setopt_array($ch, $options);
                curl_exec($ch);
                curl_close($ch);
                fclose($fh);
            }
            clearstatcache(true, $fileName);
            // Log the results of the image optimization
            $optimizedFileSize = @filesize($fileName);
            Craft::info(
                'Original: ' .$this->humanFileSize($originalFileSize, 1)
                . ' Optimized: ' . $this->humanFileSize($optimizedFileSize)
                . ' Savings: ' .number_format(abs(100 - (($optimizedFileSize * 100) / $originalFileSize)), 1) .'%',
                __METHOD__
            );
        }
    }

    public function optimizeCloudImage($fileUrl, $fileName){
        if($fileName && $fileUrl){
            $optimized = false;
            Craft::info('From Optimize Cloud Image');
            Craft::info($fileName);
            $outputFileName = getenv('CLOUDINARY_TEMP_FILE_PATH') . '/' . $fileName;
            try {
                $uploadResults = \Cloudinary\Uploader::upload($fileUrl, [
                    'quality' => 'auto'
                ]);
                if(is_array($uploadResults) && isset($uploadResults['secure_url'])){
                    $fh = fopen($outputFileName, 'w');
                    $options = [
                        CURLOPT_FILE    => $fh,
                        CURLOPT_TIMEOUT =>  120,
                        CURLOPT_URL     => $uploadResults['secure_url'],
                    ];

                    $ch = curl_init();
                    curl_setopt_array($ch, $options);
                    curl_exec($ch);
                    curl_close($ch);
                    $optimizedFileSize = @filesize($outputFileName);
                    Craft::info('Optimised Size: ' . $this->humanFileSize($optimizedFileSize));
                    fclose($fh);
                    $optimized = true;
                }
            } catch(\Exception $e){
                Craft::error($e->getMessage(), __METHOD__);
            }
            return $optimized;
        }
    }

    private function humanFileSize($bytes, $decimals = 1){
        $oldSize = Craft::$app->formatter->sizeFormatBase;
        Craft::$app->formatter->sizeFormatBase = 1000;
        $result = Craft::$app->formatter->asShortSize($bytes, $decimals);
        Craft::$app->formatter->sizeFormatBase = $oldSize;
        return $result;
    }

    public function getAbandonedCarts($lookback = null){
        $edge = new \DateTime();
        $interval = new \DateInterval('PT1H');
        $interval->invert = 1;
        $edge->add($interval);
        $orderQuery = (new craft\db\Query())
            ->select([
                'orders.id as id',
                'LEFT(orders.number,7) as shortNumber',
                'orders.dateCreated as dateCreated',
                'orders.dateUpdated as dateUpdated',
                'orders.email as email',
                'addresses.firstName as firstName',
                'addresses.lastName as lastName'
            ])
            ->from(['orders' => '{{%commerce_orders}}'])
            ->where(['not', ['orders.isCompleted' => 1]])
            ->leftJoin(['addresses' => '{{%commerce_addresses}}'], 'addresses.id = orders.billingAddressId')
            ->andWhere('[[orders.dateUpdated]] <= :edge', ['edge' => $edge->format('Y-m-d H:i:s')]);
        if($lookback){
            $lookbackDateTimeObj = new \DateTime();
            $lookbackInterval = new \DateInterval($lookback);
            $lookbackInterval->invert = 1;
            $lookbackDateTimeObj->add($lookbackInterval);
            $orderQuery->andWhere('[[orders.dateUpdated]] >= :lookback', ['lookback' => $lookbackDateTimeObj->format('Y-m-d H:i:s')]);
        }
        $orderQuery->orderBy('orders.dateUpdated desc');
        $orders = $orderQuery->all();

        $existingCustomers = $this->getExistingCustomersEmailList('P60D');

        foreach($orders as $key => $order){
            if($order['email'] && in_array($order['email'], $existingCustomers)){
                unset($orders[$key]);
            }
        }

        $results = [];
        /**
         * @var $order \craft\commerce\elements\Order
         */
        foreach($orders as $order){
            $email = 'guest';
            if($order['email']){
                $email = $order['email'];
            }
            $skuList = [];
            $total = 0;
            $items = (new craft\db\Query())
                ->select(['lineitems.total AS total', 'product.sku AS sku'])
                ->where(['lineitems.orderId' => $order['id']])
                ->from(['lineitems' => '{{%commerce_lineitems}}'])
                ->leftJoin(['product' => '{{%commerce_purchasables}}'], 'product.id = lineitems.purchasableId')
                ->all();
            foreach($items as $item){
                $skuList[] = $item['sku'];
                $total += (float) $item['total'];
            }
            $results[] = [
                'shortNumber' => $order['shortNumber'],
                'dateCreated' => $order['dateCreated'],
                'dateUpdated' => $order['dateUpdated'],
                'email' => $email,
                'firstName' => $order['firstName'],
                'lastName' => $order['lastName'],
                'sku' => implode('|', $skuList),
                'total' => $total
            ];
        }
        return $results;
    }

    public function notifyCustomerAboutReviewPublished(\barrelstrength\sproutforms\elements\Entry $sproutFormsEntry){
        $entryContent = $sproutFormsEntry->getAttributes();
        if($entryContent['moderationStatus']->value == 'published' && $entryContent['originCountry'] == getenv('HOME_COUNTRY')){
            $view = Craft::$app->getView();
            $oldTemplateMode = $view->getTemplateMode();
            $view->setTemplateMode($view::TEMPLATE_MODE_SITE);
            $product = Product::find()->slug($entryContent['productSlug'])->one();
            $renderVariables = [
                'entry' => $sproutFormsEntry,
                'productName' => $product->title,
                'productReviewsUrl' => $product->getUrl() . '/reviews'
            ];
            $newEmail = new Message();
            if (\craft\commerce\Plugin::getInstance()->getSettings()->emailSenderAddress) {
                $newEmail->setFrom(\craft\commerce\Plugin::getInstance()->getSettings()->emailSenderAddress);
            }
            $originalLanguage = Craft::$app->language;
            Craft::$app->language = $originalLanguage;
            $newEmail->setTo($entryContent['email']);
            $newEmail->setBcc([
                'miked@keiser.com'
            ]);
            $newEmail->setSubject($view->renderString('Your review for Keiser {{productName}} has been published', $renderVariables));
            try {
                $body = $view->renderTemplate('emails/review_published', $renderVariables);
                $newEmail->setHtmlBody($body);
            } catch (\Exception $e) {
                $error = Craft::t('commerce', 'Email template parse error for review published notification email. Customer Email: “{email}”. Message: “{message}”', [
                    'email' => $entryContent['email'],
                    'message' => $e->getMessage()
                ]);
                Craft::error($error, __METHOD__);
                Craft::$app->language = $originalLanguage;
                $view->setTemplateMode($oldTemplateMode);
                return false;
            }
            try {
                if (!Craft::$app->getMailer()->send($newEmail)) {
                    $error = Craft::t('commerce', 'Review published notification email could not be sent for email “{email}”.', [
                        'email' => $entryContent['email']
                    ]);
                    Craft::error($error, __METHOD__);
                    Craft::$app->language = $originalLanguage;
                    $view->setTemplateMode($oldTemplateMode);
                    return false;
                }
            } catch (\Exception $e) {
                $error = Craft::t('commerce', 'Review published notification email could not be sent for email “{email}”.' . 'Error: {error}', [
                    'error' => $e->getMessage(),
                    'email' => $entryContent['email']
                ]);
                Craft::error($error, __METHOD__);
                Craft::$app->language = $originalLanguage;
                $view->setTemplateMode($oldTemplateMode);
                return false;
            }
            Craft::$app->language = $originalLanguage;
            $view->setTemplateMode($oldTemplateMode);
        }
    }

    public function getSuggestedAccesoriesForCart(){
        $cart = \craft\commerce\Plugin::getInstance()->getCarts()->getCart();
        $accessories = [];
        $suggestWhiteGloveDelivery = false;
        $whiteGloveDeliveryFees = 0;
        foreach($cart->getLineItems() as $lineItem){
            $queryModel = Variant::find();
            $queryModel->sku = $lineItem->sku;
            $variant = $queryModel->one();
            $product = $variant->getProduct();
            if($product && $product->getType()->handle == 'equipment'){
                $queryModel = Entry::find();
                $queryModel->section = 'products';
                $queryModel->relatedTo([
                    'targetElement' => $product,
                    'field' => 'sellableEquipment'
                ]);
                $entry = $queryModel->one();
                if($entry){
                    $optionalAccessories = $entry->partsAndAccessoriesOptional->all();
                    if(!empty($optionalAccessories)){
                        foreach($optionalAccessories as $accessory){
                            $sellableProduct = $accessory->sellableAccessory->one();
                            if($sellableProduct && $accessory->displayAsSuggestedAccessory){
                                $accessories[] = $sellableProduct;
                            }
                        }
                    }

                }
                if($product->enableWhiteGloveDelivery &&
                    \keiser\contacthelpers\Plugin::getInstance()->service->whiteGloveDeliveryAvailableForRegion()){
                    $lineItemOptions = $lineItem->getOptions();
                    if(!isset($lineItemOptions['whiteGloveDelivery']) || !$lineItemOptions['whiteGloveDelivery']){
                        $suggestWhiteGloveDelivery = true;
                    } else {
                        $suggestWhiteGloveDelivery = false;
                    }
                    $whiteGloveDeliveryFees += ($product->whiteGloveDeliveryFee * $lineItem->qty);
                }
            }
        }
        $accessorySkuList = [];
        //Make the accessories list unique. Remove M Series Assembly & Maintenance Kit if WGA in cart
        foreach($accessories as $index => $accessory) {
            if(in_array($accessory->defaultSku, $accessorySkuList) || (
                $accessory->defaultSku == '550887' && $whiteGloveDeliveryFees > 0 && !$suggestWhiteGloveDelivery
                )){
                unset($accessories[$index]);
            } else {
                $accessorySkuList[] = $accessory->defaultSku;
            }
        }
        $alreadyAddedAccessories = [];
        if(!empty($accessories)){
            foreach($cart->getLineItems() as $lineItem){
                if(in_array($lineItem->sku, $accessorySkuList)){
                    $alreadyAddedAccessories[] = $lineItem->sku;
                }
            }
        }
        if(!empty($alreadyAddedAccessories)){
            foreach($accessories as $index => $accessory){
                if(in_array($accessory->defaultSku, $alreadyAddedAccessories)){
                    unset($accessories[$index]);
                }
            }
        }
        return $accessories;
    }

    public function getProductTitleBySKU($sku){
        $queryModel = Variant::find();
        $queryModel->sku = $sku;
        $variant = $queryModel->one();
        if($variant){
            return $variant->getProduct()->title;
        }
        return '';
    }

    public function getStoryTimeInSeconds($storyTime){
        $components = explode(':', $storyTime);
        return (int) $components[0] * 60 + (int) $components [1];
    }

    public function getOrdersWithPromoDetails(){
        $orders = (new craft\db\Query())
            ->select([
                'orders.id as id',
                'LEFT(orders.number,7) as shortNumber',
                'DATE_FORMAT(orders.dateOrdered, "%Y-%m-%d") as orderDate',
                'orders.email as email',
                'addresses.phone as phone',
                'orders.totalPrice as totalPrice',
                'addresses.firstName as firstName',
                'addresses.lastName as lastName',
                'addresses.city as city',
                'states.abbreviation as state',
                'addresses.zipCode as zip',
                'countries.iso as country',
            ])
            ->where(['orders.isCompleted' => 1])
            ->from(['orders' => '{{%commerce_orders}}'])
            ->leftJoin(['addresses' => '{{%commerce_addresses}}'], 'addresses.id = orders.shippingAddressId')
            ->leftJoin(['countries' => '{{%commerce_countries}}'], 'addresses.countryId = countries.id')
            ->leftJoin(['states' => '{{%commerce_states}}'], 'addresses.stateId = states.id')
            ->orderBy('orders.dateUpdated desc')
            ->all();
        $results = [];
        $allAvailedPromos = [];
        foreach($orders as $order){
            $email = 'guest';
            if($order['email']){
                $email = $order['email'];
            }
            $skuList = [];
            $titleList = [];
            $promoList = [];
            $tShirtList = [];
            $qtyList = [];
            $priceList = [];
            $items = (new craft\db\Query())
                ->select([
                    'lineitems.total AS total',
                    'lineitems.options AS options',
                    'lineitems.qty AS qty',
                    'lineitems.price AS price',
                    'lineitems.snapshot AS snapshot',
                    'product.sku AS sku',
                    ])
                ->where(['lineitems.orderId' => $order['id']])
                ->from(['lineitems' => '{{%commerce_lineitems}}'])
                ->leftJoin(['product' => '{{%commerce_purchasables}}'], 'product.id = lineitems.purchasableId')
                ->all();
            foreach($items as $item){
                $skuList[] = $item['sku'];
                $qtyList[] = $item['qty'];
                $priceList[] = $item['price'];
                $options = json_decode($item['options'], true);
                if(!empty($options)){
                    if(isset($options['promoItem'])) {
                        $promoList[] = $options['promoItem'];
                        $allAvailedPromos[] = $options['promoItem'];
                    }
                    if(isset($options['tShirtSize'])){
                        $tShirtList[] = $options['tShirtSize'];
                    }
                }
                $snapshot = json_decode($item['snapshot'], true);
                if(isset($snapshot['title'])){
                    $titleList[] = $snapshot['title'];
                }
            }
            $phoneNormalised = preg_replace('/\D/', '', $order['phone']);
            if(strlen($phoneNormalised) == 10){
                $order['phone'] = '1 ' . $order['phone'];
            }
            $results[] = [
                'shortNumber' => $order['shortNumber'],
                'orderDate' => $order['orderDate'],
                'sku' => implode('|', $skuList),
                'itemName' => implode('|', $titleList),
                'qty' => implode('|', $qtyList),
                'price' => implode('|', $priceList),
                'total' => $order['totalPrice'],
                'promoItem' => $promoList,
                'tShirtSize' => implode('|', $tShirtList),
                'firstName' => $order['firstName'],
                'lastName' => $order['lastName'],
                'email' => $email,
                'phone' => $order['phone'],
                'city' => $order['city'],
                'state' => $order['state'],
                'zip' => $order['zip'],
                'country' => $order['country']
            ];
        }
        $promoSKUMapping = [];
        if(!empty($allAvailedPromos)){
            $allAvailedPromos = array_unique($allAvailedPromos);
            foreach($allAvailedPromos as $sku){
                $queryModel = Variant::find();
                $queryModel->sku = $sku;
                $variant = $queryModel->one();
                if($variant){
                    $promoSKUMapping[$sku] = $variant->getProduct()->title;
                }
            }
        }
        foreach($results as $key => $result){
            if(!empty($result['promoItem'])){
                $promoItems = [];
                foreach($result['promoItem'] as $sku){
                    if(isset($promoSKUMapping[$sku])){
                        $promoItems[] = "{$promoSKUMapping[$sku]} ({$sku})";
                    }
                }
                $results[$key]['promoItem'] = implode('|', $promoItems);
            } else {
                $results[$key]['promoItem'] = '';
            }
        }
        return $results;
    }

    public function getFeaturedCaseStudiesForModal($allCaseStudySlugs, $currentCaseStudySlug){
        $allCaseStudySlugs = array_diff($allCaseStudySlugs, [$currentCaseStudySlug]);
        $randomKeys = array_rand($allCaseStudySlugs, 2);
        $featuredCaseStudySlugs = [];
        if(count($allCaseStudySlugs) >= 2){
            foreach($randomKeys as $key){
                $featuredCaseStudySlugs[] = $allCaseStudySlugs[$key];
            }
        }
        return $featuredCaseStudySlugs;
    }

    public function addWhiteGloveDeliveryToCart($lineItemId = null){
        $cart = \craft\commerce\Plugin::getInstance()->getCarts()->cart;
        $cartUpdated = false;
        $lineItems = $cart->getLineItems();
        foreach($lineItems as $lineItem){
            if($lineItemId && $lineItem->id != $lineItemId) {
                continue;
            }
            $queryModel = Variant::find();
            $queryModel->sku = $lineItem->sku;
            $variant = $queryModel->one();
            $product = $variant->getProduct();
            if( $product && $product->getType()->handle == 'equipment' &&
                $product->enableWhiteGloveDelivery
            ){
                $options = $lineItem->getOptions();
                if(!isset($options['whiteGloveDelivery']) || !$options['whiteGloveDelivery']){
                    $options['whiteGloveDelivery'] = true;
                }
                $lineItem->setOptions($options);
                $cart->addLineItem($lineItem);
                $cartUpdated = true;
            }
        }
        if ( $cartUpdated && !$cart->validate() ||
            !Craft::$app->getElements()->saveElement($cart, false)) {
            $error = Craft::t('commerce', 'Unable to update cart.');
            return [
                'error' => $error,
                'errors' => $cart->getErrors(),
                'success' => $cart->hasErrors(),
            ];
        }
        return [
            'success' => !$cart->hasErrors()
        ];
    }

    public function removeWhiteGloveDeliveryFromCart($lineItemId = null){
        $cart = \craft\commerce\Plugin::getInstance()->getCarts()->cart;
        $lineItems = $cart->getLineItems();
        foreach($lineItems as $lineItem){
            if($lineItemId && $lineItem->id != $lineItemId) {
                continue;
            }
            $options = $lineItem->getOptions();
            if(isset($options['whiteGloveDelivery'])){
                unset($options['whiteGloveDelivery']);
            }
            $lineItem->setOptions($options);
            $cart->addLineItem($lineItem);
        }
        if (!$cart->validate() || !Craft::$app->getElements()->saveElement($cart, false)) {
            $error = Craft::t('commerce', 'Unable to update cart.');
            return [
                'error' => $error,
                'errors' => $cart->getErrors(),
                'success' => $cart->hasErrors(),
            ];
        }
        return [
            'success' => !$cart->hasErrors()
        ];
    }

    /**
     * @param $order Order
     */
    public function sendOrderShippedEmail($order){
        if(isset($order->shipmentTrackingNumbers) && !empty($order->shipmentTrackingNumbers)){
            foreach($order->shipmentTrackingNumbers as $shipmentTrackingNumber){
                if($shipmentTrackingNumber->shipmentTrackingNumber){
                    $newRecord = KeiserCommerceShippingEmailLog::find()
                        ->where([
                            'orderId' => $order->getId(),
                            'shipmentTrackingNumber' => $shipmentTrackingNumber->shipmentTrackingNumber
                        ])
                        ->one();
                    if(!$newRecord){
                        /**
                         * @var $product Product
                         *
                         * The bundle offer for a line item can change subsequently after an order was placed
                         * (Eg: during promotions). We preserve the bundle offer, at the time of placing the order,
                         * in the line item 'options'. Hence, we would like to fetch that preserved bundle offer and
                         * display the same in the order shipment notification
                         */
                        $shippedProducts = $shipmentTrackingNumber->products->anyStatus()->all();
                        foreach($shippedProducts as $key => $product){
                            foreach($order->getLineItems() as $lineItem){
                                if( ($lineItem->getSku() == $product->getDefaultVariant()->getSku())
                                    && isset($lineItem->getOptions()['productSellersNotes'])
                                    && $lineItem->getOptions()['productSellersNotes'] ){
                                    $shippedProducts[$key]->productSellersNotes
                                        = $lineItem->getOptions()['productSellersNotes'];
                                }
                            }
                        }
                        $renderVariables = [
                            'order' => $order,
                            'shippedItems' => $shippedProducts,
                            'shipmentTrackingNumber' => $shipmentTrackingNumber->shipmentTrackingNumber,
                            'shippingDestination' => $shipmentTrackingNumber->shippingDestination,
                            'carrier' => $shipmentTrackingNumber->carrier
                        ];
                        if($this->sendOrderEmail(
                            'Order Shipped',
                            'Items from your Keiser Order (#{{order.number[:7]|upper}}) have been shipped',
                            'emails/order_shipped',
                            $order,
                            $renderVariables)){
                            $newRecord = new KeiserCommerceShippingEmailLog();
                            $newRecord->orderId = $order->getId();
                            $newRecord->shipmentTrackingNumber = $shipmentTrackingNumber->shipmentTrackingNumber;
                            $newRecord->save(false);
                        }
                    }
                }
            }
        }
    }

    public function checkIfAdminHasPushedOrderToWinmagi($order){
        if(isset($order->orderPushedToWinmagi) && $order->orderPushedToWinmagi){
            $newRecord = KeiserCommerceWinmagiPushLog::find()
                ->where([
                    'orderId' => $order->getId(),
                ])
                ->one();
            if(!$newRecord){
                $newRecord = new KeiserCommerceWinmagiPushLog();
                $newRecord->orderId = $order->getId();
                $newRecord->username = Craft::$app->user->getIsGuest() ? 'API' : Craft::$app->user->getIdentity()->name;
                $newRecord->save(false);
            }
        }
    }

    /**
     * @param $order Order
     */
    public function sendOrderConfirmationEmail($order){
        $renderVariables = [
            'order' => $order
        ];
        $subject = 'Thank you for your Keiser order (#{{order.number[:7]|upper}})';
        $template = 'emails/order_received';
        if(in_array($order->getOrderStatus()->handle, ['manualReviewRequired', 'notesAddressReviewRequired'])){
            $subject = '[Manual Review Required] Thank you for your Keiser order (#{{order.number[:7]|upper}})';
            $template = 'emails/order_manual_review';
        }
        $this->sendOrderEmail('Order Confirmation', $subject, $template, $order, $renderVariables);
    }

    private function sendOrderEmail($operationName, $subject, $template, $order, $renderVariables){
        $view = Craft::$app->getView();
        $oldTemplateMode = $view->getTemplateMode();
        $view->setTemplateMode($view::TEMPLATE_MODE_SITE);
        $newEmail = new Message();
        $originalLanguage = Craft::$app->language;
        if (\craft\commerce\Plugin::getInstance()->getSettings()->emailSenderAddress) {
            $newEmail->setFrom(\craft\commerce\Plugin::getInstance()->getSettings()->emailSenderAddress);
        }
        $orderLanguage = $order->orderLanguage ?: $originalLanguage;
        Craft::$app->language = $orderLanguage;
        if ($order->getCustomer()) {
            $newEmail->setTo($order->getEmail());
        }
        $newEmail->setBcc([
            'miked@keiser.com'
        ]);
        $newEmail->setSubject($view->renderString($subject, $renderVariables));
        try {
            $body = $view->renderTemplate($template, $renderVariables);
            $newEmail->setHtmlBody($body);
        } catch (\Exception $e) {
            $error = Craft::t('commerce', 'Email template parse error for {operationName} email. Order: “{order}”. Template error: “{message}”', [
                'operationName' => $operationName,
                'order' => $order->getShortNumber(),
                'message' => $e->getMessage()
            ]);
            Craft::error($error, __METHOD__);
            Craft::$app->language = $originalLanguage;
            $view->setTemplateMode($oldTemplateMode);
            return false;
        }
        try {
            if (!Craft::$app->getMailer()->send($newEmail)) {
                $error = Craft::t('commerce', '{operationName} email could not be sent for order “{order}”.', [
                    'operationName' => $operationName,
                    'order' => $order->getShortNumber()
                ]);
                Craft::error($error, __METHOD__);
                Craft::$app->language = $originalLanguage;
                $view->setTemplateMode($oldTemplateMode);
                return false;
            }
        } catch (\Exception $e) {
            $error = Craft::t('commerce', '{operationName} email could not be sent for order “{order}”. Error: {error}', [
                'operationName'=> $operationName,
                'error' => $e->getMessage(),
                'order' => $order->getShortNumber()
            ]);
            Craft::error($error, __METHOD__);
            Craft::$app->language = $originalLanguage;
            $view->setTemplateMode($oldTemplateMode);
            return false;
        }
        Craft::$app->language = $originalLanguage;
        $view->setTemplateMode($oldTemplateMode);
        return true;
    }

    public function getWebsitePageVariant($entry, $totalVariants, $defaultVariant = 0){
        $variant = $this->checkForVariantOverride();
        if($variant === null){
            $variant = $defaultVariant;
            if(isset($entry->contentBlockOptimizeExperimentID) && $entry->contentBlockOptimizeExperimentID){
                $cookieName = "keiser_experiments_{$entry->contentBlockOptimizeExperimentID}";
                $cookieCollection = Craft::$app->getRequest()->getCookies();
                if($cookieCollection->has($cookieName)){
                    $variant = (int)$cookieCollection[$cookieName]->value;
                } else {
                    $variant = rand(0, ($totalVariants - 1));
                    $cookie = new Cookie([
                        'name' => $cookieName,
                        'value' => $variant,
                        'expire' => time() + 60 * 60 * 24 * 60, //60 days
                        'secure' => true,
                        'httpOnly' => true,
                    ]);
                    Craft::$app->response->getCookies()->add($cookie);
                }
            }
        }
        return $variant;
    }

    public function getWebsiteTestPageVariant($defaultVariant = 0){
        $variant = $this->checkForVariantOverride();
        if($variant !== null){
            return $variant;
        } else {
            return $defaultVariant;
        }
    }

    public function getDemoVanPageVariant(){
        $variant = $this->checkForVariantOverride();
        if($variant === null){
            $cookieCollection = Craft::$app->getRequest()->getCookies();
            $variant = 2;
            if(getenv('OPTIMIZE_EXPERIMENT_DEMOVAN_ID')){
                $cookieName = 'keiser_experiments_demovan';
                if($cookieCollection->has($cookieName)){
                    $variant = (int)$cookieCollection[$cookieName]->value;
                } else {
                    $variant = rand(0,2);
                    $cookie = new Cookie([
                        'name' => $cookieName,
                        'value' => $variant,
                        'expire' => time() + 60 * 60 * 24 * 60, //60 days
                        'secure' => true,
                        'httpOnly' => true,
                    ]);
                    Craft::$app->response->getCookies()->add($cookie);
                }
            }
        }
        return $variant;
    }

    public function getLandingPageVariant(Entry $entry){
        $variant = 0;
        if($entry->contentBlockOptimizeExperimentID){
            $variant = $this->checkForVariantOverride();
            if($variant == null){
                $eligibleVariants = [0];
                if($entry->contentBlockEnableVariant2 && !empty($entry->contentBlocksVariant2)){
                    $eligibleVariants[] = 1;
                }
                if($entry->contentBlockEnableVariant3 && !empty($entry->contentBlocksVariant3)){
                    $eligibleVariants[] = 2;
                }
                $cookieCollection = Craft::$app->getRequest()->getCookies();
                $cookieName = "keiser_experiments_{$entry->contentBlockOptimizeExperimentID}";
                if($cookieCollection->has($cookieName)) {
                    $variant = (int)$cookieCollection[$cookieName]->value;
                    if(in_array($variant, $eligibleVariants)){
                        return $variant;
                    }
                }
                $variant = array_rand($eligibleVariants);
                $cookie = new Cookie([
                    'name' => $cookieName,
                    'value' => $variant,
                    'expire' => time() + 60 * 60 * 24 * 60, //60 days
                    'secure' => true,
                    'httpOnly' => true,
                ]);
                Craft::$app->response->getCookies()->add($cookie);
            }
        }
        return $variant;
    }

    private function checkForVariantOverride(){
        $overrideVariant = Craft::$app->getRequest()->getParam('ssevariant');
        /* Need to check explicitly for null as otherwise $overrideVariant could also be '0' which is a valid variant
         number but would be considered a falsy value in the conditional */
        if($overrideVariant !== null){
           return $overrideVariant;
        }
        return null;
    }

    public function getSuggestedCountryIdForCheckout(){
        $visitorGeolocation = \keiser\contacthelpers\Plugin::getInstance()->service->getVisitorGeolocation();
        $countryAbbr = array_flip($this->getAllCountryAbbreviations());
        if(isset($countryAbbr[$visitorGeolocation['country']])){
            return $countryAbbr[$visitorGeolocation['country']];
        }
        return $countryAbbr[array_key_first($countryAbbr)];
    }

    public function getSuggestedStateIdForCheckout($suggestedCountryId){
        $states = \craft\commerce\Plugin::getInstance()->getStates()->getAllStatesAsList();
        if(isset($states[$suggestedCountryId])){
            $visitorGeolocation = \keiser\contacthelpers\Plugin::getInstance()->service->getVisitorGeolocation();
            $statesAbbr = array_flip($this->getAllStateAbbreviations());
            if(isset($statesAbbr[$visitorGeolocation['subdivision']])){
                return $statesAbbr[$visitorGeolocation['subdivision']];
            }
        }
        return '';
    }

    /**
     * @param $product Product
     */
    public function parseShippingLeadTime($featureText, $product){
        $shippingLeadTime = '5 - 7';
        if($product->shippingLeadTime){
            $shippingLeadTime = $product->shippingLeadTime;
        }
        $featureText = preg_replace('/\{\{shippingLeadTime\}\}/mi', $shippingLeadTime, $featureText);
        return $featureText;
    }

    public function getShippingLeadTimeForCart(){
        $cart = \craft\commerce\Plugin::getInstance()->getCarts()->getCart();
        return $this->calculateShippingLeadTime($cart);
    }

    private function calculateShippingLeadTime(Order $order){
        $shippingLeadTime = 0;
        foreach($order->getLineItems() as $lineItem){
            $queryModel = Variant::find();
            $queryModel->sku = $lineItem->sku;
            $variant = $queryModel->one();
            $product = $variant->getProduct();
            if($product && $product->shippingLeadTime){
                if($product->shippingLeadTime > $shippingLeadTime){
                    $shippingLeadTime = $product->shippingLeadTime;
                }
            }
        }
        if($this->isPreInstalledAccessoryInCart($order)){
            if($shippingLeadTime < 5){
                $shippingLeadTime = 5;
            }
        }
        if($shippingLeadTime === 0){
            $shippingLeadTime = 5;
        }
        return $shippingLeadTime;
    }

    public function isPreInstalledAccessoryInCart($cart = null){
        if(!$cart){
            $cart = \craft\commerce\Plugin::getInstance()->getCarts()->getCart();
        }
        $m3iInCart = false;
        $dumbbellHolderInCart = false;
        foreach($cart->getLineItems() as $lineItem) {
            if($lineItem->sku == '005506BBC'){
                $m3iInCart = true;
            }
            //TODO: Change this SKU to FALSESKU when factory is not able to ship M3i quicker in 2 days
            if($lineItem->sku == '550878B'){
                $dumbbellHolderInCart = true;
            }
        }
        if($m3iInCart && $dumbbellHolderInCart){
            return true;
        }
        return false;
    }

    /**
     * @param $order Order
     */
    public function getScheduledShipDate($order){
        $shippingLeadTime = $this->calculateShippingLeadTime($order);
        $fileName = strtolower(getenv('HOME_COUNTRY')) . '_holidays.csv';
        $allHolidays = file_get_contents(__DIR__ . '/../resources/' . $fileName);
        $allHolidays = explode("\n", $allHolidays);
        $i = $shippingLeadTime;
        $scheduledShipDate = new \DateTime('now', new \DateTimeZone(getenv('HOME_COUNTRY_TIMEZONE')));
        while($i > 0){
            $scheduledShipDate->add(new \DateInterval('P1D'));
            if(in_array($scheduledShipDate->format('Y-m-d'), $allHolidays)){
                continue;
            }
            $i--;
        }
        //Craft converts the date to Y-m-d 00:00 in LA time zone and then for storing makes it back to UTC which goes 1 day back
        $now = new \DateTime('now', new \DateTimeZone(getenv('HOME_COUNTRY_TIMEZONE')));
        $la8am = new \DateTime('08:00', new \DateTimeZone(getenv('HOME_COUNTRY_TIMEZONE')));
        if($now <= $la8am){
            $scheduledShipDate->add(new \DateInterval('P1D'));
        }
        return $scheduledShipDate->format('Y-m-d');
    }

    public function getCustomCSSClasses($block){
        if(isset($block->contentBlockCSSClasses) && $block->contentBlockCSSClasses){
            return explode(' ', $block->contentBlockCSSClasses);
        }
        return [];
    }

    public function createYoutubeEmbedLink($videoLink){
        preg_match('/youtube\.com\/watch\?v\=(.*)/', $videoLink, $linkParts);
        if(isset($linkParts[1]) && $linkParts[1]){
            return 'https://www.youtube.com/embed/' . $linkParts[1];
        }
        return '';
    }

    public function createVimeoEmbedLink($videoLink){
        preg_match('/vimeo\.com\/([0-9]*)/', $videoLink, $linkParts);
        if(isset($linkParts[1]) && $linkParts[1]){
            return 'https://player.vimeo.com/video/' . $linkParts[1];
        }
        return '';
    }

    public function getExistingCustomersEmailList($dateInterval){
        $lastNDaysDateTimeObj = new \DateTime();
        $lastNDaysInterval = new \DateInterval($dateInterval);
        $lastNDaysInterval->invert = 1;
        $lastNDaysDateTimeObj->add($lastNDaysInterval);
        $customersFromLastNDays = (new craft\db\Query())
            ->select([
                'orders.email as email'
            ])
            ->from(['orders' => '{{%commerce_orders}}'])
            ->where(['=', 'orders.isCompleted', 1])
            ->where(['not', ['orders.email' => null]])
            ->andWhere('[[orders.dateOrdered]] >= :last30Days', ['last30Days' => $lastNDaysDateTimeObj->format('Y-m-d H:i:s')])
            ->all();
        $existingCustomers = [];
        foreach($customersFromLastNDays as $customer){
            $existingCustomers[] = $customer['email'];
        }
        return $existingCustomers;
    }

    public function isPrintfectionItemInOrder(Order $order){
        foreach($order->getLineItems() as $lineItem){
            $queryModel = Variant::find();
            $queryModel->sku = $lineItem->sku;
            $variant = $queryModel->one();
            $product = $variant->getProduct();
            if($product && $product->getType()->handle == 'clothing'){
                return true;
            }
        }
        return false;
    }

    public function isKeiserFulfilledItemInOrder(Order $order){
        foreach($order->getLineItems() as $lineItem){
            $queryModel = Variant::find();
            $queryModel->sku = $lineItem->sku;
            $variant = $queryModel->one();
            $product = $variant->getProduct();
            if($product && $product->getType()->handle != 'clothing'){
                return true;
            }
        }
        return false;
    }

    public function isEquipmentInCart(Order $order){
        foreach($order->getLineItems() as $lineItem){
            $queryModel = Variant::find();
            $queryModel->sku = $lineItem->sku;
            $variant = $queryModel->one();
            $product = $variant->getProduct();
            if($product && $product->getType()->handle == 'equipment'){
                return true;
            }
        }
    }
}
