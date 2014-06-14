<?php

namespace Knp\Event\Serializer;

use Knp\Event\Serializer;
use Knp\Event\Event;

class AnyCallable implements Serializer
{
    private $serializer;
    private $unserializer;

    public function __construct(callable $serializer, callable $unserializer)
    {
        $this->serializer = $serializer;
        $this->unserializer = $unserializer;
    }

    public function serialize(Event $event)
    {
        return call_user_func($this->serializer, $event);
    }

    public function unserialize($event, $class = 'Knp\Event\Event\Generic')
    {
        return call_user_func($this->unserializer, $event);
    }
}
