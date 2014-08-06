<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;
use Knp\Event\Exception\Store\NoResult;

class InMemory implements Store
{
    private $events = [];

    public function add(Event $event)
    {
        $this->events[] = $event;
    }

    public function byProvider($class, $id)
    {
        if (empty($this->events)) {
            throw new NoResult;
        }
        foreach ($this->events as $event) {
            if ($event->getProviderClass() === $class && $event->getProviderId() === $id) {
                yield $event;
            }
        }
    }

}
