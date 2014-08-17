<?php

namespace Knp\Event;

use Knp\Event\Event;
use Knp\Event\Emitter\HasIdentity;

interface Event
{
    public function getName();

    public function setEmitter(HasIdentity $emitter);
    public function getEmitterClass();
    public function getEmitterId();

    public function getAttributes();
}
