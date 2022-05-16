<?php

namespace keiser\contacthelpers\fields;

use Craft;
use barrelstrength\sproutforms\base\FormField;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use Twig\Markup;
use yii\db\Schema;

class CampaignTrackingField extends FormField implements PreviewableFieldInterface {

    /**
     * @var string The type of database column the field should have in the content table
     */
    public $columnType = Schema::TYPE_TEXT;

    public static $cookieName = 'visitorCampaign';

    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return $this->columnType;
    }

    public static function displayName(): string
    {
        return 'Campaign Tracking';
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate(
            'keiser-contact-helpers/admin/campaigntracking/input',
            [
                'name' => $this->handle,
                'value' => $value ?? '',
            ]
        );
    }

    public function getFrontEndInputHtml($value, $entry, array $renderingOptions = null): Markup
    {
        $rendered = Craft::$app->getView()->renderTemplate(
            'frontend/campaigntracking/input',
            [
                'name' => $this->handle,
                'value' => $value ?? '',
                'field' => $this,
                'renderingOptions' => $renderingOptions
            ]
        );
        return TemplateHelper::raw($rendered);
    }

    public function getExampleInputHtml(): string
    {
        return "Records campaign parameters from the URL. Supported URL params are campaign, content, keyword, lsrc, placement, site, offer. This field can be sent to SugarCRM";
    }

    public function getSettingsHtml(): string
    {
        return '';
    }

    public function getTableAttributeHtml($value, ElementInterface $element = null): string
    {
        $html = "<span>{$value}</span>";
        return $html;
    }

    public function getTemplatesPath(): string {
        return Craft::getAlias('@keiser/contacthelpers/templates/_integrations/sproutforms/fields');
    }

}
