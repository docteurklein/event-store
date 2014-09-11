<?php

namespace example\Shop\Model;

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

    public function __construct($id = null, $name = null, array $attributes = [], $createdAt = null)
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
