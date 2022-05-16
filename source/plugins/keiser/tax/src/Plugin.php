<?php

namespace keiser\tax;

use Craft;
use yii\base\Event;
use craft\events\RegisterComponentTypesEvent;
use craft\commerce\services\OrderAdjustments;

class Plugin extends \craft\base\Plugin
{
    public function init()
    {
        parent::init();

        $this->setComponents([
            'salesTaxService' => \keiser\tax\services\Service::class
        ]);

        Event::on(OrderAdjustments::class, OrderAdjustments::EVENT_REGISTER_ORDER_ADJUSTERS, function(RegisterComponentTypesEvent $e){
            $e->types[] = \keiser\tax\adjusters\KeiserTaxAdjuster::class;
        });

        $fileTarget = new \craft\log\FileTarget([
            'logFile' => Craft::getAlias('@storage/logs/keisertax.log'),
            'categories' => ['keiser-tax']
        ]);
        Craft::getLogger()->dispatcher->targets[] = $fileTarget;

    }

    protected function createSettingsModel()
    {
        return new \keiser\tax\models\Settings();
    }

    protected function settingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('keiser-tax/settings', [
            'settings' => $this->getSettings()
        ]);
    }

    public static function log($message){
        Craft::getLogger()->log($message, \yii\log\Logger::LEVEL_INFO, 'keiser-tax');
    }

}