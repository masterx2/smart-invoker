<?php

namespace SmartInvoker\Error;


use SmartInvoker\ArgumentInfo;

class TypeCastingException extends InvalidArgumentException {
	/**
	 * @var string
	 */
	public $from_type;

	/**
	 * @param ArgumentInfo $parameter
	 * @param string $from_type
	 */
	public function __construct(ArgumentInfo $parameter, $from_type) {
		$this->from_type = $from_type;
		if($parameter->type == "object") {
			parent::__construct("Argument '{$parameter->name}' should be ".($parameter->multiple ? "array of objects {$parameter->class}" : "object {$parameter->class}"), $parameter);
		} else {
			parent::__construct("Argument '{$parameter->name}' should be ".($parameter->multiple ? "array of {$parameter->type}s" : "{$parameter->type}"), $parameter);
		}
	}
}