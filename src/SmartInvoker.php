<?php


class SmartInvoker {
	/**
	 * @param callable $cb
	 * @param array $args
	 * @param callable $di
	 * @return mixed
	 */
	public static function call(callable $cb, array $args = [], $di = null) {
		if(is_array($cb)) {
			return \SmartInvoker\MethodInfo::scan($cb[0], $cb[1])->invoke($args, is_string($cb[0]) ? null : $cb[0], $di);
		} else {
			$cb = explode('::', $cb);
			return \SmartInvoker\MethodInfo::scan($cb[0], $cb[1])->invoke($args, null, $di);
		}
	}

	public static function factory($class_name, array $args = [], $di = null) {
		// Todo
	}
}