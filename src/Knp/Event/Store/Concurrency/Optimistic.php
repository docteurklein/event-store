<?php

namespace Knp\Event\Store\Concurrency;

use Knp\Event\Store;
use Knp\Event\Event;
use Knp\Event\Emitter\HasIdentity;
use Knp\Event\Reflection;
use Knp\Event\Exception\Concurrency;
use Knp\Event\Store\Concurrency\Optimistic\VersionTransporter;

class Optimistic implements Store\IsVersioned
{
    private $store;
    private $versionTransporter;

    public function __construct(Store\IsVersioned $store, VersionTransporter $versionTransporter)
    {
        $this->store = $store;
        $this->versionTransporter = $versionTransporter;
    }

    public function addSet(Event\Set $events)
    {
        $emitter = $events->getEmitter();
        $this->assertSameVersion($emitter);

        $this->store->addSet($events);
    }

    public function findBy($class, $id)
    {
        $emitter = $this->store->findBy($class, $id);

        return $emitter;
    }

    public function getCurrentVersion($class, $id)
    {
        return $this->store->getCurrentVersion($class, $id);
    }

    private function assertSameVersion(HasIdentity $emitter)
    {
        $class = (new Reflection($emitter))->resolveClass($emitter);
        $id = (string)$emitter->getId();

        $currentVersion  = max(1, $this->store->getCurrentVersion($class, $id));
        $expectedVersion = $this->versionTransporter->getExpectedVersion($class, $id);
        if ($currentVersion !== $expectedVersion) {
            throw new Concurrency\Optimistic\Conflict(sprintf('%s#%s is at version %d, but expected %d.', $class, $emitter->getId(), $currentVersion, $expectedVersion));
        }
    }
}
