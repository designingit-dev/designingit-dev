<?php

namespace keiser\commercehelpers\migrations;

use Craft;
use craft\db\Migration;

/**
 * m181130_210622_create_shipping_emails_log_table migration.
 */
class m181130_210622_create_shipping_emails_log_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('keiser_commerce_shipping_email_log', [
            'id' => $this->primaryKey(),
            'orderId' => $this->string()->notNull(),
            'shipmentTrackingNumber' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);
        echo ('Created keiser_commerce_shipping_email_log table');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('keiser_commerce_shipping_email_log');
    }
}
