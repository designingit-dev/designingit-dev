<?php

namespace keiser\supporthelpers;

use Craft;
use craft\base\Element;
use craft\elements\Entry;
use craft\events\RegisterElementSourcesEvent;
use yii\base\Event;

class Plugin extends \craft\base\Plugin {

    public $hasCpSection = true;

    public function init()
    {
        parent::init();

        Event::on(Entry::class, Entry::EVENT_REGISTER_SOURCES, function(RegisterElementSourcesEvent $event) {
            if ($event->context === 'index') {
                $sourceKeys = $this->findSupportEntityIndexes($event->sources);
                if(Craft::$app->getRequest()->getUrl() == '/admin/keiser-support-helpers'){
                    // Display only support elements when browsing /admin/keiser-support-helpers
                    $filteredSources = [];
                    foreach($sourceKeys as $sourceKey){
                        $filteredSources[$sourceKey] = $event->sources[$sourceKey];
                    }
                    $event->sources = $filteredSources;
                } else if(!Craft::$app->getRequest()->getAcceptsJson()) {
                    //Don't display support elements in Admin -> Entries
                    foreach($sourceKeys as $sourceKey){
                        unset($event->sources[$sourceKey]);
                    }
                }
            }
        });

    }

    private function findSupportEntityIndexes($sources){
        $supportEntityHandles = [
            'supportArticles',
            'supportFAQ',
            'supportAnnouncements',
            'supportCategories',
            'supportLinks',
            'supportHomepage',
            'supportLinkBlockWithIcon',
            'supportCopyBlockWithLink'
        ];
        $sourceKeys = [];
        foreach($sources as $sourceKey => $source){
            if(isset($source['data']['handle']) && in_array($source['data']['handle'], $supportEntityHandles)){
                $sourceKeys[] = $sourceKey;
            }
        }
        return $sourceKeys;
    }

}
