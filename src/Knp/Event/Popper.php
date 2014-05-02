<?php

namespace Knp\Event;

trait Popper
{
    private $events = [];

    public function popEvents()
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
