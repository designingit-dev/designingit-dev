<?php

namespace keiser\redirectmanager\records;

use craft\db\ActiveRecord;

/**
 * RedirectManagerRecord
 *
 * @property string $uri
 * @property string $location
 * @property string $type
 */
class RedirectManagerRecord extends ActiveRecord
{

    public static function tableName(): string
    {
        return 'redirectmanager';
    }
}
