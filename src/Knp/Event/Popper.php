<?php

namespace Knp\Event;

use JMS\Serializer\Annotation as Serialize;
use Knp\Event\Event;

trait Popper
{
    /**
     * @Serialize\Exclude
     **/
    private $events = [];

    /**
     * @return Event\Set the set of events emitted since last pop
     **/
    public function popEvents()
    {
        $events = $this->events;
        $this->events = [];

        return new Event\Set($this, $events);
    }

    public function emit(Event $event)
    {
        $event->setEmitter($this);
        $this->events[] = $event;
    }
}
