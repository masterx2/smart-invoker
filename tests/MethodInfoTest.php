<?php

namespace SmartInvoker;


class MethodInfoTest extends \PHPUnit_Framework_TestCase {


	public function testParse() {
		$method = MethodInfo::scan('SmartInvokerTest\Math', 'hypotenuse');
		$this->assertSame('SmartInvokerTest\Math', $method->class);
		$this->assertSame('hypotenuse', $method->method);
		$this->assertSame('Calculate hypotenuse', $method->desc);
		$this->assertTrue($method->hasOption('link'));
		$this->assertSame('https://en.wikipedia.org/wiki/Hypotenuse', $method->getOption('link'));
		$this->assertSame(array('https://en.wikipedia.org/wiki/Hypotenuse'), $method->getOptions('link'));

		$this->assertCount(3, $method->args);
		$this->assertSame(array('leg1', 'leg2', 'round'), array_keys($method->args));
		$this->assertInstanceOf('SmartInvoker\ArgumentInfo', $method->args['leg1']);

		// leg1
		$leg1 = $method->args['leg1'];
		$this->assertEquals($method->args['leg1'], $method->getArgument('leg1'));
		$this->assertFalse($leg1->optional);
		$this->assertFalse($leg1->multiple);
		$this->assertSame('float', $leg1->type);
		$this->assertNull($leg1->default);
		$this->assertSame('first cathetus of triangle', $leg1->desc);
		$this->assertSame(0, $leg1->position);
		$this->assertEquals(array('unsigned' => array('original' => 'unsigned', 'args' => true)), $leg1->verify);

		// round
		$round = $method->args['round'];
		$this->assertEquals($method->args['round'], $method->getArgument('round'));
		$this->assertTrue($round->optional);
		$this->assertFalse($round->multiple);
		$this->assertSame('int', $round->type);
		$this->assertSame(2, $round->default);
		$this->assertSame('returns the rounded value of hypotenuse to specified precision', $round->desc);
		$this->assertSame(2, $round->position);
		$this->assertEquals(array('value' => array('original' => 'value 0..6', 'args' => array(0, 6))), $round->verify);
	}


	public function providerInvoke() {
		return array(
			array(3, 4, 2, 5),
			array(3.1, 4.1, 2, 5.14),
			array(3.1, 4.1, 0, 5),
		);
	}

	/**
	 * @dataProvider providerInvoke
	 * @param float $leg1
	 * @param float $leg2
	 * @param int $round
	 * @param $result
	 * @throws Error\NotEnoughArgumentException
	 */
	public function testInvoke($leg1, $leg2, $round, $result) {
		$method = MethodInfo::scan('SmartInvokerTest\Math', 'hypotenuse');
		$this->assertEquals($result, $method->invoke(array($leg1, $leg2, $round)));
		$this->assertEquals($result, $method->invoke(array("leg2" => $leg2, "leg1" => $leg1, "round" => $round)));
	}

	public function providerInvokeMulti() {
		return array(
			array(array(2, 4, 6), 4),
		);
	}

	/**
	 * @dataProvider providerInvokeMulti
	 * @param $nums
	 * @param $result
	 * @throws Error\NotEnoughArgumentException
	 */
	public function testInvokeMulti($nums, $result) {
		$method = MethodInfo::scan('SmartInvokerTest\Math', 'avg');
		$this->assertEquals($result, $method->invoke(array("nums" => $nums)));
	}
}