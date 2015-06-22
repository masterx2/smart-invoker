<?php

namespace SmartInvoker;


class ParameterInfo {

    private static $_aliases = [
        "integer" => "int",
        "str" => "string",
        "double" => "float"
    ];

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

    public $method;
    public $name;
    public $desc;
    public $verify;
    public $multiple;
    public $type;
    public $class;
    public $optional;
    public $default;
    public $position;

    public static function import(\ReflectionParameter $param, array $doc_params = []) {
        $arg           = new static;
        $arg->method   = $param->getDeclaringFunction()->name;
        $arg->name     = $param->name;
        $arg->desc     = isset($doc_params[ $param->name ]) ? $doc_params[ $param->name ]["desc"] : "";
        $arg->verify   = isset($doc_params[ $param->name ]["verify"]) ? $doc_params[ $param->name ]["verify"] : [];
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
     * Convert value to required type
     * @param mixed $value
     * @param object $verify
     * @return mixed
     */
    public function filter($value, $verify = null) {
        $type = gettype($value);

        if($this->multiple && !is_array($value)) {
            throw new \InvalidArgumentException("parameter '{$this->name}' must be array");
        }
        if($this->type) {
            if($this->type === $type) { // type may be an array
                if($type == "object" || $this->type == "file") {
                    if($this->multiple) {
                        array_walk_recursive($param, function (&$value) {
                            if(!is_a($value, $this->class)) {
                                throw new \InvalidArgumentException("parameter '{$this->name}' must be instance of ".$this->class);
                            }
                        });
                    } else {
                        if(!is_a($value, $this->class)) {
                            throw new \InvalidArgumentException("parameter '{$this->name}' must be instance of ".$this->class);
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
                        if(call_user_func([$verify, $method], $item, $v['args']) === false) {
                            throw new \InvalidArgumentException("parameter '{$this->name}' have invalid value in '$k' element. Require ".$v['original']);
                        }
                    }
                } else {

                    if(call_user_func([$verify, $method], $value, $v['args']) === false) {
                        throw new \InvalidArgumentException("parameter '{$this->name}' have invalid value. Require ".$v['original']);
                    }
                }
            }
        }
        return $value;
    }

    /**
     * @param mixed $value
     * @return mixed
     * @throws InvalidArgumentException
     */
    private function _toType($value) {
        switch($this->type) {
            case "callable":
                if(!is_callable($value)) {
                    throw new InvalidArgumentException("argument '{$this->name}' must be callable");
                }
                break;
            case "file":
                break;
            case "int":
            case "float":
                if(!is_numeric($value)) {
                    throw new InvalidArgumentException("argument '{$this->name}' must be numeric ({$this->type})");
                } else {
                    settype($value, $this->type);
                }
                break;
            case "object":
                if(is_a($value, $this->class)) {
                    break;
                } else {
                    // creating object
                }
                throw new InvalidArgumentException("argument '{$this->name}' must be instance of ".json_encode($this->class));
            default:
                settype($value, $this->type);
        }
        return $value;
    }
} 