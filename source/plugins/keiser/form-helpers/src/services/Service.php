<?php

namespace keiser\formhelpers\services;

use Craft;
use barrelstrength\sproutforms\base\Captcha;
use barrelstrength\sproutforms\SproutForms;
use craft\base\Component;
use craft\fields\data\MultiOptionsFieldData;
use keiser\contacthelpers\fields\CampaignTrackingField;

class Service extends Component {

    private $content;
    private $excludeFields;

    public function getCaptchasHtml(){
        $html = '';
        $captchas = SproutForms::$app->forms->getAllEnabledCaptchas();
        foreach ($captchas as $captcha) {
            /**
             * @var Captcha $captcha
             */
            $html .= $captcha->getCaptchaHtml();
        }
        return $html;
    }

    public function sendToLiveAgent($content, $formFields, $departmentId){
        $this->content = $content;
        //Ensure that the customer entry exists, otherwise create it
        $v1 = 'https://keiser.ladesk.com/api';
        $apiKey = getenv('LIVEAGENT_API_KEY');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $v1 . "/customers/" . $this->content['email'] . "?&apikey=" . $apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curlResponse = curl_exec($ch);
        $found = json_decode($curlResponse);
        $this->logLiveAgentError($found);
        if(isset($found->response->statuscode) && $found->response->statuscode == '404' && isset($this->content['fullName'])){
            $curlPostData = [
                'apikey' => $apiKey,
                'email' =>  $this->content['email'],
                'name' => $this->content['fullName'],
                'note' => "This customer was auto-registered after submitting a support request on Keiser.com.",
                'send_registration_mail' => "N"
            ];
            curl_setopt($ch, CURLOPT_URL, $v1 . "/customers/");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPostData);
            $response = curl_exec($ch);
            $this->logLiveAgentError(json_decode($response));
            curl_close($ch);
        }

        $this->excludeFields = [
            'id',
            'formId',
            'formHandle',
            'elementId',
            'statusId',
            'formGroupId',
            'formName',
            'statusHandle',
            'tempId',
            'uid',
            'fieldLayoutId',
            'contentId',
            'enabled',
            'archived',
            'siteId',
            'enabledForSite',
            'title',
            'slug',
            'uri',
            'dateCreated',
            'dateUpdated',
            'hasDescendants',
            'ref',
            'status',
            'structureId',
            'totalDescendants',
            'url',
            'locale',
            'ipAddress',
            'userAgent',
            'title',
            'sendToLiveAgent',
            'sendToSugarCRM',
            'copyOfInvoice',
            'optOutOptions',
            'metaTitle',
            'metaDescription',
            'marketingOptIn',
            'userLocation',
            'geoLocation',
            'propagateAll',
            'referrer',
            'siteSettingsId',
            'canonicalId',
            'isNewForSite',
        ];

        $message = '';

        switch($departmentId){

            case 'supportTicket':
                $dept = "a0eb5607";
                $subject = "Keiser Support Request";
                $fullName = $this->content['fullName'];
                $message .= $this->useField('fullName') . " | " . $this->useField('phoneNumber') . " | " . $this->useField('email');
                if($this->inputProvided('serialNum')) $message .= "\nSerial №. " . $this->useField('serialNum');
                if($this->inputProvided('message')) $message .= "\n\n“" . $this->useField('message') . "”";
                break;

            case 'optOut':
                $dept = "933767e8";
                $subject = "Keiser Opt-Out / Data Request";
                $fullName = $this->content['fullName'];
                $message .= $this->useField('fullName') . " | " . $this->useField('phoneNumber') . " | " . $this->useField('email') . "\n\n";
                foreach($this->content['optOutOptions'] as $option){
                    if($option == 'optOutAll'){
                        $message .= "I would like to opt out completely, and have all data removed." . "\n";
                    } else if($option == 'requestResponse'){
                        $message .= "I would like a response regarding my personal data." . "\n";
                    }
                }
                break;

            case 'partsOrderForm':
                $dept = "7d4a29d9";
                $subject = "Keiser Parts Order";
                $fullName = $this->content['fullName'];
                $message .= $this->useField('fullName') . " | " . $this->useField('phoneNumber') . " | " . $this->useField('email') . "\n\n";
                if($this->inputProvided('company')) $message .= $this->useField('company');
                if($this->inputProvided('address')) $message .= "\n" . $this->useField('address');
                if($this->inputProvided('addressLine2')) $message .= "\n" . $this->useField('addressLine2');
                if($this->inputProvided('city')) $message .= "\n" . $this->useField('city') . ", ";
                if($this->inputProvided('state')) $message .= $this->useField('state') . " ";
                if($this->inputProvided('zipcode')) $message .= $this->useField('zipcode');
                if($this->inputProvided('country')) $message .= "\n" . $this->useField('country') . "\n";
                if($this->inputProvided('message')) $message .= "\n\n“" . $this->useField('message') . "”";
                break;

            case 'warrantyRegistration':
                $dept = "da316980";
                $subject = "\n\nKeiser.com Submission: Warranty Registration";
                if($this->inputProvided('invoiceNumber')) $message .=  "Invoice №. " . $this->useField('invoiceNumber') . "   ";
                if($this->inputProvided('serialNumber')) $message .=  "Serial №. " . $this->useField('serialNumber') . "   ";
                if($this->inputProvided('modelNumber')) $message .=  "Model №. " . $this->useField('modelNumber') . "\n";
                if($this->inputProvided('installDate')) $message .=  "This equipment was installed on " . $this->useField('installDate') . ".\n";
                $message .= "\n~ S O L D  T O  /  C U S T O M E R  I N F O R M A T I O N ~\n\n";
                $fullName = $this->content['fullName'];
                if($this->inputProvided('fullName')) $message .= $this->useField('fullName') . "\n";
                if($this->inputProvided('company')) $message .= $this->useField('company') . "\n";
                if($this->inputProvided('phoneNumber')) $message .= $this->useField('phoneNumber') . " | ";
                if($this->inputProvided('email')) $message .= $this->useField('email');
                if($this->inputProvided('address')) $message .= "\n\n" . $this->useField('address');
                if($this->inputProvided('addressLine2')) $message .= "\n" . $this->useField('addressLine2');
                if($this->inputProvided('city')) $message .= "\n" . $this->useField('city') . ", ";
                if($this->inputProvided('state')) $message .= $this->useField('state') . " ";
                if($this->inputProvided('zipcode')) $message .= $this->useField('zipcode');
                if($this->inputProvided('country')) $message .= "\n" . $this->useField('country') . "\n";
                if($this->inputProvided('message')) $message .= "\nAdditional entry/entries:\n“" . $this->useField('message') . "”\n";
                foreach ($this->content as $fieldHandle => $fieldValue)
                {
                    if('dealer' == substr($fieldHandle,0,6) && !empty($fieldValue)) $dealer = 1;
                }
                if(isset($dealer)){
                    $message .= "\n~ D I S T R I B U T O R  /  D E A L E R  I N F O R M A T I O N ~\n\n";
                    if($this->inputProvided('dealerContact')) $message .= 'Dealer Contact: ' . $this->useField('dealerContact') . "\n";
                    if($this->inputProvided('dealerOrDistributorName')) $message .= 'Distributed by ' .$this->useField('dealerOrDistributorName') . "\n";
                    if($this->inputProvided('dealerEmail')) $message .= $this->useField('dealerEmail') . " | ";
                    if($this->inputProvided('dealerPhoneNumber')) $message .= "Phone: " . $this->useField('dealerPhoneNumber') . " | ";
                    if($this->inputProvided('dealerFax')) $message .= "Fax: " . $this->useField('dealerFax') . "\n";
                    if($this->inputProvided('dealerAddress')) $message .= $this->useField('dealerAddress') . "\n";
                    if($this->inputProvided('dealerAddressLine2')) $message .= $this->useField('dealerAddressLine2') . "\n";
                    if($this->inputProvided('dealerCity')) $message .= $this->useField('dealerCity') . ", ";
                    if($this->inputProvided('dealerState')) $message .= $this->useField('dealerState') . "  ";
                    if($this->inputProvided('dealerCountry')) $message .= $this->useField('dealerCountry') . "  ";
                    if($this->inputProvided('dealerZipcode')) $message .= $this->useField('dealerZipcode');
                    $message .= "\n";
                }
                break;

            case 'warrantyPartsRequest':
                $dept = "7d4a29d9";
                $subject = "Keiser.com Submission: Parts Request";
                if($this->inputProvided('message')) $message .= "\n\n“" . $this->useField('message') . "”";
                $equipmentFields = [
                    'modelNumber',
                    'serialNumber',
                    'datePurchased',
                    'dateFailure',
                    'repairDate',
                    'problem',
                    'resolution'
                ];
                foreach ($this->content as $fieldHandle => $fieldValue)
                {
                    if((in_array($fieldHandle, $equipmentFields)) && !empty($fieldValue)) $equipment = 1;
                }
                if(isset($equipment) && $equipment == 1)
                {
                    $message .= "\n\n";
                    if($this->inputProvided('modelNumber')) $message .= "Model №. " . $this->useField('modelNumber');
                    if($this->inputProvided('serialNumber')) $message .= " | Serial №. " . $this->useField('serialNumber') . "\n";
                    if($this->inputProvided('datePurchased')) $message .= "Purchased on " . $this->useField('datePurchased');
                    if($this->inputProvided('dateFailure')) $message .= " | Failed on " . $this->useField('dateFailure');
                    if($this->inputProvided('repairDate')) $message .= " | Repaired on " . $this->useField('repairDate') . "\n";
                    if($this->inputProvided('problem')) $message .= "\nProblem: “" . $this->useField('problem') . "”\n";
                    if($this->inputProvided('resolution')) $message .= "\nResolution / comments: “" . $this->useField('resolution') . "”";
                }
                foreach ($this->content as $fieldHandle => $fieldValue)
                {
                    if('partNo' == substr($fieldHandle,0,6) && !empty($fieldValue)) $parts = 1;
                }
                if(isset($parts) && $parts == 1)
                {
                    $message .= "\n\n~ P A R T S   L I S T ~\n";
                    for($i = 1; $i <= 5; $i++){
                        $parts = array('partNo' . $i, 'description' . $i, 'qty' . $i);
                        foreach ($this->content as $fieldHandle => $fieldValue)
                        {
                            if(in_array($fieldHandle, $parts) && !empty($fieldValue)) $hasParts = 1;
                        }
                        if(isset($hasParts) && $hasParts == 1){
                            if($this->inputProvided('partNo' . $i)) $message .= "\nPart №. " . $i . ": " . $this->useField('partNo' . $i);
                            if($this->inputProvided('qty' . $i)) $message .= " (×" . $this->useField('qty' . $i) . ") ";
                            if($this->inputProvided('description' . $i)) $message .= "—“" . $this->useField('description' . $i) . "”";
                        }
                        $parts = 0;
                    }
                }
                $message .= "\n\n~ C U S T O M E R  I N F O R M A T I O N ~\n\n";
                $fullName = $this->useField('fullName');
                if($this->inputProvided('fullName')) $message .= $this->useField('fullName') . "\n";
                if($this->inputProvided('phoneNumber')) $message .= $this->useField('phoneNumber') . " | ";
                if($this->inputProvided('email')) $message .= $this->useField('email');
                if($this->inputProvided('fax')) $message .= " | Fax: " . $this->useField('fax');
                if($this->inputProvided('address')) $message .= "\n" . $this->useField('address');
                if($this->inputProvided('addressLine2')) $message .= "\n" . $this->useField('addressLine2');
                if($this->inputProvided('city')) $message .= "\n" . $this->useField('city') . ", ";
                if($this->inputProvided('state')) $message .= $this->useField('state') . " ";
                if($this->inputProvided('zipcode')) $message .= $this->useField('zipcode');
                if($this->inputProvided('country')) $message .= "\n" . $this->useField('country');
                $distributorFields = array('contact', 'customerNumber', 'company');
                foreach ($this->content as $fieldHandle => $fieldValue)
                {
                    if((in_array($fieldHandle, $distributorFields) || 'distributor' == substr($fieldHandle,0,11)) && !empty($fieldValue)) $distributor = 1;
                }
                if(isset($distributor) && $distributor == 1){
                    $message .= "\n\n~ D I S T R I B U T O R   I N F O R M A T I O N ~:\n\n";
                    if($this->inputProvided('contact')) $message .= $this->useField('contact');
                    if($this->inputProvided('customerNumber')) $message .= " (Customer №." . $this->useField('customerNumber') . ")";
                    if($this->inputProvided('distributorPhoneNumber')) $message .= " | Phone: " . $this->useField('distributorPhoneNumber') . " | ";
                    if($this->inputProvided('distributorFax')) $message .= "Fax: " . $this->useField('distributorFax') . "\n";
                    if($this->inputProvided('company')) $message .= "\n" . $this->useField('company');
                    if($this->inputProvided('distributorAddress')) $message .= "\n" . $this->useField('distributorAddress') . "\n";
                    if($this->inputProvided('distributorAddressLine2')) $message .= $this->useField('distributorAddressLine2') . "\n";
                    if($this->inputProvided('distributorCity')) $message .= $this->useField('distributorCity') . ", ";
                    if($this->inputProvided('distributorState')) $message .= $this->useField('distributorState') . "  ";
                    if($this->inputProvided('distributorCountry')) $message .= $this->useField('distributorCountry') . "  ";
                    if($this->inputProvided('distributorZipcodeOrPostalCode')) $message .= $this->useField('distributorZipcodeOrPostalCode');
                }
                if($this->inputProvided('shipTo')) $message .= "\n\n~ S H I P P I N G   I N F O R M A T I O N ~\n\n" . $this->useField('shipTo');
                if($this->inputProvided('comments')) $message .= "\n\nComments: “" . $this->useField('comments') . "”";
                break;

            case 'publicRelations':
                $dept = "933767e8";
                $subject = "Keiser Public Relations Enquiry";
                $fullName = $this->content['fullName'];
                $message .= $this->useField('fullName') . " | " . $this->useField('phoneNumber') . " | " . $this->useField('email');
                if($this->inputProvided('message')) $message .= "\n\n“" . $this->useField('message') . "”";
                break;

            case 'educationServices':
                $dept = "d56838b1";
                $subject = "Keiser Education Services Request";
                $fullName = $this->content['fullName'];
                $message .= $this->useField('fullName') . " | " . $this->useField('phoneNumber') . " | " . $this->useField('email');
                if($this->inputProvided('message')) $message .= "\n\n“" . $this->useField('message') . "”";
                break;

            case 'softwareIssue':
                $dept = "915249b6";
                $subject = "Keiser Software Issue";
                $fullName = $this->content['fullName'];
                $message .= $this->useField('fullName') . " | " . $this->useField('phoneNumber') . " | " . $this->useField('email');
                if($this->inputProvided('message')) $message .= "\n\n“" . $this->useField('message') . "”";
                break;

            case 'generalEnquiry':
                $dept = "0118e890";
                $subject = "Keiser General Enquiry";
                $fullName = $this->content['fullName'];
                $message .= $this->useField('fullName') . " | " . $this->useField('phoneNumber') . " | " . $this->useField('email');
                if($this->inputProvided('message')) $message .= "\n\n“" . $this->useField('message') . "”";
                break;

            case 'homeSales':
                $dept = "default";
                $subject = "Keiser Home Sales Enquiry";
                $fullName = $this->content['fullName'];
                $message .= $this->useField('fullName') . " | " . $this->useField('phoneNumber') . " | " . $this->useField('email');
                if($this->inputProvided('message')) $message .= "\n\n“" . $this->useField('message') . "”";
                break;

        }

        $userLocationField = \keiser\contacthelpers\Plugin::getExactAttributeNameForForm('userLocation', $this->content);
        if($userLocationField && $this->inputProvided($userLocationField)){
            $userLocation = explode(';', $this->content[$userLocationField]);
            $userLocationStr = implode(', ', $userLocation);
            $message .= "\n\n User Location: " . $userLocationStr;
            $countriesList = \keiser\contacthelpers\Plugin::getInstance()->service->getCountriesList();
            $countryISOMapping = [];
            foreach($countriesList as $country){
                $countryISOMapping[$country['name']] = $country['alpha2Code'];
            }
            if(isset($countryISOMapping[$userLocation[0]])){
                if(isset($userLocation[1])){
                    $representative = \keiser\contacthelpers\Plugin::getInstance()->service->findKeiserRep($countryISOMapping[$userLocation[0]], $userLocation[1]);
                } else {
                    $representative = \keiser\contacthelpers\Plugin::getInstance()->service->findKeiserRep($countryISOMapping[$userLocation[0]]);
                }
                if($representative){
                    $message .= "\n\n Representative Name: " . $representative->title;
                    $message .= "\n Representative Email: " . $representative->email;
                }
            }
        }

        $geolocationField = \keiser\contacthelpers\Plugin::getExactAttributeNameForForm('geoLocation', $this->content);
        if($this->inputProvided($geolocationField)){
            $geoLocation = explode(';', $this->content[$geolocationField]);
            $geoLocation = implode(', ', $geoLocation);
            $message .= "\n\n Geo Location: " . $geoLocation;
        }

        $otherFields = [];
        foreach ($this->content as $fieldHandle => $fieldValue){
            if(!in_array($fieldHandle, $this->excludeFields) && !empty($fieldValue)){
                $otherFields[$fieldHandle] = $fieldValue;
            }
        }
        if(count($otherFields) > 0){
            $message .= "\n\n~ Additional fields ~\n\n";
            foreach ($otherFields as $fieldHandle => $fieldValue){
                $message .= $formFields[$fieldHandle]->name . ": " . $fieldValue . "\n";
            }
        }
        $fields = [
            'message' => $message,
            'useridentifier' => $this->content['email'],
            'department' => $dept,
            'subject' => $subject,
            'recipient' => 'service@keiser.com',
            'recipient_name' => $fullName,
            'status' => 'N',
            'do_not_send_mail' => 'Y',
            'use_template' => 'N',
            'is_html_message' => 'N',
            'apikey' => $apiKey
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $v1 . "/conversations/");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curlResponse = curl_exec($ch);
        $this->logLiveAgentError(json_decode($curlResponse));
        if($departmentId == 'optOut'){
            $fields = array(
                'apikey' => $apiKey,
                'id' => 'ea64'
            );
            if(isset(json_decode($curlResponse)->response->conversationid)){
                $conversationID = json_decode($curlResponse)->response->conversationid;
                curl_setopt($ch, CURLOPT_URL, $v1 . "/conversations/" . $conversationID . "/tags");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                $response = curl_exec($ch);
                $this->logLiveAgentError(json_decode($response));
            }
        }
        curl_close($ch);
    }

    public function sendToSugarCRM($content, $form){
        $this->content = $content;
        $headers = [
            'Content-Type: application/json'
        ];
        $postParams = [
            'grant_type' => 'password',
            'client_id' => getenv('SUGARCRM_CLIENT_ID'),
            'client_secret' => getenv('SUGARCRM_CLIENT_SECRET'),
            'username' => getenv('SUGARCRM_USERNAME'),
            'password' => getenv('SUGARCRM_PASSWORD'),
            'platform' => getenv('SUGARCRM_PLATFORM_VALUE')
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, getenv('SUGARCRM_BASE_URL') . '/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postParams));
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        if(isset($result['access_token']) && $result['access_token']){
            $headers[] = "OAuth-Token: {$result['access_token']}";
            $userID = false;
            if($this->inputProvided('repEmail')){
                $userID = $this->getSugarUserByEmail($this->useField('repEmail'), $headers);
                if(!$userID){
                    $userID = $this->getSugarUserByEmail($this->useField('repVPEmail'), $headers);
                }
            }
            $name = explode(' ', $this->useField('fullName'), 2);
            $firstName = $name[0];
            $lastName = '*';
            if(isset($name[1])){
                $lastName = $name[1];
            }

            $interestedEquipments = '';
            $pageTitleField = \keiser\contacthelpers\Plugin::getExactAttributeNameForForm('pageTitle', $this->content);
            if($this->useField('interestedProducts')){
                $interestedEquipments = 'Interests: ' . implode(', ', $this->useField('interestedProducts'));
            } else if($pageTitleField && $this->useField($pageTitleField)){
                $interestedEquipments = 'Submitted From Page: ' . $this->useField($pageTitleField);
            }
            $institutionType = '';
            if($this->useField('institutionType')){
                $institutionType = implode(',', $this->useField('institutionType'));
            }
            if($interestedEquipments && $institutionType){
                $description = "Industry: {$institutionType} \n {$interestedEquipments} \n Message: {$this->useField('message')}";
            } else if($interestedEquipments) {
                $description = "{$interestedEquipments} \n Message: {$this->useField('message')}";
            } else {
                $description = "Message: {$this->useField('message')}";
            }
            $geolocationField = \keiser\contacthelpers\Plugin::getExactAttributeNameForForm('geoLocation', $this->content);
            $geoLocation = explode(';', $this->content[$geolocationField]);
            $altAddress = implode(', ', $geoLocation);
            $campaignTracking = [];
            $campaignTrackingField = \keiser\contacthelpers\Plugin::getExactAttributeNameForForm('campaignTracking', $this->content);
            if($campaignTrackingField && $this->useField($campaignTrackingField)){
                $campaignTracking = explode(';', $this->content[$campaignTrackingField]);
                Craft::$app->response->getCookies()->remove(CampaignTrackingField::$cookieName);
            }
            $emailOptin = false;
            if(isset($this->content['marketingOptIn']) && $this->content['marketingOptIn'] == 'yes'){
                $emailOptin = true;
            }
            $postalCode = '';
            if($this->useField('zip')){
                $postalCode = $this->useField('zip');
            } else if($this->useField('zipCode')){
                $postalCode = $this->useField('zipCode');
            }
            $countryName = '';
            if($this->useField('country')){
                $countryName = $this->useField('country');
            } else if($this->useField('countryName')){
                $countryName = $this->useField('countryName');
            }
            $postParams = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'title' => $this->useField('customerTitle'),
                'email1' => $this->useField('email'),
                'primary_address_city' => (isset($geoLocation[0]) ? $geoLocation[0] : ''),
                'primary_address_state' => (isset($geoLocation[1]) ? $geoLocation[1] : ''),
                'primary_address_postalcode' => $postalCode,
                'primary_address_country' => $countryName,
                'alt_address_street' => $altAddress,
                'account_description' => $institutionType,
                'product_title_c' => $interestedEquipments,
                'description' => $description,
                'phone_mobile' => $this->useField('phoneNumber'),
                'lead_source' => '6',
                'lead_source_description' => $this->useField('lead_source_description') ? $this->useField('lead_source_description') : $form->name,
                'lead_temp_c' => 'Hot',
                'campaign_c' => (isset($campaignTracking[0]) ? $campaignTracking[0] : ''),
                'content_creative_c' => (isset($campaignTracking[1]) ? $campaignTracking[1] : ''),
                'keyword_c' => (isset($campaignTracking[2]) ? $campaignTracking[2] : ''),
                'lscr_c' => (isset($campaignTracking[3]) ? $campaignTracking[3] : ''),
                'placement_c' => (isset($campaignTracking[4]) ? $campaignTracking[4] : ''),
                'site_c' => (isset($campaignTracking[5]) ? $campaignTracking[5] : ''),
                'offer_c' => (isset($campaignTracking[6]) ? $campaignTracking[6] : ''),
                'adwords_url_c' => (isset($campaignTracking[7]) ? $campaignTracking[7] : ''),
                'email_optin_c' => $emailOptin
            ];
            if($userID){
                $postParams['assigned_user_id'] = $userID;
                $postParams['status'] = 'Assigned';
            }
            curl_setopt($ch, CURLOPT_URL, getenv('SUGARCRM_BASE_URL') . '/Leads?erased_fields=true');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postParams));
            curl_exec($ch);
        }
    }

    private function inputProvided($x){
        if(isset($this->content[$x]) && !empty($this->content[$x])){
            return true;
        }
        return false;
    }

    private function useField($x){
        $this->excludeFields[] = $x;
        if(isset($this->content[$x]) && is_object($this->content[$x])){
            switch (get_class($this->content[$x])){
                case 'craft\fields\data\MultiOptionsFieldData':
                case 'craft\fields\data\SingleOptionFieldData':
                    /**
                     * @var MultiOptionsFieldData $x
                     */
                    $options = $this->content[$x]->getOptions();
                    $values = [];
                    foreach($options as $option){
                        if($option->selected){
                            $values[] = $option->value;
                        }
                    }
                    return $values;
                    break;
            }
        } else if(isset($this->content[$x])){
            return $this->content[$x];
        }
        return '';
    }

    private function getSugarUserByEmail($email, $headers){
        $postParams =
            '{
            "filter":[
                {
                    "email_addresses.email_address": "'. $email .'"
                }
            ]
        }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, getenv('SUGARCRM_BASE_URL') . '/Users/filter');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        if(isset($result['records']) && !empty($result['records'])) {
            return $result['records'][0]['id'];
        }
        return false;
    }

    private function logLiveAgentError($response){
        if(isset($response->response->status) && $response->response->status == "ERROR"){
            \keiser\formhelpers\Plugin::log($response);
        }
    }

}
