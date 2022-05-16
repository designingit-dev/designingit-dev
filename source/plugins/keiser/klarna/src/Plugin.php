<?php

namespace keiser\klarna;

use Craft;
use craft\commerce\services\Gateways;
use craft\events\RegisterComponentTypesEvent;
use craft\web\twig\variables\CraftVariable;
use keiser\klarna\gateways\Gateway;
use keiser\klarna\services\Service;
use yii\base\Event;

class Plugin extends \craft\base\Plugin {

    public static $klarnaSessionIdKey = 'klarnaSessionId';

    public function init(){
        parent::init();

        Event::on(Gateways::class, Gateways::EVENT_REGISTER_GATEWAY_TYPES, function(RegisterComponentTypesEvent $event){
            $event->types[] = Gateway::class;
        });

        $this->setComponents([
            'service' => \keiser\klarna\services\Service::class,
        ]);

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('keiserKlarnaHelpers', \keiser\klarna\services\Service::class);
        });

        $fileTarget = new \craft\log\FileTarget([
            'logFile' => Craft::getAlias('@storage/logs/keiserklarna.log'),
            'categories' => ['keiser-klarna']
        ]);
        Craft::getLogger()->dispatcher->targets[] = $fileTarget;
    }

    public static function log($message){
        Craft::getLogger()->log($message, \yii\log\Logger::LEVEL_INFO, 'keiser-klarna');
    }

    /**
     * @return Service|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getService(){
        return $this->get('service');
    }

}
