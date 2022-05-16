<?php

namespace keiser\commercehelpers\models;

use yii\db\ActiveRecord;

class KeiserCommerceShippingEmailLog extends ActiveRecord {

    /**
     * @var int|null ID
     */
    public $id;

    public static function tableName()
    {
        return 'keiser_commerce_shipping_email_log';
    }

}