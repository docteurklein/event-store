<?php

namespace Knp\Event\Example\Shop;

require __DIR__.'/../vendor/autoload.php';

\Symfony\Component\Debug\Debug::enable();

require '_model.php';
$serializer = require '_serializer.php';

class RDBMProjector implements \Doctrine\Common\EventSubscriber
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getSubscribedEvents()
    {
        return [
            'ProductCreated',
            'ProductRenamed',
        ];
    }

    public function ProductCreated(\Knp\Event\Event $event)
    {
        $statement = $this->pdo->prepare('INSERT INTO product ( id, name, created_at ) VALUES ( :id, :name, :created_at );');
        $statement->bindValue('id', $event->id);
        $statement->bindValue('name', $event->name);
        $statement->bindValue('created_at', $event->createdAt->format('Y-m-d'));
        $statement->execute();
    }

    public function ProductRenamed(\Knp\Event\Event $event)
    {
        $statement = $this->pdo->prepare('UPDATE product SET name = :name WHERE id = :id;');
        $statement->bindValue('id', $event->getEmitterId());
        $statement->bindValue('name', $event->name);
        $statement->execute();
    }
}

$evm = new \Doctrine\Common\EventManager;
$evm->addEventSubscriber(
    new RDBMProjector(
        new \PDO('pgsql:dbname=event_store_projection', null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_EMULATE_PREPARES => 0,
        ])
    )
);

$repository = new \Knp\Event\Repository(
    new \Knp\Event\Store\Dispatcher(
        //new \Knp\Event\Store\InMemory,
        //new \Knp\Event\Store\Pdo\Store(
        //    new \PDO('pgsql:dbname=event_store', null, null, [
        //        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        //        \PDO::ATTR_EMULATE_PREPARES => 0,
        //    ]),
        //    $serializer
        //),
        new \Knp\Event\Store\Mongo((new \MongoClient)->selectDB('event'), $serializer),
        $evm
    ),
    new \Knp\Event\Player\Aggregate(
        ['Knp\Event\Example\Shop\roduct' => new \Knp\Event\Player\ReflectionBased],
        new \Knp\Event\Player\ReflectionBased
    )
);
