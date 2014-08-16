<?php

namespace spec\Knp\Event\Store;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Knp\Event\Serializer;
use Knp\Event\Event;
use Knp\Event\Emitter;
use MongoDB;
use MongoCollection;
use MongoCursor;
use Knp\Event\Exception\Store\NoResult;

class MongoSpec extends ObjectBehavior
{
    function let(MongoDB $db, MongoCollection $collection, MongoCursor $cursor, Serializer $serializer, Emitter $emitter, Event $event)
    {
        $this->beConstructedWith($db, $serializer);
        $this->shouldHaveType('Knp\Event\Store');
        $db->selectCollection(Argument::type('string'))->willReturn($collection);
        $emitter->getId()->willReturn(1);
        $collection->find(['emitter_id' => '1'])->willReturn($cursor);
        $serializer->unserialize(Argument::any())->willReturn($event);
        $serializer->serialize(Argument::type('Knp\Event\Event'))->willReturn();
    }

    function it_stores_events(Event\Set $events, $event, $emitter, $collection)
    {
        $collection->batchInsert(Argument::type('array'))->shouldBeCalled();
        $events->getEmitter()->willReturn($emitter);
        $events->all()->willReturn([$event]);
        $this->addSet($events);
    }

    function its_findBy_retrieves_emitter_specific_events($cursor, $event)
    {
        $cursor->rewind()->willReturn();
        $cursor->count()->willReturn(1);
        $cursor->valid()->willReturn(true, false);
        $cursor->next()->willReturn();
        $cursor->current()->willReturn($event);
        $events = $this->findBy('A\Test\FQCN', 1);
        $events->shouldHaveType('Traversable');
    }

    function its_findBy_throws_no_result_if_empty($cursor, $event)
    {
        $cursor->rewind()->willReturn();
        $cursor->count()->willReturn(0);
        $cursor->valid()->willReturn(false);
        $events = $this->shouldThrow(new NoResult)->during('findBy', ['A\Test\FQCN', 1]);
    }
}
