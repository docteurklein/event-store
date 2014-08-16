<?php

namespace spec\Knp\Event\Store;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Knp\Event\Event;
use Knp\Event\Emitter;
use Knp\Event\Exception\Store\NoResult;

final class InMemorySpec extends ObjectBehavior
{
    public function let(Event\Set $events, Event $event, Emitter $emitter)
    {
        $events->all()->willReturn([$event]);
        $events->getEmitter()->willReturn($emitter);
        $emitter->getId()->willReturn(1);
    }

    function it_is_a_store()
    {
        $this->shouldHaveType('Knp\Event\Store');
    }

    function it_stores_events($events)
    {
        $this->addSet($events);
    }

    function its_findBy_retrieves_emitter_specific_events($events, $emitter)
    {
        $this->addSet($events);
        $this->findBy('Double\Emitter\P8', 1)->shouldHaveType('Traversable'); // TODO find correct class name
    }

    function its_findBy_throws_no_result_if_empty()
    {
        $this->shouldThrow(new NoResult)->during('findBy', ['A\Test\FQCN', 1]);
    }
}
