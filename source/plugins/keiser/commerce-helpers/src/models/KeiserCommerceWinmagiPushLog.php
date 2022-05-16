<?php

namespace keiser\commercehelpers\models;

use yii\db\ActiveRecord;

class KeiserCommerceWinmagiPushLog extends ActiveRecord {

    /**
     * @var int|null ID
     */
    public $id;

    public static function tableName()
    {
        return 'keiser_commerce_winmagi_push_log';
    }

}
