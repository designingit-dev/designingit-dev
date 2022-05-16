<?php

namespace keiser\contacthelpers\migrations;

use barrelstrength\sproutforms\fields\formfields\SingleLine;
use Craft;
use craft\db\Migration;

/**
 * m180914_092817_update_product_review_invisible_fields migration.
 */
class m180914_092817_update_product_review_invisible_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%fields}}', [
            'type' => SingleLine::class,
            'settings' => '{}'
        ], [
            'type' => \barrelstrength\sproutforms\fields\formfields\Invisible::class,
            'handle' => 'editUrl'
        ]);

        $this->update('{{%fields}}', [
            'type' => SingleLine::class,
            'settings' => '{}'
        ], [
            'type' => \barrelstrength\sproutforms\fields\formfields\Invisible::class,
            'handle' => 'authToken'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180914_092817_update_product_review_invisible_fields cannot be reverted.\n";
        return false;
    }
}
