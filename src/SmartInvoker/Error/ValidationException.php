<?php

namespace SmartInvoker\Error;


use SmartInvoker\ArgumentInfo;

class ValidationException extends InvalidArgumentException {
	public $validator;

	/**
	 * @param ArgumentInfo $parameter
	 * @param string $validator
	 * @param \Exception $prev
	 */
	public function __construct(ArgumentInfo $parameter, $validator, \Exception $prev = null) {
		$this->validator = $validator;
		if($prev) {
			parent::__construct("Error occurred while validation of the parameter '{$parameter->name}': {$prev->getMessage()}", $parameter, $prev);
		} else {
			parent::__construct("Argument '{$parameter->name}' has invalid value. Require " . $parameter->verify[$validator]['original'], $parameter, $prev);
		}
	}
}