<?php

namespace keiser\contacthelpers\migrations;

use Craft;
use craft\db\Migration;

/**
 * m180912_070015_update_sugar_crm_field migration.
 */
class m180912_070015_update_sugar_crm_field extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%fields}}', [
            'type' => \keiser\contacthelpers\fields\SendToSugarCRMField::class,
            'settings' => '{}'
        ], [
            'type' => 'barrelstrength\sproutforms\fields\formfields\Hidden',
            'handle' => 'sendToSugarCRM'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180912_070015_update_sugar_crm_field cannot be reverted.\n";
        return false;
    }
}
