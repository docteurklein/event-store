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

    public function add(Event $event)
    {
        $this->store->add($event);
        $this->dispatcher->dispatchEvent($event->getName(), $event);
    }

    public function findBy($class, $id)
    {
        return $this->store->findBy($class, $id);
    }

}
