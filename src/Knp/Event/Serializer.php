<?php

namespace Knp\Event;

use Knp\Event\Event;

interface Serializer
{
    public function serialize(Event $event);

    public function unserialize($event);
}
