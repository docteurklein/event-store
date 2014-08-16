<?php

namespace Knp\Event;

class Reflection
{
    public function resolveClass($object)
    {
        return get_class($object);
    }
}
