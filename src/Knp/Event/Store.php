<?php

namespace Knp\Event;

interface Store
{
    public function addSet(Event\Set $events);

    public function findBy($class, $id);
}
