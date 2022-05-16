<?php
/**
 * Keiser Search Helpers plugin for Craft CMS
 *
 * This plugin powers the Algolia search integration of keiser.com
 *
 * @author    Akshay Agarwal
 * @copyright Copyright (c) 2018 Akshay Agarwal
 * @link      https://10xmanagement.com
 * @package   KeiserSearchHelpers
 * @since     1.0.0
 */

namespace keiser\searchhelpers;

use Craft;
use craft\elements\Entry;
use keiser\searchhelpers\models\Settings;
use yii\base\Event;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use craft\events\RegisterTemplateRootsEvent;

class Plugin extends \craft\base\Plugin
{

    public $hasCpSettings = true;

    public function init()
    {
        parent::init();

        $this->setComponents([
            'keiserSearchHelpers' => \keiser\searchhelpers\services\KeiserSearchHelpersService::class
        ]);

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event){
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('keiserSearchHelpers', \keiser\searchhelpers\services\KeiserSearchHelpersService::class);
        });

        Event::on(\craft\elements\Entry::class, \craft\elements\Entry::EVENT_AFTER_SAVE, function(Event $event){
            /** @var \craft\elements\Entry $entry */
            $entry = $event->sender;
            if(!$entry->getIsDraft() && !$entry->getIsUnpublishedDraft() && !$entry->getIsRevision()) {
                if($entry->enabled && (isset($entry->metaNoIndex) ? !$entry->metaNoIndex : true)){
                    $this->keiserSearchHelpers->updateAlgoliaIndex('add', $entry);
                } else {
                    $this->keiserSearchHelpers->updateAlgoliaIndex('remove', $entry);
                }
            }
        });

        Event::on(\craft\elements\Entry::class, \craft\elements\Entry::EVENT_AFTER_DELETE, function(Event $event){
            /** @var \craft\elements\Entry $entry */
            $entry = $event->sender;
            if(!$entry->getIsDraft() && !$entry->getIsUnpublishedDraft() && !$entry->getIsRevision()) {
                $this->keiserSearchHelpers->updateAlgoliaIndex('remove', $entry);
            }
        });

        Event::on(\craft\base\Plugin::class, \craft\base\Plugin::EVENT_AFTER_SAVE_SETTINGS, function(Event $event){
            $this->keiserSearchHelpers->buildAlgoliaIndex($this->getSettings());
        });

        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['keiser-search-helpers'] = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates';
        });

    }

    protected function createSettingsModel()
    {
        return new Settings();
    }

    protected function settingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('keiser-search-helpers/settings', [
            'settings' => $this->getSettings()
        ]);
    }
}
