<?php

namespace SmartInvokerTest;


class Math {
	/**
	 * Calculate hypotenuse
	 * @link https://en.wikipedia.org/wiki/Hypotenuse
	 * @param float $leg1 (unsigned) first cathetus of triangle
	 * @param float $leg2 (unsigned) second cathetus of triangle
	 * @param int $round (value 0..6) returns the rounded value of hypotenuse to specified precision
	 * @return float
	 */
	public static function hypotenuse($leg1, $leg2, $round = 2) {
		var_dump(func_get_args());
		return round(sqrt($leg1*$leg1 + $leg2*$leg2), $round);
	}
}