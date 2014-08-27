<?php

namespace funk\Knp\Event\Serializer\Jms\Handler\Event;

use Knp\Event\Event;
use Knp\Event\Serializer\Jms\Builder;
use example\Shop\Model\Product;

class Generic implements \Funk\Spec
{
    private $serializer;

    function __construct()
    {
        $this->serializer = (new Builder)->build();
    }

    function it_restores_an_identical_version_of_a_serialized_object()
    {
        $emitter = new Product(\Rhumsaa\Uuid\Uuid::uuid4(), 'shoe');
        $event = new Event\Generic('yes it did', [
            'reason' => 'because!',
        ]);
        $event->setEmitter($emitter);

        if ($this->serializer->unserialize($this->serializer->serialize($event)) != $event) {
            throw new \Exception;
        }
    }
}
