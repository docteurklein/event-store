<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;
use Knp\Event\Exception\Store\NoResult;

final class InMemory implements Store
{
    private $events = [];

    public function addSet(Event\Set $events)
    {
        if (isset($this->events[get_class($events->getEmitter())][(string)$events->getEmitter()->getId()])) {
            return $this->events[get_class($events->getEmitter())][(string)$events->getEmitter()->getId()] = array_merge(
                $this->events[get_class($events->getEmitter())][(string)$events->getEmitter()->getId()],
                $events->all()
            );
        }

        $this->events[get_class($events->getEmitter())][(string)$events->getEmitter()->getId()] = $events->all();
    }

    public function findBy($class, $id)
    {
        if (empty($this->events[$class][$id])) {
            throw new NoResult;
        }

        return $this->iterate($class, $id);
    }

    private function iterate($class, $id)
    {
        foreach ($this->events[$class][$id] as $event) {
            yield $event;
        }
    }
}
