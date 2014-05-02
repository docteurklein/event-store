<?php

namespace Knp\Event;

interface Provider
{
    /**
     * empties and returns the list of events raised internally
     *
     * @return array
     **/
    public function popEvents();

    public function getId();
}
