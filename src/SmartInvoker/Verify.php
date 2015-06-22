<?php

namespace SmartInvoker;

/**
 * Data verification
 * (count 7, value >7, value <=8, count 1..4, file, date %Y-%m-%d, keyword)
 * @package Viron
 */
class Verify {
    public $context;

    public function __construct($context) {
        $this->context = $context;
    }

    public function __call($name, $params) {
        if(method_exists($this->context, $name.'Validator')) {
            return call_user_func_array([$this->context, $name.'Validator'], $params);
        }
        return true;
    }

    /**
     * @param $value
     * @return bool
     */
    public function unsigned($value) {
        return $value >= 0;
    }

    /**
     * @param mixed $date
     * @param $format
     * @return bool
     */
    public function date($date, $format) {
        if(is_string($format)) {
            return strptime($date, $format) !== false;
        } else {
            return strtotime($date) !== false;
        }
    }

	/**
	 * Dummy validator, just for marks
	 * @return bool
	 */
	public function is() {
		return true;
	}

	/**
	 * Text string should be < 255 Bytes
	 * @param string $string
	 * @return bool
	 */
	public function smalltext($string) {
		return strlen($string) < 0x100; // 255B
	}

	/**
	 * Text string should be < 64 MiB
	 * @param string $text
	 * @return bool
	 */
	public function text($text) {
		return strlen($text) < 0x10000; // 64MiB
	}

	/**
	 * @param string $text
	 * @return bool
	 */
	public function largetext($text) {
		return strlen($text) < 0x200000;  // 2MiB
	}

    /**
     * @param mixed $value
     * @return bool
     */
    public function positive($value) {
        return $value > 0;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function negative($value) {
        return $value < 0;
    }

    /**
     * @param string $value
     * @param $type
     * @return bool
     */
    public function email($value, $type) {
        if($type == "extended" && strpos($value, "<") !== false) {
            if(preg_match('/^(?:[^\n\r<])?<(.*?)>$/', $value, $matche)) {
                return filter_var($matche[1], FILTER_VALIDATE_EMAIL) !== false;
            }
        } else {
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        }
        return false;
    }

    /**
     * @param string $value
     * @return bool
     */
    public function domain($value) {
        return !!preg_match('~([a-z0-9-]*\.)+[a-z0-9]+~', $value);
    }

    /**
     * @param string $value
     * @return bool
     */
    public function url($value) {
        return filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED, FILTER_FLAG_QUERY_REQUIRED) !== false;
    }

    /**
     * @param string $value
     * @return bool
     */
    public function ip($value) {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * @param string $value
     * @return int
     */
    public function keyword($value) {
        return !!preg_match('!^[a-z0-9_-]*$!i', $value);
    }

    /**
     * @param string $value
     * @param array|string $len
     * @return bool
     */
    public function between($value, $len) {
        if(is_array($len)) {
            return $value >= $len[0] && $value <= $len[1];
        } else {
            return $value == $len;
        }
    }

    /**
     * @param string $value
     * @param array|string $len
     * @return bool
     */
    public function length($value, $len) {
        if(is_array($len)) {
            return strlen($value) >= $len[0] && strlen($value) <= $len[1];
        } else {
            return strlen($value) == $len;
        }
    }

    /**
     * @param string $value
     * @param callable $callback
     * @return mixed
     */
    public function callback($value, $callback) {
        return call_user_func($callback, $value);
    }

    /**
     * Class name
     * @param string $class
     * @return bool
     */
    public function className($class) {
        return class_exists($class);
    }

    /**
     * Must be valid file
     * @param string $path
     * @return bool
     */
    public function file($path) {
        return is_file(strval($path));
    }

    /**
     * Must be valid directory
     * @param string $path
     * @return bool
     */
    public function dir($path) {
        return is_dir(strval($path));
    }

    /**
     * Limiting the number of elements in the array
     * @param array $value
     * @param int|array $num the number of elements in the array
     * @return bool
     */
    public function count($value, $num) {
        if(is_array($num)) {
            return count($value) >= $num[0] && count($value) <= $num[1];
        } else {
            return count($value) == $num;
        }
    }

    /**
     * @param $value
     * @param string $pattern
     * @return bool
     */
    public function mask($value, $pattern) {
        return !!preg_match('~^['.$pattern.']*$~', $value);
    }

    /**
     *
     */
    public function regexp($value, $pattern) {
        return !!preg_match($pattern, $value);
    }

	/**
	 * @param $value
	 * @param $pattern
	 * @return bool
	 */
	public function like($value, $pattern) {
        $matched = sscanf($value." 0x\x7", $pattern." 0x%[\x7]");
        return $matched && !is_null(end($matched));
    }

	/**
	 * @param $value
	 * @param $variants
	 * @return bool
	 */
    public function variants($value, $variants) {
	    if(is_array($variants)) {
			return in_array($value, $variants);
	    } elseif(is_callable($variants)) {
            return in_array($value, (array)call_user_func($variants));
        } else {
            return strpos($variants." ", $value." ") !== false;
        }
    }

	/**
	 * @param mixed $value
	 * @param $callback
	 * @return bool
	 */
    public function option($value, $callback) {
        $options = call_user_func($callback, $value);
        return $options && isset($options[$value]);
    }

    /**
     * @param $validators
     * @return array
     */
    public static function parse($validators) {
        $verify = array();
        if(preg_match_all('!((.*?):?(\s+.*?)?),\s*!', $validators.',', $m)) {
            foreach($m[2] as $k => $validator) {
                $arg = trim($m[3][$k]);
                if($arg) {
                    if($validator == "variants") {
                        if(is_callable($arg)) {
                            $args = call_user_func($arg);
                        } else {
                            $args = preg_split('/\s+/', trim($arg));
                        }
                    } elseif (preg_match('!^(?<interval>(?<interval_from>\d+)\.\.(?<interval_to>\d+))|(?<range>(?<range_sign>[\>\<]\=?)\s*(?<range_value>\d+))$!', $arg, $args)) {
                        if($args['interval']) {
                            $args = array($args['interval_from'] * 1, $args['interval_to'] * 1);
                        } elseif($args['range']) {
                            switch ($args['range_sign']) {
                                case '<':
                                    $args = array(-PHP_INT_MAX, $args['range_value'] - 1);
                                    break;
                                case '<=':
                                    $args = array(-PHP_INT_MAX, $args['range_value']);
                                    break;
                                case '>':
                                    $args = array($args['range_value'] + 1, PHP_INT_MAX);
                                    break;
                                case '>=':
                                    $args = array($args['range_value'], PHP_INT_MAX);
                                    break;
                                case '=':
                                    $args = array($args['range_value'], $args['range_value']);
                                    break;
                                default:
                                    continue;
                            }
                        } else {
                            $args = $arg;
                        }
                    } else {
                        $args = $arg;
                    }
                } else {
                    $args = true;
                }
                $verify[$validator] = array(
                    "original" => $m[1][$k],
                    "args" => $args
                );
            }
        }
        return $verify;
    }
}