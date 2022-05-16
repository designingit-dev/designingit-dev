<?php

namespace keiser\contacthelpers\fields;

use Craft;
use barrelstrength\sproutforms\base\FormField;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use Twig\Markup;

class MarketingOptInField extends FormField implements PreviewableFieldInterface {

    public $mailChimpListID = '';
    public $requireDoubleOptIn = false;

    public function init()
    {
        parent::init();
    }

    public static function displayName(): string
    {
        return 'Marketing OptIn';
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->view->renderTemplate(
            'keiser-contact-helpers/admin/marketingoptin/input',
            [
                'name' => $this->handle,
                'value' => $value
            ]
        );
    }

    public function getFrontEndInputHtml($value, $entry, array $renderingOptions = null): Markup
    {
        $rendered = Craft::$app->view->renderTemplate(
            'frontend/marketingoptin/input',
            [
                'name'             => $this->handle,
                'value'            => $value,
                'field'            => $this,
                'renderingOptions' => $renderingOptions,
            ]
        );
        return TemplateHelper::raw($rendered);
    }

    public function getExampleInputHtml(): string
    {
        return "Remember to set the MailChimp List ID & Require Double Opt-in in this field's settings. This field can be sent to SugarCRM & LiveAgent";
    }

    public function getTableAttributeHtml($value, ElementInterface $element = null): string
    {
        $html = '<span>' . $value . '</span>';
        return $html;
    }

    public function getSettingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'keiser-contact-helpers/admin/marketingoptin/settings',
            [
                'field' => $this
            ]
        );
    }

    public function getTemplatesPath(): string {
        return Craft::getAlias('@keiser/contacthelpers/templates/_integrations/sproutforms/fields');
    }

}
