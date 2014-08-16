<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;
use \MongoDB;
use \MongoCollection;
use \MongoBinData;
use Knp\Event\Serializer;
use Knp\Event\Exception\Store\NoResult;
use Knp\Event\Reflection;

final class Mongo implements Store
{
    private $events;
    private $serializer;
    private $reflection;

    public function __construct(MongoDB $events, Serializer $serializer, Reflection $reflection = null)
    {
        $this->events = $events;
        $this->serializer = $serializer;
        $this->reflection = $reflection ?: new Reflection;
    }

    public function addSet(Event\Set $events)
    {
        $this->events->selectCollection($this->reflection->resolveClass($events->getEmitter()))->batchInsert(
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
