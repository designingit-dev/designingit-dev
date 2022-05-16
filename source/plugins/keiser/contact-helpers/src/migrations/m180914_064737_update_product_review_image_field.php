<?php

namespace keiser\contacthelpers\migrations;

use Craft;
use craft\db\Migration;

/**
 * m180914_064737_update_product_review_image_field migration.
 */
class m180914_064737_update_product_review_image_field extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%fields}}', [
            'settings' => '{"cssClasses":"","useSingleFolder":1,"defaultUploadLocationSource":"folder:1","defaultUploadLocationSubpath":"job-applications","singleUploadLocationSource":"folder:1","singleUploadLocationSubpath":"product-review-images","restrictFiles":"1","allowedKinds":["image"],"sources":"*","source":null,"targetSiteId":null,"viewMode":"large","limit":"1","selectionLabel":"+ ADD AN IMAGE (OPTIONAL)","localizeRelations":false}'
        ], [
            'type' => \barrelstrength\sproutforms\fields\formfields\FileUpload::class,
            'handle' => 'reviewImage'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180914_064737_update_product_review_image_field cannot be reverted.\n";
        return false;
    }
}
