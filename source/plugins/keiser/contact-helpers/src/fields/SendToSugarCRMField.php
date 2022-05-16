<?php

namespace keiser\contacthelpers\fields;

use Craft;
use barrelstrength\sproutforms\base\FormField;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use Twig\Markup;

class SendToSugarCRMField extends FormField implements PreviewableFieldInterface {

    public function init()
    {
        parent::init();
    }

    public static function displayName(): string
    {
        return 'Send To SugarCRM';
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $html = "<span>Yes</span>";
        return $html;
    }

    public function getFrontEndInputHtml($value, $entry, array $renderingOptions = null): Markup
    {
        return new Markup('', Craft::$app->charset);
    }

    public function getExampleInputHtml(): string
    {
        return "Field names must match those required by the chosen integration
                <br>Supports commonly used field handles such as fullName, phoneNumber, email, zipCode, message, interestedProducts, institutionType
                <br>To assign tickets to reps, use a text field with handle repEmail or repVPEmail";
    }

    public function getSettingsHtml(): string
    {
        return '';
    }

    public function getTableAttributeHtml($value, ElementInterface $element = null): string
    {
        $html = "<span>Yes</span>";
        return $html;
    }

    public function getTemplatesPath(): string {
        return Craft::getAlias('@keiser/contacthelpers/templates/_integrations/sproutforms/fields');
    }

}
