<?php

namespace Knp\Event\Example\Shop;

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
use JMS\Serializer\Annotation as Serialize;

class Product implements \Knp\Event\Emitter, \Serializable
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

    public function __construct($id, $name, array $attributes = [], $createdAt = null)
    {
        $this->id = $id ?: \Rhumsaa\Uuid\Uuid::uuid4();
        $this->name = $name;
        $this->attributes = $attributes;
        $this->createdAt = $createdAt ?: new \DateTime;

        $this->emit(new \Knp\Event\Event\Generic('ProductCreated', [
            'id' => $this->id,
            'name' => $name,
            'attributes' => $attributes,
            'createdAt' => $this->createdAt,
        ]));
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
        $this->emit(new \Knp\Event\Event\Generic('ProductRenamed', [
            'name' => $name,
        ]));
    }

    public function addAttribute(Attribute $attribute)
    {
        $this->attributes[] = $attribute;
        $this->emit(new \Knp\Event\Event\Generic('AttributeAdded', [
            'attribute' => $attribute,
        ]));
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

class Cart implements \Knp\Event\Emitter
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

        $this->emit(new \Knp\Event\Event\Generic('CartCreated', [
            'id' => $this->id,
            'items' => $items,
            'createdAt' => $this->createdAt,
        ]));
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
        $this->emit(new \Knp\Event\Event\Generic('ItemAdded', [
            'item' => $item,
        ]));
    }
}

class Item
{
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
