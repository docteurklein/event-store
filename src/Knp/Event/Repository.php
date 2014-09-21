<?php

namespace Knp\Event;

use Knp\Event\Exception\Store\NoResult;
use PhpOption;

final class Repository
{
    private $store;
    private $player;

    public function __construct(Store $store, Player $player)
    {
        $this->store = $store;
        $this->player = $player;
    }

    public function save(Emitter $object)
    {
        $events = $object->popEvents();
        $this->store->addSet($events);
    }

    public function find($class, $id)
    {
        try {
            $events = $this->store->findBy($class, $id);
            return new PhpOption\Some($this->player->replay($events, $class));
        } catch (NoResult $e) {
            return PhpOption\None::create();
        }
    }
}
