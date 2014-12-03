<?php

namespace Knp\Event\Event;

use Knp\Event\Emitter;
use Knp\Event\Event;

final class Set
{
    private $emitter;
    private $events;

    public function __construct(Emitter $emitter, array $events)
    {
        $this->emitter = $emitter;
        $this->events = $events;
    }

    public function all()
    {
        return $this->events;
    }

    public function getEmitter()
    {
        return $this->emitter;
    }
}
