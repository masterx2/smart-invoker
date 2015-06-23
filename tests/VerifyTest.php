<?php

namespace SmartInvoker;


class VerifyTest extends \PHPUnit_Framework_TestCase {

	public $verify;

	public static function variants() {
		return array("v1", "v2");
	}

	public static function options() {
		return array("v1" => "version 1", "v2" => "version 2");
	}

	public function equalValidator($val, $letter) {
		return $val === $letter;
	}

	public function setUp() {
		$this->verify = new Verify($this);
	}

	public function providerValidators() {
		return array(
			array("unsigned", true, 1),
			array("unsigned", true, 0),
			array("unsigned", false, -1),

			array("date", true,  "2015-06-21"),
			array("date", true,  "20150621"),
			array("date", false, "15|06|21"),
			array("date", true,  "15|06|21", "%y|%m|%d"),

			array("is", true,  1),

			array("smalltext", true,  str_pad("", 255 ,"a")),
			array("smalltext", false,  str_pad("", 256 ,"a")),

			array("text", true,  str_pad("", 256 ,"a")),

			array("largetext", true,  str_pad("", 256 ,"a")),

			array("positive", true,  1),
			array("positive", false,  0),
			array("positive", false,  -1),

			array("negative", true,  -1),
			array("negative", false,  0),
			array("negative", false,  1),

			array("email", true,   "a.cobest@gmail.com"),
			array("email", false,  "a.cobest.gmail.com"),
			array("email", false,  "Ivan Shalganov <a.cobest@gmail.com>"),
			array("email", true,   "Ivan Shalganov <a.cobest@gmail.com>", "extended"),
			array("email", false,  "Ivan Shalganov a.cobest@gmail.com", "extended"),
			array("email", false,  "Ivan Shalganov <a.cobest@gmail.com", "extended"),
			array("email", false,  "Ivan Shalganov <a.cobest.gmail.com>"),

			array("domain", true,   "yandex.com"),
			array("domain", false,  "yandexcom"),
			array("domain", false,  "домен.рф"), // add punicode

			array("url", true,  "https://www.yandex.com/search/?text=Smart%20Invoker%20PHP"),
			array("url", true,  "https://www.yandex.com/"),
			array("url", false,  "https://домен.рф/"), // add punicode
			array("url", false,  "//www.yandex.com/search/?text=Smart%20Invoker%20PHP"),
			array("url", false,  "text=Smart%20Invoker%20PHP"),

			array("ip", true,  "127.0.0.1"),
			array("ip", true,  "::1"),
			array("ip", false,  "127.o.o.1"),

			array("keyword", true,  "bzick"),
			array("keyword", false,  "bzick/"),

			array("value", true,  2, array(1, 3)),
			array("value", false,  4, array(1, 3)),
			array("value", true,  4, 4),
			array("value", false,  4, 5),


			array("length", true,  "bzick", array(1, 6)),
			array("length", false,  "bzick", array(1, 3)),
			array("length", true,  "bzick", 5),
			array("length", false,  "bzick", 6),

			array("callback", true,  "is_string"),
			array("callback", false,  "is_string2"),

			array("className", true,  'SmartInvoker\VerifyTest'),
			array("className", false,  'SmartInvoker\VerifyTestInv'),

			array("file", true,  __FILE__),
			array("file", false,  '/unexists'),

			array("dir", true,  __DIR__),
			array("dir", false,  '/unexists'),

			array("mask", true,  'bzick', "a-z"),
			array("mask", false, 'bzick2', "a-z"),
			array("mask", true,  'bzick2', "a-z0-9"),

			array("regexp", true,   'bzick', '/^bzick$/'),
			array("regexp", false,  'bzick2', '/^bzick$/'),
			array("regexp", true,   'bzick2', '/^bzick\d+$/'),
			array("regexp", false,  'bzick22', '/^bzick\d$/'),

			array("like", true,  'bzick', "bz*ck"),
			array("like", false, 'bzick2', "bz*ck"),
			array("like", true,  'bzick2', "bz*ck[0-9]"),

			array("variants", true,  'v1', array("v1", "v2")),
			array("variants", false,  'v3', array("v1", "v2")),
			array("variants", true,  'v1', "v1 v2"),
			array("variants", false,  'v3', "v1 v2"),
			array("variants", true,  'v1', 'SmartInvoker\VerifyTest::variants'),
			array("variants", false,  'v3', 'SmartInvoker\VerifyTest::variants'),

			array("option", true,  'v1', 'SmartInvoker\VerifyTest::options'),
			array("option", false,  'v3', 'SmartInvoker\VerifyTest::options'),

			array("equal", true,  'v3', 'v3'),
			array("equal", false,  'v3', 'v4'),

			array("fake", true,  'v3'),

		);
	}

	/**
	 * @dataProvider providerValidators
	 * @param $method
	 * @param $value
	 * @param $param
	 * @param $result
	 */
	public function testValidators($method, $result, $value, $param = true) {
		$this->assertEquals($result, $this->verify->$method($value, $param));
	}

}