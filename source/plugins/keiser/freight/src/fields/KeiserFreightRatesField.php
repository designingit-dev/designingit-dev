<?php
namespace keiser\freight\fields;

use Craft;
use craft\base\Field;
use craft\helpers\StringHelper;
use craft\base\ElementInterface;
use yii\db\Schema;

class KeiserFreightRatesField extends Field
{

    public $shippingZonesAvailable = [];

    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    public static function displayName(): string
    {
        return 'Keiser Freight Shipping Rates';
    }

    public function getSettingsHtml(): string
    {
        $selected = $this->shippingZonesAvailable;

        $shippingZones = \craft\commerce\Plugin::getInstance()->shippingZones->getAllShippingZones();

        $zones = [];

        foreach ($shippingZones as $shippingZone) {
            $zones[$shippingZone->id] = $shippingZone->name;
        }

        $zone = Craft::$app->view->renderTemplateMacro('_includes/forms', 'checkboxSelectField', [
              [
                'label'   => 'Shipping Zones',
                'instructions' => 'Select which shipping zones should be available.',
                'id'           => 'shippingZonesAvailable',
                'name'         => 'shippingZonesAvailable',
                'options'      => $zones,
                'values'       => $selected
              ]
        ]);

        return $zone;
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $input = '<input type="hidden" name="'. $this->handle .'" value="">';

        $tableHtml = $this->_getInputHtml($this->handle, $value, false);

        if($tableHtml)
        {
            $input .= $tableHtml;
        }

        return $input;
    }

    public function serializeValue($value, ElementInterface $element = null)
    {
        return $value;
    }

    public function getStaticHtml($value, ElementInterface $element): string
    {
        return $this->_getInputHtml(StringHelper::randomString(), $value, true);
    }

    private function _getInputHtml($name, $value, $static)
    {
        // Set allowed zones (configured via the field)
        $allowedZones = $this->shippingZonesAvailable;

        // Get All zones
        $shippingZones = \craft\commerce\Plugin::getInstance()->shippingZones->getAllShippingZones();;

        $zones = [];

        foreach ($shippingZones as $shippingZone) {
            if (in_array($shippingZone->id, $allowedZones)) {
                $zones[$shippingZone->id] = $shippingZone->name;
            }
        }

        // Column headings
        $columns = [
            ['handle' => 'zoneId', 'heading' => 'Method', 'type' => 'singleline'],
            ['handle' => 'rate', 'heading' => 'Standard', 'type' => 'singleline'],
            ['handle' => 'nextDay', 'heading' => 'Next Day', 'type' => 'singleline'],
            ['handle' => 'twoDay', 'heading' => '2-Day', 'type' => 'singleline'],
            ['handle' => 'threeDay', 'heading' => '3-Day', 'type' => 'singleline'],
            ['handle' => 'fullyAssembled', 'heading' => 'Fully-Assembled', 'type' => 'singleline']
        ];

        if ($allowedZones)
        {
            if(!is_array($value)){
                $value = json_decode($value, true);
            }
            if (empty($value))
            {
                $value = [];
                foreach ($allowedZones as $zone) {
                    $value[] = [
                        'zoneId' => $zone,
                        'rate' => '0.00',
                        'nextDay' => '',
                        'twoDay' => '',
                        'threeDay' => '',
                        'fullyAssembled' => '',
                    ];
                }
            } else if(is_array($value)) {
                foreach ($allowedZones as $zone) {
                    $rateFound = false;
                    foreach($value as $rate){
                        if($rate['zoneId'] == $zone){
                            $rateFound = true;
                        }
                    }
                    if(!$rateFound){
                        $value[] = [
                            'zoneId' => $zone,
                            'rate' => '0.00',
                            'nextDay' => '',
                            'twoDay' => '',
                            'threeDay' => '',
                            'fullyAssembled' => '',
                        ];
                    }
                }
                foreach($value as $key => $rate){
                    if(!in_array($rate['zoneId'], $allowedZones)){
                        unset($value[$key]);
                    }
                }
            }

            $id = Craft::$app->view->formatInputId($name);

            return Craft::$app->view->renderTemplate('keiser-freight/table', [
                'id'     => $id,
                'name'   => $name,
                'cols'   => $columns,
                'rows'   => $value,
                'static' => $static,
                'zones'  => $zones
            ]);
        }
    }

    public function getTemplatesPath(){
        return Craft::getAlias('@keiser/freight/templates/_fieldtype');
    }
}
