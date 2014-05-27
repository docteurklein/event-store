<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;
use \MongoDB;
use \MongoCollection;
use \MongoBinData;
use Knp\Event\Serializer;

class Mongo implements Store
{
    private $events;
    private $serializer;

    public function __construct(MongoDB $events, Serializer $serializer)
    {
        $this->events = $events;
        $this->serializer = $serializer;
    }

    public function add(Event $event)
    {
        $this->events->selectCollection($event->getProviderClass())->insert([
            'id' => (string)$event->getProviderId(),
            'event' => new MongoBinData($this->serializer->serialize($event)),
        ]);
    }

    public function byProvider($class, $id)
    {
        $documents = $this->events->selectCollection($class)->find([
            'id' => $id,
        ]);

        $events = [];
        foreach ($documents as $document) {
            $events[] = $this->serializer->unserialize((string) $document['event']);
        }

        return new \ArrayIterator($events);
    }
}
