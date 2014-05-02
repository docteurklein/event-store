<?php

namespace Knp\Event\AggregateRoot;

interface CanBeReplayed
{
    /**
     * @return array list of methods to call indexed by event name
     **/
    public function getReplayableSteps();
}
