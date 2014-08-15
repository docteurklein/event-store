<?php

namespace Knp\Event\Emitter;

interface CanBeReplayed
{
    /**
     * @return array list of methods to call indexed by event name
     **/
    public function getReplayableSteps();
}
