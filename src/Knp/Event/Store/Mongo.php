<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;
use \MongoDB;
use \MongoCollection;

class Mongo implements Store
{
    private $events;
    private $transformer;

    public function __construct(MongoDB $events)
    {
        $this->events = $events;
    }

    public function add(Event $event)
    {
        $this->events->selectCollection($event->getProviderClass())->insert(['id' => (string)$event->getProviderId(), 'event' => serialize($event)]);
    }

    public function byProvider($class, $id)
    {
        $documents = $this->events->selectCollection($class)->find([
            'id' => $id,
        ]);

        $events = [];
        foreach ($documents as $document) {
            $events[] = unserialize($document['event']);
        }

        return new \ArrayIterator($events);
    }
}
