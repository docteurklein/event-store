<?php

namespace Knp\Event\Repository;

use Knp\Event\Store;
use Knp\Event\Repository;
use Knp\Event\Player;
use Knp\Event\Player\ReflectionBased;
use Knp\Event\Dispatcher;

class Factory
{
    private $store;
    private $dispatcher;
    private $map;

    public function __construct(Store $store = null, Dispatcher $dispatcher = null, array $map = [])
    {
        $this->store = $store ?: new Store\InMemory;
        $this->dispatcher = $dispatcher ?: new Dispatcher;
        $this->map = $map;
    }

    public function create()
    {
        return new Repository(
            new Store\Dispatcher($this->store, $this->dispatcher),
            $this->getPlayer()
        );
    }

    private function getPlayer()
    {
        if (empty($this->map)) {
            return new Player\ReflectionBased;
        }

        return new Player\Aggregate($this->map, new Player\ReflectionBased);
    }
}
