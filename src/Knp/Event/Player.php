<?php

namespace Knp\Event;

use Knp\Event\Emitter\CanBeReplayed;
use Traversable;

interface Player
{
    const CAN_BE_REPLAYED = CanBeReplayed::class;

    public function replay(Traversable $events, $class);
}
