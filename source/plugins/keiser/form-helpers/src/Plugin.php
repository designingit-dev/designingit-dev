<?php

namespace keiser\formhelpers;

use barrelstrength\sproutforms\elements\Form;
use Craft;
use craft\events\TemplateEvent;
use \craft\web\twig\variables\CraftVariable;
use craft\web\View;
use yii\base\Event;

class Plugin extends \craft\base\Plugin {

    public function init(){
        parent::init();

        $this->setComponents([
            'keiserFormHelpers' => \keiser\formhelpers\services\Service::class
        ]);

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event){
            $variable = $event->sender;
            $variable->set('keiserFormHelpers', \keiser\formhelpers\services\Service::class);
        });

        Event::on(\barrelstrength\sproutforms\elements\Entry::class, \barrelstrength\sproutforms\elements\Entry::EVENT_AFTER_SAVE, function(Event $event){
            $entry = $event->sender;
            if($entry && !$entry->getIsSpam()){
                $content = $entry->getAttributes();
                $formFields = $entry->getFields();
                /**
                 * @var $form Form
                 */
                $form = $entry->getForm();
                $sendToLiveAgentField = \keiser\contacthelpers\Plugin::getExactAttributeNameForForm('sendToLiveAgent', $formFields);
                if($sendToLiveAgentField) {
                    $this->keiserFormHelpers->sendToLiveAgent($content, $formFields, $formFields[$sendToLiveAgentField]->liveAgentDepartmentId);
                }
                $sendToSugarCRMField = \keiser\contacthelpers\Plugin::getExactAttributeNameForForm('sendToSugarCRM', $formFields);
                if($sendToSugarCRMField){
                    if($form->handle == 'demoBrochure'){
                        $rep = \keiser\contacthelpers\Plugin::getInstance()->service->findKeiserRep($content['countryISO'], $content['zipCode']);
                        if($rep){
                            $content['repEmail'] = $rep->email;
                            if(isset($rep->repVP[0])){
                                $content['repVPEmail'] = $rep->repVP[0]->email;
                            }
                        }
                        $geolocationField = \keiser\contacthelpers\Plugin::getExactAttributeNameForForm('geoLocation', $content);
                        $content['message'] = 'Van Visit Request from ' . $content[$geolocationField];
                        $content['lead_source_description'] = 'Van Visit Request Web Form Submit';
                    }
                    $this->keiserFormHelpers->sendToSugarCRM($content, $form);
                }
                $identifyFields = [
                    'countryISO',
                    'zip',
                    'fullName',
                    'customerTitle',
                    'email',
                    'phoneNumber',
                    'institutionType',
                    'marketingOptIn',
                    'userLocation'
                ];
                $identifyValues = [];
                foreach($identifyFields as $field){
                    if($fieldName = \keiser\contacthelpers\Plugin::getExactAttributeNameForForm($field, $formFields)){
                        $identifyValues[$field] = $content[$fieldName];
                    }
                }
                if(!empty($identifyValues)){
                    $email = "";
                    if(isset($identifyValues['email'])){
                        $email = $identifyValues['email'];
                    }
                    if(isset($identifyValues['userLocation'])){
                        $userLocationParts = explode(';', $identifyValues['userLocation']);
                        if(isset($userLocationParts[0])){
                            $identifyValues['countryISO'] = \keiser\contacthelpers\Plugin::getInstance()->service->getCountryISOFromCountryName($userLocationParts[0]);
                        }
                        if(isset($userLocationParts[1])){
                            $identifyValues['zip'] = $userLocationParts[1];
                        }
                        unset($identifyValues['userLocation']);
                    }
                    if(isset($identifyValues['institutionType'])){
                        /**
                         * @var $institutionType craft\fields\data\SingleOptionFieldData
                         */
                        $institutionType = $identifyValues['institutionType'];
                        $identifyValues['institutionType'] = $institutionType->value;
                    }
                    $identifyValues['isB2B'] = true;
                    $identifyCall = 'rudderanalytics.identify("' . hash('sha256', $email). '",';
                    $identifyValues = json_encode($identifyValues);
                    $identifyCall .= $identifyValues . ');';
                    Craft::$app->getSession()->addJsFlash($identifyCall, View::POS_END, 'rudderFormIdentify');
                }
                $trackCall = 'rudderanalytics.track("Form Submission", {
                    "form_name": "'. $form->name .'",
                    "form_handle": "'. $form->handle .'",
                    "category": "form_submission",
                    "label": "'. $form->name .'",
                    "value": "'. $form->handle .'",
                    "site": window.location.hostname
                });';
                Craft::$app->getSession()->addJsFlash($trackCall, View::POS_END, 'rudderFormTrack');
            }
        });

        $fileTarget = new \craft\log\FileTarget([
            'logFile' => Craft::getAlias('@storage/logs/keiserformhelpers.log'),
            'categories' => ['keiser-form-helpers']
        ]);
        Craft::getLogger()->dispatcher->targets[] = $fileTarget;

        if (Craft::$app->getRequest()->isCpRequest && !Craft::$app->getUser()->getIsGuest()) {
            Event::on(
                View::class,
                View::EVENT_BEFORE_RENDER_TEMPLATE,
                function (TemplateEvent $event) {
                    $view = Craft::$app->getView();
                    $view->registerJs(file_get_contents(__DIR__ . '/resources/keiser-form-helpers.js'), View::POS_END);
                    $view->registerCss(file_get_contents(__DIR__ . '/resources/keiser-form-helpers.css'));
                }
            );
        }

    }

    public static function log($message){
        Craft::getLogger()->log($message, \yii\log\Logger::LEVEL_INFO, 'keiser-form-helpers');
    }

}
