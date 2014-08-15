<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;
use Knp\Event\Exception\Store\NoResult;

final class InMemory implements Store
{
    private $events = [];

    public function add(Event $event)
    {
        $this->events[] = $event;
    }

    public function byEmitter($class, $id)
    {
        if (empty($this->events)) {
            throw new NoResult;
        }
        foreach ($this->events as $event) {
            if ($event->getEmitterClass() === $class && $event->getEmitterId() === $id) {
                yield $event;
            }
        }
    }

}
