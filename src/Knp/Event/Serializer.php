<?php

namespace Knp\Event;

interface Serializer
{
    public function serialize(Event $event);

    public function unserialize($event, $class = 'Knp\Event\Event\Generic');
}
