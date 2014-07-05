<?php

namespace Knp\Event;

trait Popper
{
    /**
     * @Serialize\Exclude
     **/
    private $events = [];

    public function popEvents()
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
