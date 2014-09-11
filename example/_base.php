<?php

namespace Knp\Event\Example\Shop;

require __DIR__.'/../vendor/autoload.php';

\Symfony\Component\Debug\Debug::enable();
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

//$serializer = new \Knp\Event\Serializer\AnyCallable('igbinary_serialize', 'igbinary_unserialize');
$serializer = (new \Knp\Event\Serializer\Jms\Builder)->build();

class RDBMProjector implements \Knp\Event\Subscriber
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

$dispatcher = new \Knp\Event\Dispatcher;
$dispatcher->add(
    new RDBMProjector(
        new \PDO('pgsql:dbname=event_store_projection', null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_EMULATE_PREPARES => 0,
        ])
    )
);

//$store = new \Knp\Event\Store\Pdo\Store(
//    new \PDO('pgsql:dbname=event_store', null, null, [
//        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
//        \PDO::ATTR_EMULATE_PREPARES => 0,
//    ]),
//    $serializer
//);

//$store = new \Knp\Event\Store\InMemory;

$store = new \Knp\Event\Store\Mongo((new \MongoClient)->selectDB('event'), $serializer);

$repository = (new \Knp\Event\Repository\Factory(new \Knp\Event\Store\Concurrency\Optimistic($store), $dispatcher))->create();
