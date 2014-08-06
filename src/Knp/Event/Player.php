<?php

namespace Knp\Event;

use Knp\Event\Event;

interface Player
{
    const CAN_BE_REPLAYED = 'Knp\Event\AggregateRoot\CanBeReplayed';

    public function replay(array $events, $class);
}
