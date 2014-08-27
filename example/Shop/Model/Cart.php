<?php

namespace example\Shop\Model;

use JMS\Serializer\Annotation as Serialize;

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
