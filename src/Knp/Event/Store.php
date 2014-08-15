<?php

namespace Knp\Event;

use Knp\Event\Event;

interface Store
{
    public function addSet(Event\Set $events);

    public function findBy($class, $id);
}
