<?php

namespace example\Shop\Model;

use JMS\Serializer\Annotation as Serialize;

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
