<?php

namespace SmartInvoker;


class TypeCastingException extends InvalidArgumentException {
	public $to_type;
	public $from_type;
}