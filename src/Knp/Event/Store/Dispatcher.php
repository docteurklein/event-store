<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;
use Doctrine\Common\EventManager;

final class Dispatcher implements Store
{
    private $store;
    private $dispatcher;

    public function __construct(Store $store, EventManager $dispatcher)
    {
        $this->store = $store;
        $this->dispatcher = $dispatcher;
    }

    public function addSet(Event\Set $events)
    {
        $this->store->addSet($events);

        foreach ($events as $event) { // TODO is this reliable ?
            $this->dispatcher->dispatchEvent($event->getName(), $event);
        }
    }

    public function findBy($class, $id)
    {
        return $this->store->findBy($class, $id);
    }

}
