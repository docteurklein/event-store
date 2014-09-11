<?php

namespace Knp\Event\Store\Pdo;

use Knp\Event;
use Knp\Event\Serializer;
use Knp\Event\Exception\Store\NoResult;
use Knp\Event\Reflection;
use \PDO;

final class Store implements Event\Store, Event\Store\IsVersioned
{
    private $pdo;
    private $serializer;

    public function __construct(\PDO $pdo, Serializer $serializer)
    {
        $this->pdo = $pdo;
        $this->serializer = $serializer;
    }

    private function add(Event\Event $event)
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

    public function addSet(Event\Event\Set $events)
    {
        $this->pdo->beginTransaction();
        foreach ($events->all() as $event) {
            $this->add($event);
        }
        $this->pdo->commit();
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

        $hasFetched = false;
        while (false !== $row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $hasFetched = true;
            $event = $this->serializer->unserialize(json_decode($row['attributes'], true), $row['event_class']);
            $reflect = new Reflection($event);
            $reflect->setPropertyValue('emitterClass', $row['emitter_class']);
            $reflect->setPropertyValue('emitterId', $row['emitter_id']);

            yield $event;
        }

        if (!$hasFetched) {
            throw new NoResult;
        }
    }

    public function getCurrentVersion($class, $id)
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*)
            FROM event
            WHERE emitter_class = :class AND emitter_id = :id
        ;');
        $statement->bindValue('class', $class);
        $statement->bindValue('id', $id);
        $statement->execute();

        return $statement->fetchColumn();
    }
}

