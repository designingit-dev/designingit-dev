<?php

namespace keiser\contacthelpers\fields;

use Craft;
use barrelstrength\sproutforms\base\FormField;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use Twig\Markup;

class SendToLiveAgentField extends FormField implements PreviewableFieldInterface {

    public $liveAgentDepartmentId = '';

    public function init()
    {
        parent::init();
    }

    public static function displayName(): string
    {
        return 'Send To Live Agent';
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $html = "<span>{$this->liveAgentDepartmentId}</span>";
        return $html;
    }

    public function getFrontEndInputHtml($value, $entry, array $renderingOptions = null): Markup
    {
        return new Markup('', Craft::$app->charset);
    }

    public function getExampleInputHtml(): string
    {
        return "Remember to configure the LiveAgent Department ID in this field's settings. Field names must match those required by the chosen integration              
                <br> Some commonly used field handles are fullName, phoneNumber, email, zipCode, state, country, message
                <br> The integration will also generally send all fields in the form to LiveAgent even if they are not pre-mapped";
    }

    public function getSettingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'keiser-contact-helpers/admin/sendtoliveagent/settings',
            [
                'field' => $this
            ]
        );
    }

    public function getTableAttributeHtml($value, ElementInterface $element = null): string
    {
        $html = "<span>{$this->liveAgentDepartmentId}</span>";
        return $html;
    }

    public function getTemplatesPath(): string {
        return Craft::getAlias('@keiser/contacthelpers/templates/_integrations/sproutforms/fields');
    }

}
