<?php

namespace Knp\Event\Example\Shop;

require __DIR__.'/../vendor/autoload.php';

class Product implements \Knp\Event\AggregateRoot\CanBeReplayed, \Knp\Event\Provider
{
    use \Knp\Event\Popper;

    public $id;
    public $name;
    public $attributes;
    public $createdAt;

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
}

class Attribute
{
    public $id;
    public $name;
    public $value;

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
    public $currency;
    public $value;

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
