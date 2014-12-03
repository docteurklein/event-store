<?php

namespace Knp\Event;

use Knp\Event\Emitter\CanBeReplayed;
use Traversable;

interface Player
{
    const CAN_BE_REPLAYED = CanBeReplayed::class;

    /**
     * builds an object back from its events history.
a    *
     * @throws InvalidArgumentException
     **/
    public function replay(Traversable $events, $class);
}
