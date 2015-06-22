<?php


class SmartInvoker {

	/**
	 * @param callable $cb
	 * @param array $args
	 * @param callable $creator
	 * @return mixed
	 */
	public static function call($cb, array $args = [], $creator = null) {
		if(is_array($cb)) {
			return \SmartInvoker\MethodInfo::scan($cb[0], $cb[1])->invoke($args, is_string($cb[0]) ? null : $cb[0], $creator);
		} elseif(is_object($cb)) {
			// todo
		} else {
			if(strpos($cb, '::')) {
				$cb = explode('::', $cb);
				return \SmartInvoker\MethodInfo::scan($cb[0], $cb[1])->invoke($args, null, $creator);
			} else {
				// todo
			}
		}
	}

	public static function factory($class_name, array $args = [], $di = null) {
		// Todo
	}
}