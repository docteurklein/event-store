<?php

namespace Knp\Event\Store\Concurrency;

use Knp\Event\Store;
use Knp\Event\Event;
use Knp\Event\Emitter\HasIdentity;
use Knp\Event\Reflection;
use Knp\Event\Exception\Concurrency;

class Optimistic implements Store, Store\IsVersioned
{
    private $store;
    private $versions = [];

    public function __construct(Store\IsVersioned $store)
    {
        $this->store = $store;
    }

    public function addSet(Event\Set $events)
    {
        $emitter = $events->getEmitter();
        $class = (new Reflection($emitter))->resolveClass($emitter);
        $id = (string)$emitter->getId();

        $currentVersion   = max(1, $this->store->getCurrentVersion($class, $id));
        $expectedVersion = $this->getExpectedVersion($class, $id);
        if ($currentVersion !== $expectedVersion) {
            throw new Concurrency\Optimistic\Conflict(sprintf('%s#%s is at version %d, but expected %d.', $class, $emitter->getId(), $currentVersion, $expectedVersion));
        }

        $this->store->addSet($events);
        $this->versions[$class][(string)$id][] = $this->store->getCurrentVersion($class, $id);
    }

    public function findBy($class, $id)
    {
        $currentVersion = max(1, $this->store->getCurrentVersion($class, $id));
        $this->versions[$class][(string)$id][] = $currentVersion;

        return $this->store->findBy($class, $id);
    }

    public function getCurrentVersion($class, $id)
    {
        return $this->store->getCurrentVersion($class, $id);
    }

    private function getExpectedVersion($class, $id)
    {
        if (!isset($this->versions[$class][$id])) {
            return 1;
        }

        return max($this->versions[$class][$id]);
    }
}
