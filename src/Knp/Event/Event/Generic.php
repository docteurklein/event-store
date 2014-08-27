<?php

namespace Knp\Event\Event;

use Knp\Event\Event;
use Knp\Event\Emitter\HasIdentity;
use Doctrine\Common\EventArgs;
use JMS\Serializer\Annotation as Serialize;

final class Generic extends EventArgs implements Event, \Serializable
{
    /**
     * @Serialize\Type("string")
     **/
    private $name;

    /**
     * @Serialize\Type("array")
     **/
    private $attributes;

    /**
     * @Serialize\Type("string")
     **/
    private $emitterClass;

    /**
     * @Serialize\Type("Rhumsaa\Uuid\Uuid")
     **/
    private $emitterId;

    public function __construct($name, array $attributes = [])
    {
        $this->name = $name;
        $this->attributes = $attributes;
    }

    public function setEmitter(HasIdentity $emitter)
    {
        $this->emitterClass = get_class($emitter);
        $this->emitterId = $emitter->getId();
    }

    public function getEmitterClass()
    {
        return $this->emitterClass;
    }

    public function getEmitterId()
    {
        return $this->emitterId;
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
