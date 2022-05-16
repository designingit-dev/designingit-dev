<?php

namespace keiser\commercehelpers\integrations\sproutforms\fields;

use craft\helpers\Template as TemplateHelper;

/**
 * Class KeiserRichTextField
 *
 * @package Craft
 */
class KeiserRichTextField extends SproutFormsBaseField
{
    /**
     * @return string
     */
    public function getType()
    {
        return 'RichText';
    }

    /**
     * @param FieldModel $field
     * @param mixed      $value
     * @param mixed      $settings
     * @param array|null $renderingOptions
     *
     * @return \Twig_Markup
     */
    public function getInputHtml($field, $value, $settings, array $renderingOptions = null)
    {
        return TemplateHelper::raw('');
    }

}
