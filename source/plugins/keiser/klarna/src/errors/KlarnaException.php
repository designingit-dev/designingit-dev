<?php

namespace keiser\klarna\errors;

use Exception;

class KlarnaException extends Exception {

    public $message;
    public $code;

    public function __construct($message = "", $code = 0)
    {
        parent::__construct($message, $code, null);
    }

}
