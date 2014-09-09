<?php

namespace Knp\Event\Repository;

use Knp\Event\Store;
use Knp\Event\Repository;
use Knp\Event\Player;
use Knp\Event\Dispatcher;

final class Factory
{
    private $store;
    private $dispatcher;
    private $player;

    public function __construct(Store $store = null, Dispatcher $dispatcher = null, Player $player = null)
    {
        $this->store = $store ?: new Store\InMemory;
        $this->dispatcher = $dispatcher ?: new Dispatcher;
        $this->player = $player ?: new Player\ReflectionBased;
    }

    public function create()
    {
        return new Repository(
            new Store\Dispatcher($this->store, $this->dispatcher),
            $this->player
        );
    }
}
