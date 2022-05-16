<?php

namespace keiser\commercehelpers\migrations;

use Craft;
use craft\db\Migration;

/**
 * m200222_133236_set_winmagipush_for_existing_orders migration.
 */
class m200222_133236_set_winmagipush_for_existing_orders extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        Craft::$app->db->createCommand('UPDATE content SET field_orderPushedToWinmagi = 1 WHERE content.elementId IN (SELECT id FROM commerce_orders WHERE isCompleted = 1)')->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200222_133236_set_winmagipush_for_existing_orders cannot be reverted.\n";
        return false;
    }
}
