<?php

namespace keiser\commercehelpers\errors;

use yii\base\Exception;

class OrderNotFoundException extends Exception {

    public function getName(){
        return "Order not found";
    }

}
