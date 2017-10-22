<?php

namespace SmartInvoker\Error;


use Throwable;

class MethodNotFoundException extends \BadMethodCallException {

    /** @var string */
    public $calledMethod;

    public function __construct($message = "", $code = 0, Throwable $previous = null, $calledMethod = null) {
        parent::__construct($message, $code, $previous);
        $this->calledMethod = $calledMethod;
    }
}