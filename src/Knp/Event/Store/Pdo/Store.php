<?php

namespace Knp\Event\Store\Pdo;

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

    private function add(Event $event)
    {
        $statement = $this->pdo->prepare('INSERT INTO event ( name, emitter_class, emitter_id, attributes ) VALUES ( :name, :emitter_class, :emitter_id, :attributes );');
        $statement->bindValue('name', $event->getName());
        $statement->bindValue('emitter_class', $event->getEmitterClass());
        $statement->bindValue('emitter_id', $event->getEmitterId());
        $statement->bindValue('attributes', json_encode($this->serializer->serialize($event)));
        $statement->execute();
    }

    public function addSet(Event\Set $events)
    {
        $this->pdo->beginTransaction();
        foreach ($events->all() as $event) {
            $this->add($event);
        }
        $this->pdo->commit();
    }

    public function findBy($class, $id)
    {
        $statement = $this->pdo->prepare('SELECT name, emitter_class, emitter_id, attributes FROM event WHERE emitter_class = :class AND emitter_id = :id');
        $statement->bindValue('class', $class);
        $statement->bindValue('id', $id);
        $statement->execute();

        $hasFetched = false; // TODO argghh!
        while( false !== $row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $hasFetched = true;
            // TODO allow other event classes
            $event = new \Knp\Event\Event\Generic($row['name'], $this->serializer->unserialize(json_decode($row['attributes'], true))->getAttributes());
            $event->setEmitterClass($row['emitter_class']);
            $event->setEmitterId($row['emitter_id']);

            yield $event;
        }

        if (!$hasFetched) {
            throw new NoResult;
        }
    }
}

