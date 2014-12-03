<?php

namespace spec\Knp\Event\Store\Concurrency;

use PhpSpec\ObjectBehavior;
use Knp\Event\Store;
use Prophecy\Prophet;
use Knp\Event\Event;
use Knp\Event\Emitter;
use Knp\Event\Store\IsVersioned;
use Knp\Event\Store\Concurrency\Optimistic\VersionTransporter;
use Prophecy\Argument;

class OptimisticSpec extends ObjectBehavior
{
    function let(IsVersioned $store, VersionTransporter $versionTransporter)
    {
        $this->beConstructedWith($store, $versionTransporter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Knp\Event\Store\Concurrency\Optimistic');
    }

    function it_allows_same_version($store, $versionTransporter, Event $event, Emitter $emitter)
    {
        $store->getCurrentVersion(Argument::cetera())->willReturn(1);
        $store->addSet(Argument::any())->shouldBeCalled();
        $versionTransporter->getExpectedVersion(Argument::cetera())->willReturn(1);
        $versionTransporter->update(Argument::cetera())->shouldBeCalled(1);
        $this->addSet(new Event\Set($emitter->getWrappedObject(), [$event->getWrappedObject()]));
    }

    function it_refuses_conflictual_sets($store, $versionTransporter, Event $event, Event $conflictual, Emitter $emitter)
    {
        $store->getCurrentVersion(Argument::cetera())->willReturn(3);
        $versionTransporter->getExpectedVersion(Argument::cetera())->willReturn(1);

        $this->shouldThrow('Knp\Event\Exception\Concurrency\Optimistic\Conflict', 'addSet', [new Event\Set($emitter->getWrappedObject(), [
            $event->getWrappedObject(),
            $conflictual->getWrappedObject(),
        ])]);
    }
}
