<?php

namespace spec\Knp\Event\Store;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Knp\Event\Event;

final class InMemorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Knp\Event\Store\InMemory');
    }

    function it_stores_events(Event $event)
    {
        $this->add($event);
    }
}
