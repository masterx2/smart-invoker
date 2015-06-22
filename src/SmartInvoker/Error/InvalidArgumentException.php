<?php

namespace SmartInvoker\Error;


use SmartInvoker\ArgumentInfo;

class InvalidArgumentException extends \Exception {
	public $param;

	public function __construct($message, ArgumentInfo $param, $prev = null) {
		$this->param = $param;
		parent::__construct($message, 0, $prev);
	}
}