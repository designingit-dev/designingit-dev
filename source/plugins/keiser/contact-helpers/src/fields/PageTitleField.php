<?php

namespace keiser\contacthelpers\fields;

use Craft;
use barrelstrength\sproutforms\base\FormField;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use Twig\Markup;
use yii\db\Schema;

class PageTitleField extends FormField implements PreviewableFieldInterface {

    /**
     * @var string The type of database column the field should have in the content table
     */
    public $columnType = Schema::TYPE_TEXT;

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
        return 'Page Title';
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate(
            'keiser-contact-helpers/admin/pagetitle/input',
            [
                'name' => $this->handle,
                'value' => $value ?? '',
            ]
        );
    }

    public function getFrontEndInputHtml($value, $entry, array $renderingOptions = null): Markup
    {
        $rendered = Craft::$app->getView()->renderTemplate(
            'frontend/pagetitle/input',
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
        return 'Captures the Title & URL of the page on which this form is displayed. This field can be sent to SugarCRM';
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
