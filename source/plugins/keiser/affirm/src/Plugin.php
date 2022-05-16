<?php

namespace keiser\affirm;


use Craft;
use craft\commerce\services\Gateways;
use craft\events\RegisterComponentTypesEvent;
use keiser\affirm\gateways\Gateway;
use yii\base\Event;

class Plugin extends \craft\base\Plugin {

    public function init(){
        parent::init();

        Event::on(Gateways::class, Gateways::EVENT_REGISTER_GATEWAY_TYPES, function(RegisterComponentTypesEvent $event){
            $event->types[] = Gateway::class;
        });

        $fileTarget = new \craft\log\FileTarget([
            'logFile' => Craft::getAlias('@storage/logs/keiseraffirm.log'),
            'categories' => ['keiser-affirm']
        ]);
        Craft::getLogger()->dispatcher->targets[] = $fileTarget;
    }

    public static function log($message){
        Craft::getLogger()->log($message, \yii\log\Logger::LEVEL_INFO, 'keiser-affirm');
    }

}
