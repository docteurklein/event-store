<?php

namespace Knp\Event\Store;

use Knp\Event\Store;
use Knp\Event\Event;
use \PDO;

class Rdbm implements Store
{
    private $pdo;

    public function __construct(\PDO $pdo = null)
    {
        $this->pdo = $pdo ?: new \PDO('pgsql:dbname=event_store');
    }

    public function add(Event $event)
    {
        $statement = $this->pdo->prepare('INSERT INTO event ( name, provider_class, provider_id, attributes ) VALUES ( :name, :provider_class, :provider_id, :attributes );');
        $statement->bindValue('name', $event->getName());
        $statement->bindValue('provider_class', $event->getProviderClass());
        $statement->bindValue('provider_id', $event->getProviderId());
        $statement->bindValue('attributes', base64_encode(serialize($event->getAttributes())));
        $statement->execute();
    }

    public function byProvider($class, $id)
    {
        $statement = $this->pdo->prepare('SELECT name, provider_class, provider_id, attributes FROM event WHERE provider_class = :class AND provider_id = :id');
        $statement->bindValue('class', $class);
        $statement->bindValue('id', $id);
        $statement->execute();

        return new \ArrayIterator($statement->fetchAll(PDO::FETCH_FUNC, function($name, $providerClass, $providerId, $attributes) {
            $event = new \Knp\Event\Event\Generic($name, unserialize(base64_decode($attributes)));
            $event->setProviderClass($providerClass);
            $event->setProviderId($providerId);

            return $event;
        }));
    }
}
