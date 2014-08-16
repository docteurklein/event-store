<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;
use Knp\Event\Exception\Store\NoResult;
use Knp\Event\Reflection;

final class InMemory implements Store
{
    private $reflection;
    private $events = [];

    public function __construct(Reflection $reflection = null)
    {
        $this->reflection = $reflection ?: new Reflection;
    }

    public function addSet(Event\Set $events)
    {
        $class = $this->reflection->resolveClass($events->getEmitter());
        $id = (string)$events->getEmitter()->getId();

        if (isset($this->events[$class][$id])) {
            $this->events[$class][$id] = array_merge($this->events[$class][$id], $events->all());
            return;
        }

        $this->events[$class][$id] = $events->all();
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
