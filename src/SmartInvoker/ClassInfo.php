<?php

namespace SmartInvoker;


class ClassInfo {
	const FLAG_OBJECT = 1;
	const FLAG_STATIC = 2;
	const FLAG_BOTH   = 3;

    /**
     * @var MethodInfo[]
     */
    public $methods = array();
	/**
	 * @var array
	 */
    public $options = array();
	/**
	 * @var string
	 */
    public $class;
    public $desc    = "";

	/**
	 * @param string $class_name
	 * @param int $load load static or object methods or both
	 * @param string $pattern method name mask (glob syntax)
	 */
	public function __construct($class_name, $load = self::FLAG_BOTH, $pattern = "*") {
		$this->class = $class_name;
		$ref = new \ReflectionClass($this->class);

		if($load) {
			foreach($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $me) {
				if($load === self::FLAG_OBJECT) {
					if($me->isStatic()) {
						continue;
					}
				} elseif($load === self::FLAG_STATIC) {
					if(!$me->isStatic()) {
						continue;
					}
				}
				if($pattern !== "*" && !fnmatch($pattern, $me->name)) {
					continue;
				}
				$this->methods[$me->name]  = MethodInfo::import($me);
			}
		}
	}

    /**
     * Get method info from class
     * @param string $method
     * @return MethodInfo|bool
     */
    public function getMethod($method) {
	    if(isset($this->methods[$method])) {
		    return $this->methods[$method];
	    } elseif(method_exists($this->class, $method)) {
		    return $this->methods[$method]  = MethodInfo::scan($this->class, $method);
	    } else {
		    return false;
	    }
    }
} 