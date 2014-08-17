<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;

final class Logger implements Store
{
    private $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function addSet(Event\Set $events)
    {
        var_dump(sprintf('storing %d events', count($events->all())));
        return $this->store->addSet($events);
    }

    public function findBy($class, $id)
    {
        $events = $this->store->findBy($class, $id);
        var_dump(sprintf('found %s events for %s %s', '?', $class, $id));

        return $events;
    }
}
