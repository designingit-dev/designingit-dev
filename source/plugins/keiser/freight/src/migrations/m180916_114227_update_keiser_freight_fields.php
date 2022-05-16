<?php

namespace keiser\freight\migrations;

use Craft;
use craft\db\Migration;

/**
 * m180916_114227_update_keiser_freight_fields migration.
 */
class m180916_114227_update_keiser_freight_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%fields}}', [
            'type' => \keiser\freight\fields\KeiserFreightRatesField::class
        ], ['type' => 'KeiserFreight_Rates']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180916_114227_update_keiser_freight_fields cannot be reverted.\n";
        return false;
    }
}
