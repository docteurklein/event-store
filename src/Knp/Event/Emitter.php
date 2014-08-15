<?php

namespace Knp\Event;

interface Emitter extends Emitter\HasIdentity, Emitter\CanBeReplayed
{
    /**
     * empties and returns the list of events raised internally
     *
     * @return array
     **/
    public function popEvents();
}
