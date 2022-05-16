<?php

namespace keiser\redirectmanager\models;

use craft\base\Model;

class RedirectManagerModel extends Model
{
    /**
     * @var int $id
     */
    public $id;

    /**
     * @var string $uri
     */
    public $uri;

    /**
     * @var string $location
     */
    public $location;

    /**
     * @var string $type
     */
    public $type;
}
