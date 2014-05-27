<?php

namespace Knp\Event\Example\Shop;

require __DIR__.'/../vendor/autoload.php';

class Product implements \Knp\Event\AggregateRoot\CanBeReplayed, \Knp\Event\Provider, \Serializable
{
    use \Knp\Event\Popper;

    private $id;
    private $name;
    private $attributes;
    private $createdAt;

    public function __construct($id, $name, array $attributes, \DateTime $createdAt = null)
    {
        $this->id = $id;
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
            'attributes' => $this->attributes,
            'createdAt' => $this->createdAt,
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
    private $id;
    private $name;
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

    public function __construct($id, array $items, \DateTime $createdAt = null)
    {
        $this->id = $id;
        $this->items = $items;
        $this->createdAt = $createdAt ?: new \DateTime;

        $this->events[] = new \Knp\Event\Event\Generic('CartCreated', [
            'id' => $this->id,
            'name' => $items,
            'attributes' => $attributes,
            'createdAt' => $this->createdAt,
        ]);

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

    private $id;
    private $cart;
    private $product;
    private $quantity;
    private $createdAt;

    public function __construct($id, Cart $cart, Product $product, $quantity, \DateTime $createdAt = null)
    {
        $this->id = $id;
        $this->cart = $cart;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->createdAt = $createdAt ?: new \DateTime;

        $this->events[] = new \Knp\Event\Event\Generic('ItemCreated', [
            'id' => $id,
            'cart' => $cart,
            'product' => $product,
            'quantity' => $quantity,
            'createdAt' => $this->createdAt,
        ]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }
}

class RDBMProjector implements \Doctrine\Common\EventSubscriber
{
    private $pdo;

    public function __construct(\PDO $pdo = null)
    {
        $this->pdo = $pdo ?: new \PDO('pgsql:dbname=event_store_projection');
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
$evm->addEventSubscriber(new RDBMProjector);

$repository = new \Knp\Event\Repository(
    new \Knp\Event\Store\Dispatcher(
        //new \Knp\Event\Store\InMemory,
        new \Knp\Event\Store\Rdbm(new \PDO('pgsql:dbname=event_store'), new \Knp\Event\Serializer\AnyCallable('igbinary_serialize', 'igbinary_unserialize')),
        //new \Knp\Event\Store\Mongo((new \MongoClient)->selectDB('event'), new \Knp\Event\Serializer\AnyCallable('igbinary_serialize', 'igbinary_unserialize')),
        $evm
    ),
    new \Knp\Event\Player
);
