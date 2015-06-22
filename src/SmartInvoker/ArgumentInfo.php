<?php

namespace SmartInvoker;


use SmartInvoker\Error\TypeCastingException;
use SmartInvoker\Error\ValidationException;

class ArgumentInfo {

    private static $_aliases = array(
        "integer" => "int",
        "str" => "string",
        "double" => "float"
    );

    /**
     * @var array of native types with priorities
     */
    private static $_native = array(
        "int" => 9,
        "bool" => 7,
        "float" => 8,
        "string" => 10,
        "array" => 6,
        "NULL" => 1,
        "resource" => 5,
        "callable" => 10
    );

	/**
	 * Original method name
	 * @var string
	 */
    public $method;
	/**
	 * Parameter name
	 * @var string
	 */
    public $name;
	/**
	 * Parameter description
	 * @var
	 */
    public $desc;
	/**
	 * Verification list
	 * @var array[]
	 */
    public $verify;
	/**
	 * Expected multiple values
	 * @var bool
	 */
    public $multiple = false;
	/**
	 * Type of expected value (native PHP type)
	 * @var string
	 */
    public $type;
	/**
	 * Class name, if parameter expects object
	 * @var string
	 */
    public $class;
	/**
	 * Is this optional parameter?
	 * @var bool
	 */
    public $optional = false;
	/**
	 * Default value
	 * @var
	 */
    public $default;
	/**
	 * Position in argument list of method (starts with 0)
	 * @var int
	 */
    public $position;

	/**
	 * Import information from reflection
	 * @param \ReflectionParameter $param
	 * @param array $doc_params
	 * @return static
	 */
    public static function import(\ReflectionParameter $param, array $doc_params = array()) {
        $arg           = new static;
        $arg->method   = $param->getDeclaringFunction()->name;
        $arg->name     = $param->name;
        $arg->desc     = isset($doc_params[ $param->name ]) ? $doc_params[ $param->name ]["desc"] : "";
        $arg->verify   = isset($doc_params[ $param->name ]["verify"]) ? $doc_params[ $param->name ]["verify"] : array();
        $arg->optional = $param->isOptional();
        $arg->default  = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
        $arg->position = $param->getPosition();

        if($param->isArray()) {
            $arg->multiple = true;
            $arg->type = null;
        }

        if($c = $param->getClass()) {
            $arg->type = "object";
            $arg->class = $c->name;
        } elseif(isset($doc_params[ $param->name ])) {
            $_type = $doc_params[ $param->name ]["type"];
            if(strpos($_type, "|")) { // multitype mark as mixed
                $arg->type = null;
            } elseif($_type === "mixed") {
                if(strpos($_type, "[]")) {
                    $arg->multiple = true;
                }
                $arg->type = null;
            } else {
                if(strpos($_type, "[]")) {
                    $_type = rtrim($_type, '[]');
                    $arg->multiple = true;
                }

                if(isset(self::$_native[$_type])) {
                    $arg->type = $_type;
                } else {
                    $_type = ltrim($_type,'\\');
                    $arg->type = "object";
                    $arg->class = $_type;
                }
            }
        } else {
            $arg->type = null;
        }

        return $arg;
    }

	/**
	 * Convert value to required type (with validation if verify present)
	 * @param mixed $value
	 * @param object $verify
	 * @return mixed
	 * @throws TypeCastingException
	 * @throws ValidationException
	 */
    public function filter($value, $verify = null) {
        $type = gettype($value);

        if($this->multiple && !is_array($value)) {
            throw new TypeCastingException($this, gettype($value));
        }
        if($this->type) {
            if($this->type === $type) { // type may be an array
                if($type == "object") {
                    if($this->multiple) {
                        array_walk_recursive($param, function (&$value) {
                            if(!is_a($value, $this->class)) {
	                            throw new TypeCastingException($this, gettype($value));
                            }
                        });
                    } else {
                        if(!is_a($value, $this->class)) {
	                        throw new TypeCastingException($this, gettype($value));
                        }
                    }
                }
            } else {  // if invalid type - tying fix it
                if($this->multiple) {
                    array_walk_recursive($param, function (&$value) {
                        $value = $this->_toType($value);
                    });
                } else {
                    $value = $this->_toType($value);
                }
            }
        }
        if($this->verify && $verify) {
            foreach($this->verify as $method => $v) {
                if($this->multiple) {
                    foreach($value as $k => &$item) {
	                    try {
		                    if (call_user_func(array($verify, $method), $item, $v['args']) === false) {
			                    throw new ValidationException($this, $method);
		                    }
	                    } catch(\Exception $e) {
		                    throw new ValidationException($this, $method, $e);

	                    }
                    }
                } else {
	                try {
		                if (call_user_func(array($verify, $method), $value, $v['args']) === false) {
			                throw new ValidationException($this, $method);
		                }
	                } catch (\Exception $e) {
		                throw new ValidationException($this, $method, $e);
	                }
                }
            }
        }
        return $value;
    }

	/**
	 * Type casting
	 * @param mixed $value
	 * @param null $creator
	 * @return mixed
	 * @throws TypeCastingException
	 */
    private function _toType($value, $creator = null) {
        switch($this->type) {
            case "callable":
                if(!is_callable($value)) {
                    throw new TypeCastingException($this, gettype($value));
                }
                break;
            case "file":
                break;
            case "int":
            case "float":
                if(!is_numeric($value)) {
                    throw new TypeCastingException($this, gettype($value));
                } else {
                    settype($value, $this->type);
                }
                break;
            case "object":
                if(is_a($value, $this->class)) {
                    break;
                } elseif($creator) {
	                $value = call_user_func($creator, $this->class, $value);
                }
                throw new TypeCastingException($this, gettype($value));
            default:
                settype($value, $this->type);
        }
        return $value;
    }
}