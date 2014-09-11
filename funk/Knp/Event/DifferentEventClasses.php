<?php

namespace funk\Knp\Event;

use Knp\Event\Store;
use Knp\Event\Repository;
use Rhumsaa\Uuid\Uuid;
use Knp\Event\Event;
use JMS\Serializer\Annotation as Serialize;

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

class DifferentEventClasses implements \Funk\Spec
{
    function it_handles_different_event_classes()
    {
        $serializer = (new \Knp\Event\Serializer\Jms\Builder)->build();
        $stores = [
            new \Knp\Event\Store\InMemory,
            new \Knp\Event\Store\Pdo\Store(
                new \PDO('pgsql:dbname=event_store', null, null, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_EMULATE_PREPARES => 0,
                ]),
                $serializer
            ),
            new \Knp\Event\Store\Mongo((new \MongoClient)->selectDB('event'), $serializer),
        ];

        foreach ($stores as $store) {
            $this->test($store);
        }
    }

    private function test(Store $store)
    {
        $repository = (new Repository\Factory($store))->create();
        $product = new Product;
        $product->name('test');
        $product->name('no test');
        $repository->save($product);
        $re = $repository->find(Product::class, (string)$product->getId())->get();
        expect($product->getName())->toBeLike($re->getName());
    }
}

class Product implements \Knp\Event\Emitter
{
    use \Knp\Event\Popper;

    /**
     * @Serialize\Type("Rhumsaa\Uuid\Uuid")
     **/
    private $id;

    /**
     * @Serialize\Type("string")
     **/
    private $name;

    public function __construct(Uuid $id = null)
    {
        $this->id = $id ?: Uuid::uuid4();

        $this->emit(new ProductCreated($this->id));
    }

    public function name($name)
    {
        $this->name = $name;
        $this->emit(new ProductNamed($this->name));
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getReplayableSteps()
    {
        return [
            'ProductCreated' => '__construct',
            'ProductNamed' => 'name',
        ];
    }
}

class ProductCreated implements Event
{
    use \Knp\Event\Event\HandlesEmitter;

    /**
     * @Serialize\Type("Rhumsaa\Uuid\Uuid")
     **/
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return 'ProductCreated';
    }

    public function getAttributes()
    {
        return [$this->id];
    }
}

class ProductNamed implements Event
{
    use \Knp\Event\Event\HandlesEmitter;

    /**
     * @Serialize\Type("string")
     **/
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return 'ProductNamed';
    }

    public function getAttributes()
    {
        return [$this->name];
    }
}
