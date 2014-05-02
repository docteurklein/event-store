<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;

class InMemory implements Store
{
    private $events = [];

    public function add(Event $event)
    {
        $this->events[] = $event;
    }

    public function byProvider($class, $id)
    {
        foreach ($this->events as $event) {
            if ($event->getProviderClass() === $class && $event->getProviderId() === $id) {
                yield $event;
            }
        }
    }

}
