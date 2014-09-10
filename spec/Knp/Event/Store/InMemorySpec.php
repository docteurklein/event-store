<?php

namespace spec\Knp\Event\Store;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Knp\Event\Event;
use Knp\Event\Emitter;
use Knp\Event\Exception\Store\NoResult;
use Knp\Event\Reflection;

final class InMemorySpec extends ObjectBehavior
{
    public function let(Event $event, Emitter $emitter, Reflection $reflection)
    {
        $this->beConstructedWith($reflection);
        $reflection->resolveClass(Argument::any())->willReturn('A\Test\FQCN');
        $emitter->getId()->willReturn(1);
    }

    function it_is_a_store()
    {
        $this->shouldHaveType('Knp\Event\Store');
    }

    function it_stores_events($emitter, $event)
    {
        $this->addSet(new Event\Set($emitter->getWrappedObject(), [$event->getWrappedObject()]));
    }

    function its_findBy_retrieves_emitter_specific_events($event, $emitter)
    {
        $this->addSet(new Event\Set($emitter->getWrappedObject(), [$event->getWrappedObject()]));
        $this->findBy('A\Test\FQCN', 1)->shouldHaveType('Traversable'); // TODO find correct class name
    }

    function its_findBy_throws_no_result_if_empty()
    {
        $this->shouldThrow(new NoResult)->during('findBy', ['A\Test\FQCN', 1]);
    }
}
