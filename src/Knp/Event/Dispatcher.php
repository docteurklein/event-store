<?php

namespace Knp\Event;

use Knp\Event\Subscriber;
use Knp\Event\Event;

final class Dispatcher
{
    private $listeners;

    public function add(Subscriber $subscriber)
    {
        foreach ($subscriber->getSubscribedEvents() as $event => $method) {
            $this->listeners[$event][] = [$subscriber, $method];
        }
    }

    public function dispatch(Event $event)
    {
        if (empty($this->listeners[$event->getName()])) {
            return;
        }
        foreach ($this->listeners[$event->getName()] as $listener) {
            call_user_func($listener, $event);
        }
    }
}
