<?php

namespace keiser\stripe;


use Craft;
use craft\commerce\services\Gateways;
use craft\events\RegisterComponentTypesEvent;
use craft\web\twig\variables\CraftVariable;
use keiser\stripe\gateways\Gateway;
use yii\base\Event;

class Plugin extends \craft\base\Plugin {

    public function init(){
        parent::init();

        Event::on(Gateways::class, Gateways::EVENT_REGISTER_GATEWAY_TYPES, function(RegisterComponentTypesEvent $event){
            $event->types[] = Gateway::class;
        });

        Event::on(\craft\web\twig\variables\CraftVariable::class, CraftVariable::EVENT_INIT, function($event){
            $variable = $event->sender;
            $variable->set('keiserStripeGateway', \keiser\stripe\services\Service::class);
        });

        $fileTarget = new \craft\log\FileTarget([
            'logFile' => Craft::getAlias('@storage/logs/keiserstripe.log'),
            'categories' => ['keiser-stripe']
        ]);
        Craft::getLogger()->dispatcher->targets[] = $fileTarget;
    }

    public static function log($message){
        Craft::getLogger()->log($message, \yii\log\Logger::LEVEL_INFO, 'keiser-stripe');
    }

}
