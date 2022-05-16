<?php

namespace keiser\freight\migrations;

use Craft;
use craft\db\Migration;

/**
 * m181030_225056_update_keiser_freight_field_datatype migration.
 */
class m181030_225056_update_keiser_freight_field_datatype extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('{{%content}}', 'field_shippingRates', 'text');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->alterColumn('{{%content}}', 'field_shippingRates', 'varchar(255)');
    }
}
