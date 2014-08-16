<?php

namespace funk\Knp\Event\Serializer\Jms\Handler\Event;

use Knp\Event\Event;

class Generic implements \Funk\Spec
{
    function it_restores_an_identical_version_of_a_serialized_object()
    {
        $serializer = require 'example/_serializer.php';
        require 'example/_model.php';
        $emitter = new \Knp\Event\Example\Shop\Product(\Rhumsaa\Uuid\Uuid::uuid4(), 'shoe');
        $event = new Event\Generic('yes it did', [
            'reason' => 'because!',
        ]);
        $event->setEmitter($emitter);

        if ($serializer->unserialize($serializer->serialize($event)) == $event) {
            throw new \Exception;
        }
    }
}
