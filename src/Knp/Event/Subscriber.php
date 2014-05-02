<?php

namespace Knp\Event;

interface Subscriber
{
    /**
     * @return array an array of methods to call indexed by event name
     **/
    public function getSubscribedEvents();
}
