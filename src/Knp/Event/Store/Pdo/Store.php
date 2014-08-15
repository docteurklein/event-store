<?php

namespace Knp\Event\Store;

use Knp\Event\Store as Base;
use Knp\Event\Event;
use \PDO;
use Knp\Event\Serializer;
use Knp\Event\Exception\Store\NoResult;

final class Store implements Base
{
    private $pdo;
    private $serializer;

    public function __construct(\PDO $pdo, Serializer $serializer)
    {
        $this->pdo = $pdo;
        $this->serializer = $serializer;
    }

    public function add(Event $event)
    {
        $statement = $this->pdo->prepare('INSERT INTO event
            (  event_class,  name,  emitter_class,  emitter_id,  attributes ) VALUES
            ( :event_class, :name, :emitter_class, :emitter_id, :attributes )
        ;');
        $statement->bindValue('event_class', get_class($event));
        $statement->bindValue('name', $event->getName());
        $statement->bindValue('emitter_class', $event->getEmitterClass());
        $statement->bindValue('emitter_id', $event->getEmitterId());
        $statement->bindValue('attributes', json_encode($this->serializer->serialize($event)));
        $statement->execute();
    }

    public function findBy($class, $id)
    {
        $statement = $this->pdo->prepare('SELECT event_class, name, emitter_class, emitter_id, attributes
            FROM event
            WHERE emitter_class = :class AND emitter_id = :id
        ;');
        $statement->bindValue('class', $class);
        $statement->bindValue('id', $id);
        $statement->execute();

        $hasFetched = false; // TODO argghh!
        while( false !== $event = $statement->fetch(PDO::FETCH_CLASS | PDO::FETCH_CLASSTYPE | PDO::FETCH_PROPS_LATE)) {
            die(var_dump($event));
            $hasFetched = true;
            yield $event;
        }

        if (!$hasFetched) {
            throw new NoResult;
        }
    }
}

