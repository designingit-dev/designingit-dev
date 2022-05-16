<?php

namespace keiser\contacthelpers\fields;

use Craft;
use barrelstrength\sproutforms\base\FormField;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use Twig\Markup;

class SendToMailchimpField extends FormField implements PreviewableFieldInterface {

    public $mailChimpListId = '';

    public function init()
    {
        parent::init();
    }

    public static function displayName(): string
    {
        return 'Subscribe to Mailchimp List';
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $html = "<span>{$this->mailChimpListId}</span>";
        return $html;
    }

    public function getFrontEndInputHtml($value, $entry, array $renderingOptions = null): Markup
    {
        return new Markup('', Craft::$app->charset);
    }

    public function getExampleInputHtml(): string
    {
        return "Remember to configure the Mailchimp List ID in this field's settings. Requires an email field in the
         form with the handle set as 'email'";
    }

    public function getSettingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'keiser-contact-helpers/admin/sendtomailchimp/settings',
            [
                'field' => $this
            ]
        );
    }

    public function getTableAttributeHtml($value, ElementInterface $element = null): string
    {
        $html = "<span>{$this->mailChimpListId}</span>";
        return $html;
    }

    public function getTemplatesPath(): string {
        return Craft::getAlias('@keiser/contacthelpers/templates/_integrations/sproutforms/fields');
    }

}
