<?php

require_once __DIR__.'/../vendor/autoload.php';


class Example {

	/**
	 * @param float $leg1 (unsigned)
	 * @param float $leg2 (unsigned)
	 * @param int $round (1..6)
	 * @return float
	 */
	public function hypotenuseCalc($leg1, $leg2, $round = 2) {
		var_dump("=======", $leg1, $leg2, $round);
		return round(sqrt($leg1*$leg1 + $leg2*$leg2), $round);
	}

	public function test() {

	}
}

$class = new \SmartInvoker\ClassInfo('Example', \SmartInvoker\ClassInfo::FLAG_OBJECT, "*Calc");

var_dump(json_encode($class, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
//var_dump(SmartInvoker::call([new Example(), 'hypotenuse'], ['leg2' => 3, 'leg1' => 4]));