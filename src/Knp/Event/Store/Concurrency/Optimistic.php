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

        $this->assertSameVersion($emitter, $class, $id);

        $this->store->addSet($events);
        $this->resetExpectedVersion($class, $id);
    }

    public function findBy($class, $id)
    {
        $currentVersion = max(1, $this->store->getCurrentVersion($class, $id));
        $emitter = $this->store->findBy($class, $id);
        $this->resetExpectedVersion($class, $id);

        return $emitter;
    }

    public function getCurrentVersion($class, $id)
    {
        return $this->store->getCurrentVersion($class, $id);
    }

    private function assertSameVersion(HasIdentity $emitter, $class, $id)
    {
        $currentVersion  = max(1, $this->store->getCurrentVersion($class, $id));
        $expectedVersions = $this->getExpectedVersions($class, $id);
        foreach ($expectedVersions as $expectedVersion) {
            if ($currentVersion !== $expectedVersion) {
                throw new Concurrency\Optimistic\Conflict(sprintf('%s#%s is at version %d, but expected %d.', $class, $emitter->getId(), $currentVersion, $expectedVersion));
            }
        }
    }

    private function getExpectedVersions($class, $id)
    {
        if (!isset($this->versions[$class][$id])) {
            return [1];
        }

        return $this->versions[$class][$id];
    }

    private function resetExpectedVersion($class, $id)
    {
        $this->versions[$class][(string)$id][] = $this->store->getCurrentVersion($class, $id);
    }
}
