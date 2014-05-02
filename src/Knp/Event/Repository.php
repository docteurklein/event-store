<?php

namespace Knp\Event;

use Knp\Event\Store;
use Knp\Event\Player;

class Repository
{
    private $store;
    private $player;

    public function __construct(Store $store, Player $player)
    {
        $this->store = $store;
        $this->player = $player;
    }

    public function save(Provider $object)
    {
        $events = $object->popEvents();
        foreach ($events as $event) {
            $event->setProvider($object);
            $this->store->add($event);
        }
    }

    public function find($class, $id)
    {
        $events = iterator_to_array($this->store->byProvider($class, $id));
        if (empty($events)) {
            return;
        }

        return $this->player->replay($events, $class);
    }
}
