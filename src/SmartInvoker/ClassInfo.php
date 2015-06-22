<?php

namespace SmartInvoker;


class ClassInfo {

    /**
     * @var MethodInfo[]
     */
    public $methods = [];
    public $params  = [];
    public $class   = "";
    public $desc    = "";

    public static function parse($class) {
        $info = new static;
        $info->class = $class;
        $ref = new \ReflectionClass($class);

        foreach($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $me) {
            if($me->name{0} == "_" || $me->isStatic()) {
                continue;
            }
            $info->methods[$me->name]  = MethodInfo::import($me);
        }

        return $info;
    }

    /**
     * @param $method
     * @return MethodInfo|bool
     */
    public function getMethod($method) {
        return isset($this->methods[$method]) ? $this->methods[$method] : false;
    }
} 