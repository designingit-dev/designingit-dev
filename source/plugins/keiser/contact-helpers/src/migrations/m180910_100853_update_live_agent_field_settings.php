<?php

namespace keiser\contacthelpers\migrations;

use Craft;
use craft\db\Migration;

/**
 * m180910_100853_update_live_agent_field_settings migration.
 */
class m180910_100853_update_live_agent_field_settings extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%fields}}', [
            'settings' => '{}'
        ], [
            'type' => \keiser\contacthelpers\fields\SendToLiveAgentField::class,
            'handle' => 'sendToLiveAgent'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180910_100853_update_live_agent_field_settings cannot be reverted.\n";
        return false;
    }
}
