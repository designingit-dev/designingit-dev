<?php

namespace keiser\contacthelpers\migrations;

use Craft;
use craft\db\Migration;
use yii\db\Expression;

/**
 * m180910_080737_update_live_agent_field migration.
 */
class m180910_080737_update_live_agent_field extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%fields}}', [
            'type' => \keiser\contacthelpers\fields\SendToLiveAgentField::class,
        ], [
            'type' => 'barrelstrength\sproutforms\fields\formfields\Hidden',
            'handle' => 'sendToLiveAgent'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180910_080737_update_live_agent_field cannot be reverted.\n";
        return false;
    }
}
