<?php

namespace Knp\Event;

interface Store
{
    public function addSet(Event\Set $events);

    /**
     * @throws Knp\Event\Exception\Store\NoResult
     **/
    public function findBy($class, $id);
}
