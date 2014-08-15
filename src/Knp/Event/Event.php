<?php

namespace Knp\Event;

use Knp\Event\Event;

interface Event
{
    public function getName();

    public function getEmitterClass();
    public function getEmitterId();

    public function getAttributes();
}
