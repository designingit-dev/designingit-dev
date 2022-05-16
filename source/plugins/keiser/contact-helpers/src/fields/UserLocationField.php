<?php

namespace keiser\contacthelpers\fields;

use Craft;
use barrelstrength\sproutforms\base\FormField;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use Twig\Markup;

class UserLocationField extends FormField implements PreviewableFieldInterface {

    public function init()
    {
        parent::init();
    }

    public static function displayName(): string
    {
        return 'User Location';
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->view->renderTemplate(
            'keiser-contact-helpers/admin/userlocation/input',
            [
                'name' => $this->handle,
                'value' => $value
            ]
        );
    }

    public function getFrontEndInputHtml($value, $entry, array $renderingOptions = null): Markup
    {
        $rendered = Craft::$app->view->renderTemplate(
            'frontend/userlocation/input',
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
        return 'Displays an interactive input to capture the visitor ZIP (US Only) or Country Code. This field can be sent to SugarCRM & LiveAgent';
    }

    public function getTableAttributeHtml($value, ElementInterface $element = null): string
    {
        $userLocationParts = explode(';', $value);
        $html = '';
        if(isset($userLocationParts[0])){
            $html .= "<span>Country: {$userLocationParts[0]}</span>";
        }
        if(isset($userLocationParts[1])){
            $html .= "<br><span>ZIP: {$userLocationParts[1]}</span>";
        }
        return $html;
    }

    public function getTemplatesPath(): string {
        return Craft::getAlias('@keiser/contacthelpers/templates/_integrations/sproutforms/fields');
    }

}
