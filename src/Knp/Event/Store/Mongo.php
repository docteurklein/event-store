<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;
use \MongoDB;
use \MongoCollection;
use \MongoBinData;
use Knp\Event\Serializer;
use Knp\Event\Exception\Store\NoResult;

final class Mongo implements Store
{
    private $events;
    private $serializer;

    public function __construct(MongoDB $events, Serializer $serializer)
    {
        $this->events = $events;
        $this->serializer = $serializer;
    }

    public function addSet(Event\Set $events)
    {
        $this->events->selectCollection(get_class($events->getEmitter()))->batchInsert(
            array_map(function($event) { return $this->serializer->serialize($event); }, $events->all())
        );
    }

    public function findBy($class, $id)
    {
        $events = $this->events->selectCollection($class)->find([
            'emitter_id' => (string)$id,
        ]);
        if (0 === $events->count()) {
            throw new NoResult;
        }

        return $this->iterate($events);
    }

    private function iterate(\MongoCursor $events)
    {
        foreach ($events as $event) {
            yield $this->serializer->unserialize($event);
        }
    }
}
