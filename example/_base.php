<?php

namespace Knp\Event\Example\Shop;

require __DIR__.'/../vendor/autoload.php';

\Symfony\Component\Debug\Debug::enable();
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

use JMS\Serializer\Annotation as Serialize;

class Product implements \Knp\Event\AggregateRoot\CanBeReplayed, \Knp\Event\Provider, \Serializable
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

    /**
     * @Serialize\Type("array")
     **/
    private $attributes;

    /**
     * @Serialize\Type("DateTime")
     **/
    private $createdAt;

    public function __construct($id = null, $name = null, array $attributes = [], $createdAt = null)
    {
        $this->id = $id ?: \Rhumsaa\Uuid\Uuid::uuid4();
        $this->name = $name;
        $this->attributes = $attributes;
        $this->createdAt = $createdAt ?: new \DateTime;

        $this->events[] = new \Knp\Event\Event\Generic('ProductCreated', [
            'id' => $this->id,
            'name' => $name,
            'attributes' => $attributes,
            'createdAt' => $this->createdAt,
        ]);
    }

    public function __toString()
    {
        return sprintf('%s %s (%s) at %s', $this->name, $this->id, implode(', ', $this->attributes), $this->createdAt->format('Y-m-d H:i:s'));
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function rename($name)
    {
        $this->name = $name;
        $this->events[] = new \Knp\Event\Event\Generic('ProductRenamed', [
            'name' => $name,
        ]);
    }

    public function addAttribute(Attribute $attribute)
    {
        $this->attributes[] = $attribute;
        $this->events[] = new \Knp\Event\Event\Generic('AttributeAdded', [
            'attribute' => $attribute,
        ]);
    }

    public function getAttribute($name)
    {
        return current(array_filter($this->attributes, function($attribute) use($name) {
            return $attribute->name === $name;
        }));
    }

    public function getReplayableSteps()
    {
        return [
            'ProductCreated' => '__construct',
            'AttributeAdded' => 'addAttribute',
            'ProductRenamed' => 'rename',
        ];
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->name,
            $this->attributes,
            $this->createdAt,
        ]);
    }

    public function unserialize($data)
    {
        list(
            $this->id,
            $this->name,
            $this->attributes,
            $this->createdAt
        ) = unserialize($data);
    }
}

class Attribute
{
    /**
     * @Serialize\Type("Rhumsaa\Uuid\Uuid")
     **/
    private $id;

    /**
     * @Serialize\Type("string")
     **/
    private $name;

    /**
     * @Serialize\Type("string")
     **/
    private $value;

    public function __construct($id, $name, $value)
    {
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
    }

    public function __toString()
    {
        return sprintf('%s: %s', $this->name, $this->value);
    }
}

class Price
{
    private $currency;
    private $value;

    public function __construct($currency, $value)
    {
        $this->currency = $currency;
        $this->value = $value;
    }

    public function __toString()
    {
        return sprintf('%s %s', $this->currency, $this->value);
    }
}

class Cart implements \Knp\Event\AggregateRoot\CanBeReplayed, \Knp\Event\Provider
{
    use \Knp\Event\Popper;

    private $id;
    private $items;
    private $attributes;
    private $createdAt;

    public function __construct($id, array $items = [], \DateTime $createdAt = null)
    {
        $this->id = $id;
        $this->items = $items;
        $this->createdAt = $createdAt ?: new \DateTime;

        $this->events[] = new \Knp\Event\Event\Generic('CartCreated', [
            'id' => $this->id,
            'items' => $items,
            'createdAt' => $this->createdAt,
        ]);
    }

    public function __toString()
    {
        return sprintf('%s : %s', $this->id, implode(', ', $this->items));
    }

    public function getReplayableSteps()
    {
        return [
            'CartCreated' => '__construct',
            'ItemAdded' => 'addItem',
        ];
    }

    public function getId()
    {
        return $this->id;
    }

    public function addItem(Item $item)
    {
        $this->items[] = $item;
        $this->events[] = new \Knp\Event\Event\Generic('ItemAdded', [
            'item' => $item,
        ]);
    }
}

class Item implements \Knp\Event\Provider
{
    use \Knp\Event\Popper;

    /**
     * @Serialize\Type("Rhumsaa\Uuid\Uuid")
     **/
    private $id;

    /**
     * @Serialize\Type("Rhumsaa\Uuid\Uuid")
     **/
    private $productId;

    /**
     * @Serialize\Type("integer")
     **/
    private $quantity;

    /**
     * @Serialize\Type("DateTime")
     **/
    private $createdAt;

    public function __construct($id, $productId, $quantity, \DateTime $createdAt = null)
    {
        $this->id = $id;
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->createdAt = $createdAt ?: new \DateTime;

        $this->events[] = new \Knp\Event\Event\Generic('ItemCreated', [
            'id' => $id,
            'productId' => $productId,
            'quantity' => $quantity,
            'createdAt' => $this->createdAt,
        ]);
    }

    public function __toString()
    {
        return sprintf('%s x %s', $this->quantity, $this->productId);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getProductId()
    {
        return $this->productId;
    }
}

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
        $statement->bindValue('id', $event->getProviderId());
        $statement->bindValue('name', $event->name);
        $statement->execute();
    }
}

$evm = new \Doctrine\Common\EventManager;
$evm->addEventSubscriber(new RDBMProjector(new \PDO('pgsql:dbname=event_store')));

$serializer = new \Knp\Event\Serializer\Jms(
    (new \JMS\Serializer\SerializerBuilder)
        ->setSerializationVisitor('array', new \Knp\Event\Serializer\Jms\Visitor\ArraySerialize(
            new \JMS\Serializer\Naming\SerializedNameAnnotationStrategy(new \JMS\Serializer\Naming\CamelCaseNamingStrategy))
        )
        ->setDeSerializationVisitor('array', new \Knp\Event\Serializer\Jms\Visitor\ArrayDeserialize(
            new \JMS\Serializer\Naming\SerializedNameAnnotationStrategy(new \JMS\Serializer\Naming\CamelCaseNamingStrategy))
        )
        ->configureHandlers(function(\JMS\Serializer\Handler\HandlerRegistry $handlers) {
            $handlers->registerHandler('serialization', 'Rhumsaa\Uuid\Uuid', 'array', function($visitor, \Rhumsaa\Uuid\Uuid $id, array $type) {
                return (string) $id;
            });
            $handlers->registerHandler('deserialization', 'Rhumsaa\Uuid\Uuid', 'array', function($visitor, $id, array $type) {
                return \Rhumsaa\Uuid\Uuid::fromString($id);
            });
            $handlers->registerHandler('serialization', 'DateTime', 'array', function($visitor, \DateTime $date, array $type) {
                return $date->format(\DateTime::ISO8601);
            });
            $handlers->registerHandler('deserialization', 'DateTime', 'array', function($visitor, $date, array $type) {
                return \DateTime::createFromFormat(\DateTime::ISO8601, $date);
            });
            $handlers->registerSubscribingHandler(new \Knp\Event\Serializer\Jms\Handler\Event\Generic);
        })
        ->addDefaultHandlers()
        ->addDefaultListeners()
    ->build()
);

//$serializer = new \Knp\Event\Serializer\AnyCallable('igbinary_serialize', 'igbinary_unserialize');

$repository = new \Knp\Event\Repository(
    new \Knp\Event\Store\Dispatcher(
        //new \Knp\Event\Store\InMemory,
        //new \Knp\Event\Store\Rdbm(new \PDO('pgsql:dbname=event_store'), $serializer),
        new \Knp\Event\Store\Mongo((new \MongoClient)->selectDB('event'), $serializer),
        $evm
    ),
    new \Knp\Event\Player\Aggregate(
        ['Knp\Event\Example\Shop\roduct' => new \Knp\Event\Player\ReflectionBased],
        new \Knp\Event\Player\ReflectionBased
    )
);
