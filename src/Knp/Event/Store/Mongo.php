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
        $this->events->selectCollection($event->getProviderClass())->insert(
            (array)$this->serializer->serialize($event)
        );
    }

    public function byProvider($class, $id)
    {
        $documents = $this->events->selectCollection($class)->find([
            'provider_id' => (string)$id,
        ]);

        return new CursorIterator($documents, $this->serializer);
    }
}

/**
 * TODO tmp
 * see https://jira.mongodb.org/browse/PHP-820
 * see https://jira.mongodb.org/browse/PHP-977
 **/
class CursorIterator extends \IteratorIterator
{
    public function __construct(\Traversable $t, $serializer)
    {
        parent::__construct($t);
        $this->serializer = $serializer;
    }
    public function current()
    {
        return $this->serializer->unserialize(parent::current());
    }
}
