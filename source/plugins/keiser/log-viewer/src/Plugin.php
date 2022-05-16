<?php

namespace keiser\logviewer;
use yii\base\Event;

class Plugin extends \craft\base\Plugin
{
    public $hasCpSection = true;

    public function init()
    {
        parent::init();

        Event::on(\craft\web\twig\variables\CraftVariable::class, \craft\web\twig\variables\CraftVariable::EVENT_INIT, function(Event $event){
           $variable = $event->sender;
           $variable->set('logViewer', \keiser\logviewer\services\Service::class);
        });

        $this->setComponents([
            'logViewer' => \keiser\logviewer\services\Service::class
        ]);

    }
}