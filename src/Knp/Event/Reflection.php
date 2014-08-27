<?php

namespace Knp\Event;

use ReflectionObject;
use ReflectionClass;
use ReflectionProperty;

class Reflection
{
    private $subject;
    private $reflect;

    public function __construct($subject)
    {
        $this->subject = $subject;
        $this->reflect = is_object($subject) ? new ReflectionObject($subject) : new ReflectionClass($subject);
    }

    public function resolveClass($object)
    {
        return get_class($object);
    }

    public function newInstance()
    {
        return $this->reflect->newInstanceArgs(func_get_args());
    }

    public function setPropertyValue($name, $value)
    {
        $property = $this->reflect->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($this->subject, $value);
    }
}
