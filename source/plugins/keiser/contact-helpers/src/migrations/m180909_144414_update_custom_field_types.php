<?php

namespace keiser\contacthelpers\migrations;

use Craft;
use craft\db\Migration;

/**
 * m180909_144414_update_custom_field_types migration.
 */
class m180909_144414_update_custom_field_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%fields}}', [
            'type' => \keiser\contacthelpers\fields\UserLocationField::class
        ], ['type' => 'KeiserContactHelpers_UserLocation']);

        $this->update('{{%fields}}', [
            'type' => \keiser\contacthelpers\fields\GeoLocationField::class
        ], ['type' => 'KeiserContactHelpers_GeoLocation']);

        $this->update('{{%fields}}', [
            'type' => \keiser\contacthelpers\fields\MarketingOptInField::class
        ], ['type' => 'KeiserContactHelpers_MarketingOptIn']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->update('{{%fields}}', [
            'type' => 'KeiserContactHelpers_UserLocation'
        ], ['type' => \keiser\contacthelpers\fields\UserLocationField::class]);

        $this->update('{{%fields}}', [
            'type' => 'KeiserContactHelpers_GeoLocation'
        ], ['type' => \keiser\contacthelpers\fields\GeoLocationField::class]);

        $this->update('{{%fields}}', [
            'type' => 'KeiserContactHelpers_MarketingOptIn'
        ], ['type' => \keiser\contacthelpers\fields\MarketingOptInField::class]);
    }
}
