<?php

namespace Knp\Event\Serializer;

use Knp\Event\event;
use Knp\Event\Serializer;
use JMS\Serializer\Serializer as JmsSerializer;

final class Jms implements Serializer
{
    private $serializer;
    private $format;

    public function __construct(JmsSerializer $serializer, $format = 'array')
    {
        $this->serializer = $serializer;
        $this->format = $format;
    }

    public function serialize(Event $event)
    {
        return $this->serializer->serialize($event, $this->format);
    }

    public function unserialize($event, $class = 'Knp\Event\Event\Generic')
    {
        return $this->serializer->deserialize($event, $class, $this->format);
    }
}
