<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;
use \PDO;
use Knp\Event\Serializer;

class Rdbm implements Store
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
        $statement = $this->pdo->prepare('INSERT INTO event ( name, provider_class, provider_id, attributes ) VALUES ( :name, :provider_class, :provider_id, :attributes );');
        $statement->bindValue('name', $event->getName());
        $statement->bindValue('provider_class', $event->getProviderClass());
        $statement->bindValue('provider_id', $event->getProviderId());
        $statement->bindValue('attributes', $this->serializer->serialize($event), PDO::PARAM_LOB);
        $statement->execute();
    }

    public function byProvider($class, $id)
    {
        $statement = $this->pdo->prepare('SELECT name, provider_class, provider_id, attributes FROM event WHERE provider_class = :class AND provider_id = :id');
        $statement->bindValue('class', $class);
        $statement->bindValue('id', $id);
        $statement->execute();

        while( false !== $row = $statement->fetch(PDO::FETCH_ASSOC)) {
            // TODO allow other event classes
            $event = new \Knp\Event\Event\Generic($row['name'], $this->serializer->unserialize(stream_get_contents($row['attributes']))->getAttributes());
            $event->setProviderClass($row['provider_class']);
            $event->setProviderId($row['provider_id']);

            yield $event;
        }
    }
}
