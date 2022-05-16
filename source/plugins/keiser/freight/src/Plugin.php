<?php

namespace keiser\freight;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use keiser\freight\ShippingMethod;
use craft\commerce\events\RegisterAvailableShippingMethodsEvent;
use craft\services\Fields;
use yii\base\Event;
use craft\web\View;

class Plugin extends \craft\base\Plugin
{

    public $schemaVersion = '1.0.3';

    public function init(){

        parent::init();

        $this->setComponents([
            'keiserFreightRatesService' => \keiser\freight\services\KeiserFreightRatesService::class
        ]);

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = \keiser\freight\fields\KeiserFreightRatesField::class;
        });

        Event::on(
            \craft\commerce\services\ShippingMethods::class,
            \craft\commerce\services\ShippingMethods::EVENT_REGISTER_AVAILABLE_SHIPPING_METHODS,
            function(RegisterAvailableShippingMethodsEvent $e){
                $shippingMethods = [];

                $carrier = (object) [
                    'id' => 'keiser',
                    'readable' => 'Keiser'
                ];

                if($e->order)
                {
                    $rates = $this->keiserFreightRatesService->getRates($e->order);

                    if(Craft::$app->config->general->devMode)
                    {
                        self::log('Rates for Order #'.$e->order->id." (Order Number: ".$e->order->number);
                    }

                    if(gettype($rates) == 'object')
                    {
                        foreach($rates as $rate)
                        {
                            if(Craft::$app->config->general->devMode)
                            {
                                self::log('Rate: '.$rate->service . ' ('.$rate->rate.')');
                            }

                            $shippingMethods[] = new ShippingMethod($carrier, ['handle' => $rate->id, 'name' => $rate->service], $rate, $e->order);

                        }
                    }
                }

                if (count($shippingMethods) == 0) {
                    $shippingMethods[] = new ShippingMethod($carrier, ['handle' => 'freight', 'name' => 'Freight']);
                    $shippingMethods[] = new ShippingMethod($carrier, ['handle' => 'NextDay', 'name' => 'Next Day Shipping']);
                    $shippingMethods[] = new ShippingMethod($carrier, ['handle' => 'TwoDay', 'name' => '2-Day Shipping']);
                    $shippingMethods[] = new ShippingMethod($carrier, ['handle' => 'ThreeDay', 'name' => '3-Day Shipping']);
                    $shippingMethods[] = new ShippingMethod($carrier, ['handle' => 'FullyAssembled', 'name' => 'White Glove Fully Assembled']);
                }

                $e->shippingMethods = array_merge($e->shippingMethods, $shippingMethods);
            }
        );

        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['keiser-freight'] = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates/_fieldtype';
        });

        $fileTarget = new \craft\log\FileTarget([
            'logFile' => Craft::getAlias('@storage/logs/keiserfreight.log'),
            'categories' => ['keiser-freight']
        ]);
        Craft::getLogger()->dispatcher->targets[] = $fileTarget;
    }

    public static function log($message){
        Craft::getLogger()->log($message, \yii\log\Logger::LEVEL_INFO, 'keiser-freight');
    }
}
