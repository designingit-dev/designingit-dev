<?php

namespace keiser\affirm\errors;

use Exception;

class AffirmException extends Exception {

    public $message;
    public $code;
    public $field;
    public $type;

    public function __construct($message = "", $code = 0, $field = null, $type = null)
    {
        parent::__construct($message, $code, null);
        $this->message = $message;
        $this->code = $code;
        $this->field = $field;
        $this->type = $type;
    }

}
