<?php

namespace Knp\Event\Event;

use Knp\Event\Event;
use Knp\Event\Emitter\HasIdentity;
use JMS\Serializer\Annotation as Serialize;
use Serializable;

final class Generic implements Event, Serializable
{
    use HandlesEmitter;

    /**
     * @Serialize\Type("string")
     **/
    private $name;

    /**
     * @Serialize\Type("array")
     **/
    private $attributes;

    public function __construct($name, array $attributes = [])
    {
        $this->name = $name;
        $this->attributes = $attributes;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function __get($index)
    {
        return $this->attributes[$index];
    }

    public function serialize()
    {
        return serialize([
            $this->name,
            $this->attributes,
            $this->emitterClass,
            $this->emitterId,
        ]);
    }

    public function unserialize($data)
    {
        list(
            $this->name,
            $this->attributes,
            $this->emitterClass,
            $this->emitterId,
        ) = unserialize($data);
    }
}
