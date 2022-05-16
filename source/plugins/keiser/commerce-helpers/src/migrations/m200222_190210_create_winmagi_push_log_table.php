<?php

namespace keiser\commercehelpers\migrations;

use Craft;
use craft\db\Migration;

/**
 * m200222_190210_create_winmagi_push_log_table migration.
 */
class m200222_190210_create_winmagi_push_log_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('keiser_commerce_winmagi_push_log', [
            'id' => $this->primaryKey(),
            'orderId' => $this->string()->notNull(),
            'username' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);
        echo ('Created keiser_commerce_winmagi_push_log table');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('keiser_commerce_wimagi_push_log');
    }
}
