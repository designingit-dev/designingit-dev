<?php

namespace keiser\commercehelpers\migrations;

use Craft;
use craft\db\Migration;

/**
 * m200118_131011_create_sessions_table migration.
 */
class m200118_131011_create_sessions_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('session', [
            'id' => $this->string(255)->notNull(),
            'uid' => $this->string(255)->notNull()->unique(),
            'expire' => $this->integer(),
            'data' => $this->binary(),
            'dateCreated' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'dateUpdated' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
        $this->addPrimaryKey('id','session', 'id');
        echo ('Created session table');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('session');
    }
}
