<?php

require_once __DIR__.'/../vendor/autoload.php';


class Example {

	/**
	 * @param float $leg1 (unsigned)
	 * @param float $leg2 (unsigned)
	 * @param int $round (1..6)
	 * @return float
	 */
	function hypotenuse($leg1, $leg2, $round = 2) {
		var_dump("=======", $leg1, $leg2, $round);
		return round(sqrt($leg1*$leg1 + $leg2*$leg2), $round);
	}
}

var_dump(SmartInvoker::call([new Example(), 'hypotenuse'], ['leg2' => 3, 'leg1' => 4]));