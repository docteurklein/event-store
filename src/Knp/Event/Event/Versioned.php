<?php

namespace Knp\Event\Event;

use Knp\Event\Event;
use Knp\Event\Emitter\HasIdentity;

class Versioned implements Event
{
    private $event;
    private $version;

    public function __construct(Event $event, $version)
    {
        $this->event = $event;
        $this->version = $version;
    }

    public function getName()
    {
        return $this->event->getName();
    }

    public function setEmitter(HasIdentity $emitter)
    {
        $this->event->setEmitter($emitter);
    }

    public function getEmitterClass()
    {
        return $this->event->getEmitterClass();
    }

    public function getEmitterId()
    {
        return $this->event->getEmitterId();
    }

    public function getAttributes()
    {
        return $this->event->getAttributes();
    }

    public function getVersion()
    {
        return $this->version;
    }
}
