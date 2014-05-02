<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;
use \MongoCollection;

class Mongo implements Store
{
    private $events;

    public function __construct(MongoCollection $events)
    {
        $this->events = $events;
    }

    public function add(Event $event)
    {
        $this->events->insert($event);
    }

    public function byProvider($class, $id)
    {
        return $this->events->find([
            'class' => $class,
            'id' => $id,
        ]);
    }
}
