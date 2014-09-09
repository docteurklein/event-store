<?php

namespace spec\Knp\Event\Player;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Knp\Event\Emitter;
use Knp\Event\Event;

class ReflectionBasedSpec extends ObjectBehavior
{
    function it_is_a_player()
    {
        $this->shouldHaveType('Knp\Event\Player\ReflectionBased');
    }

    function it_reconstructs_current_state_of_an_object_by_replaying_its_past_events(Replayable $emitter, Event $isBorn, Event $hasLived, Event $isDead, \ArrayIterator $events)
    {
        $isBorn->getName()->willReturn('born');
        $isBorn->getAttributes()->willReturn(['id' => 10]);
        $hasLived->getName()->willReturn('to be');
        $hasLived->getAttributes()->willReturn([]);
        $isDead->getName()->willReturn('wild');
        $isDead->getAttributes()->willReturn([]);

        $events->iterates([$isBorn, $hasLived, $isDead]);

        $emitter = $this->replay($events, 'spec\Knp\Event\Player\Replayable');
        $emitter->getId()->shouldEqual(10);
    }
}

class Replayable implements Emitter
{
    use \Knp\Event\Popper;

    public function getId()
    {
        return $this->id;
    }

    public function getReplayableSteps()
    {
        return [
            'born' => 'born',
            'to be' => 'live',
            'wild' => 'dieFinally',
        ];
    }

    public function born($id)
    {
        $this->id = $id;
    }

    public function live()
    {
    }

    public function dieFinally()
    {
    }
}
