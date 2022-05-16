<?php

namespace keiser\commercehelpers\errors;

use yii\base\Exception;

class EnvironmentVariableNotFound extends Exception {

    public function getName(){
        return 'Environment variable not found';
    }

}
