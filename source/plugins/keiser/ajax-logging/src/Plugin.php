<?php

namespace keiser\ajaxlogging;

use Craft;

class Plugin extends \craft\base\Plugin {

    public function init(){
        parent::init();

        $fileTarget = new \craft\log\FileTarget([
            'logFile' => Craft::getAlias('@storage/logs/keiserajaxlogging.log'),
            'categories' => ['keiser-ajax-logging']
        ]);
        Craft::getLogger()->dispatcher->targets[] = $fileTarget;
    }

    public static function log($message, $level, $category){
        Craft::getLogger()->log($message, $level, $category);
    }

}