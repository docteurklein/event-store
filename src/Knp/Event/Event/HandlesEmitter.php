<?php

namespace Knp\Event\Event;

use Knp\Event\Emitter\HasIdentity;

trait HandlesEmitter
{
    /**
     * @Serialize\Type("string")
     **/
    private $emitterClass;

    /**
     * @Serialize\Type("Rhumsaa\Uuid\Uuid")
     **/
    private $emitterId;

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

}
