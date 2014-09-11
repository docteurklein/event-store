<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;
use Knp\Event\Exception\Store\NoResult;
use Knp\Event\Reflection;
use Knp\Event\Emitter\HasIdentity;

final class InMemory implements Store, Store\IsVersioned
{
    private $reflection;
    private $events = [];

    public function __construct(Reflection $reflection = null)
    {
        $this->reflection = $reflection ?: new Reflection(self::class);
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

    public function getCurrentVersion($class, $id)
    {
        if (empty($this->events[$class][$id])) {
            return 0;
        }

        return count($this->events[$class][$id]);
    }
}
