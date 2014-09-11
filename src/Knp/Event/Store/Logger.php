<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;
use Knp\Event\Emitter\HasIdentity;

final class Logger implements Store, Store\IsVersioned
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
        $count = $this->getCurrentVersion($class, $id);
        var_dump(sprintf('found %s events for %s %s', $count, $class, $id));

        return $events;
    }

    public function getCurrentVersion($class, $id)
    {
        return $this->store->getCurrentVersion($class, $id);
    }
}
