<?php

namespace keiser\searchhelpers\models;

use craft\base\Model;

class Settings extends Model
{
    public $algoliaApplicationId;
    public $algoliaAdminApiKey;
    public $algoliaSearchApiKey;
    public $algoliaIndexPrefix;

}