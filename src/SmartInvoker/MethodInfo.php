<?php

namespace SmartInvoker;


class MethodInfo {

    public $class;
    public $method;
    public $name;
    public $desc;
    /**
     * @var ParameterInfo[]
     */
    public $args = [];
    public $files = false;
    public $return;
    public $options = [];

	/**
	 * Scan method
	 * @param mixed $class class name or object
	 * @param string $method
	 * @return MethodInfo
	 * @throws MethodNotFoundException
	 */
    public static function scan($class, $method) {
        try {
            $me = new \ReflectionMethod($class, $method);
        } catch(\Exception $e) {
            throw new MethodNotFoundException("Reflection($class::$method) failed", 0, $e);
        }
        return MethodInfo::import($me);
    }

	/**
	 * Import method from reflection
	 * @param \ReflectionMethod $method
	 * @return static
	 */
    public static function import(\ReflectionMethod $method) {
        $info = new static;
        $info->method = $method->name;
        $doc = $method->getDocComment();
        $doc_params = array();

        if($doc) {
            $doc = preg_replace('/^\s*(\*\s*)+/mS', '', trim($doc, "*/ \t\n\r"));
            if(strpos($doc, "@") !== false) {
                $doc = explode("@", $doc, 2);
                if($doc[0] = trim($doc[0])) {
                    $info->desc = $doc[0];
                }
                if($doc[1]) {
                    foreach(preg_split('/\r?\n@/mS', $doc[1]) as $param) {
                        $param = preg_split('/\s+/', $param, 2);
                        if(!isset($param[1])) {
                            $param[1] = "";
                        }
                        switch(strtolower($param[0])) {
                            case 'description':
                                if(empty($info->desc)) {
                                    $info->desc = $param[1];
                                }
                                break;
                            case 'param':
                                if(preg_match('/^(.*?)\s*\$(\w+)\s*(?:\(([^\)]+)\))?/mS', $param[1], $matches)) {
                                    $doc_params[ $matches[2] ] = array(
                                        "type" => $matches[1],
                                        "desc" => trim(substr($param[1], strlen($matches[0]))),
                                        "verify" => isset($matches[3]) ? Verify::parse($matches[3], $matches[1]) : array()
                                    );
                                }
                                break;
                            case 'return':
                                if(preg_match('/^([^\s]+)\s*(?:\(([^\)]+)\))?\s*(.*?)$/m', $param[1], $matches)) {
                                    $info->return["type"] = $matches[1];
                                    $info->return["render"] = $matches[2];
                                    $info->return["desc"] = $matches[3];
                                }
                                break;
                            default:
                                if(isset($info->options[ $param[0] ])) {
                                    $info->options[ $param[0] ][] = $param[1];
                                } else {
                                    $info->options[ $param[0] ] = [ $param[1] ];
                                }
                        }
                    }
                }
            } else {
                $info->desc = $doc;
            }

        }
        foreach($method->getParameters() as $param) {
            $arg = ParameterInfo::import($param, $doc_params);
            if($arg->type == 'file') {
                $info->files = true;
            }
            $info->args[$arg->name] = $arg;
        }
        return $info;
    }

    /**
     * Invoke method
     * @param array $params
     * @param object $object
     * @return mixed
     */
    public function invoke(array $params = [], $object = null) {
        $args = [];
	    $verify = new Verify($object ?: $this->class);
        foreach ($this->args as $name => $arg) {
            if(isset($params[ $name ])) {
                $param = $params[ $name ];
                $args[] = $arg->filter($param, $verify);
                unset($params[ $name ]);
            } elseif(isset($params[ $arg->position ])) {
                $param = $params[ $arg->position ];
                $args[] = $arg->filter($param, $verify);
                unset($params[ $arg->position ]);
            } elseif($arg->optional) {
                $args[] = $arg->default;
                continue;
            } else {
                throw new NotEnoughArgumentException("required parameter '$name'");
            }
        }

        return call_user_func_array([$object ?: $this->class, $this->method], $args);
    }

    public function hasOption($option) {
        return !empty($this->options[$option]);
    }

    public function getOption($option, $index = 0) {
        return !empty($this->options[$option][$index]) ? $this->options[$option][$index] : null;
    }

    public function getOptions($option) {
        return isset($this->options[$option]) ? $this->options[$option] : [];
    }
} 