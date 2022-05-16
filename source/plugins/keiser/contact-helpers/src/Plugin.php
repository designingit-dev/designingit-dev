<?php

namespace keiser\contacthelpers;

use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\events\OnSaveEntryEvent;
use barrelstrength\sproutforms\services\Fields;
use barrelstrength\sproutforms\events\RegisterFieldsEvent;
use barrelstrength\sproutforms\elements\Form;

use Craft;

use craft\base\Field;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use keiser\contacthelpers\fields\CampaignTrackingField;
use keiser\contacthelpers\fields\GeoLocationField;
use keiser\contacthelpers\fields\MarketingOptInField;
use keiser\contacthelpers\fields\PageTitleField;
use keiser\contacthelpers\fields\SendToLiveAgentField;
use keiser\contacthelpers\fields\SendToMailchimpField;
use keiser\contacthelpers\fields\SendToSugarCRMField;
use keiser\contacthelpers\fields\UserLocationField;
use yii\base\Event;

class Plugin extends \craft\base\Plugin {

    public $schemaVersion = '1.0.6';

    public function init(){

        parent::init();

        $this->setComponents([
            'service' => \keiser\contacthelpers\services\Service::class,
        ]);

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('keiserContactHelpers', \keiser\contacthelpers\services\Service::class);
        });

        Event::on(\barrelstrength\sproutforms\elements\Entry::class, \barrelstrength\sproutforms\elements\Entry::EVENT_AFTER_SAVE, function($event){
            $sproutFormsEntry = $event->sender;
            if(Craft::$app->request->isSiteRequest){
                /**
                 * @var Form $form
                 */
                $form = $sproutFormsEntry->getForm();
                $entryContent = $sproutFormsEntry->getAttributes();
                $marketingOptInField = self::getExactAttributeNameForForm('marketingOptIn',$entryContent);
                if($marketingOptInField && $entryContent[$marketingOptInField] === 'yes' && isset($entryContent['email']) && $entryContent['email']){
                    $field = $form->getField($marketingOptInField);
                    if(isset($field->mailChimpListID) && $field->mailChimpListID){
                        $mailChimpSubscriberExists = \keiser\commercehelpers\Plugin::getInstance()->keiserCommerceHelpers->mailChimpSubscriberExists($entryContent['email'], $field->mailChimpListID);
                    } else {
                        $mailChimpSubscriberExists = \keiser\commercehelpers\Plugin::getInstance()->keiserCommerceHelpers->mailChimpSubscriberExists($entryContent['email']);
                    }
                    if(!$mailChimpSubscriberExists){
                        $mergeFields = [];
                        if(isset($entryContent['fullName']) && $entryContent['fullName']){
                            $name = explode(' ', $entryContent['fullName'], 2);
                            $mergeFields['FNAME'] = $name[0];
                            if(isset($name[1])){
                                $mergeFields['LNAME'] = $name[1];
                            }
                        }
                        $requireDoubleOptIn = false;
                        if(isset($field->requireDoubleOptIn) && $field->requireDoubleOptIn){
                            $requireDoubleOptIn = true;
                        }
                        if(isset($field->mailChimpListID) && $field->mailChimpListID){
                            \keiser\commercehelpers\Plugin::getInstance()->keiserCommerceHelpers->mailChimpSubscriberUpdate($entryContent['email'], $mergeFields, $field->mailChimpListID, $requireDoubleOptIn);
                        } else {
                            \keiser\commercehelpers\Plugin::getInstance()->keiserCommerceHelpers->mailChimpSubscriberUpdate($entryContent['email'], $mergeFields, null, $requireDoubleOptIn);
                        }
                    }
                }
                if(isset($entryContent['email']) && $entryContent['email']){
                    foreach($form->getFields() as $field){
                        if($field instanceof SendToMailchimpField && isset($field->mailChimpListId) && $field->mailChimpListId){
                            $mailChimpSubscriberExists = \keiser\commercehelpers\Plugin::getInstance()->keiserCommerceHelpers->mailChimpSubscriberExists($entryContent['email'], $field->mailChimpListId);
                            if(!$mailChimpSubscriberExists){
                                \keiser\commercehelpers\Plugin::getInstance()->keiserCommerceHelpers->mailChimpSubscriberUpdate($entryContent['email'], [], $field->mailChimpListId, false);
                            }
                        }
                    }
                }
            }
        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELDS, function(RegisterFieldsEvent $event) {
            $event->fields[] = new MarketingOptInField();
            $event->fields[] = new UserLocationField();
            $event->fields[] = new GeoLocationField();
            $event->fields[] = new SendToLiveAgentField();
            $event->fields[] = new SendToSugarCRMField();
            $event->fields[] = new PageTitleField();
            $event->fields[] = new CampaignTrackingField();
            $event->fields[] = new SendToMailchimpField();
        });

        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['keiser-contact-helpers'] = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates/_integrations/sproutforms/fields';
        });

        $fileTarget = new \craft\log\FileTarget([
            'logFile' => Craft::getAlias('@storage/logs/keisercontacthelpers.log'),
            'categories' => ['keiser-contact-helpers']
        ]);
        Craft::getLogger()->dispatcher->targets[] = $fileTarget;
    }

    public static function log($message){
        Craft::getLogger()->log($message, \yii\log\Logger::LEVEL_INFO, 'keiser-contact-helpers');
    }

    public static function getExactAttributeNameForForm($fieldName, $arr){
        foreach($arr as $key => $val){
            if(preg_match("/{$fieldName}[0-9]*/", $key)){
                return $key;
            }
        }
        return false;
    }

}
