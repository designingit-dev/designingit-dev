<?php

namespace keiser\contacthelpers\fields;

use Craft;
use barrelstrength\sproutforms\base\FormField;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use Twig\Markup;

class GeoLocationField extends FormField implements PreviewableFieldInterface {

    public function init()
    {
        parent::init();
    }

    public static function displayName(): string
    {
        return 'Geo Location';
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate(
            'keiser-contact-helpers/admin/geolocation/input',
            [
                'name' => $this->handle,
                'value' => $value ?? '',
            ]
        );
    }

    public function getFrontEndInputHtml($value, $entry, array $renderingOptions = null): Markup
    {
        $rendered = Craft::$app->getView()->renderTemplate(
            'frontend/geolocation/input',
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
        return 'Captures the visitor city,state,country,zipcode (US only) from their IP address. This field can be sent to SugarCRM & LiveAgent';
    }

    public function getSettingsHtml(): string
    {
        return '';
    }

    public function getTableAttributeHtml($value, ElementInterface $element = null): string
    {
        $userLocationParts = explode(';', $value);
        $html = "";
        if(isset($userLocationParts[0])){
            $html .= "<span>City: {$userLocationParts[0]}</span>";
        }
        if(isset($userLocationParts[1])){
            $html .= "<br><span>State: {$userLocationParts[1]}</span>";
        }
        if(isset($userLocationParts[2])){
            $html .= "<br><span>Country: {$userLocationParts[2]}</span>";
        }
        if(isset($userLocationParts[3])){
            $html .= "<br><span>ZIP: {$userLocationParts[3]}</span>";
        }
        return $html;
    }

    public function getTemplatesPath(): string {
        return Craft::getAlias('@keiser/contacthelpers/templates/_integrations/sproutforms/fields');
    }

}
