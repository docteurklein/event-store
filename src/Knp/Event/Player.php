<?php

namespace Knp\Event;

use Knp\Event\Event;

class Player
{
    const CAN_BE_REPLAYED = 'Knp\Event\AggregateRoot\CanBeReplayed';

    public function replay(array $events, $class)
    {
        $reflect = new \ReflectionClass($class);

        if (!$reflect->implementsInterface(self::CAN_BE_REPLAYED)) {
            throw new \InvalidArgumentException(sprintf('"%s" must implement "%s"', $class, self::CAN_BE_REPLAYED));
        }

        $object = $reflect->newInstanceWithoutConstructor();
        $methods = $object->getReplayableSteps();
        die(var_dump($events));
        foreach ($events as $event) {
            if (isset($methods[$event->getName()])) {
                $method = $methods[$event->getName()];
                if ($method === '__construct') {
                    $object = $reflect->newInstanceArgs($event->getAttributes());
                }
                $reflect->getMethod($method)->invokeArgs($object, $event->getAttributes());
            }
        }
        $object->popEvents();

        return $object;
    }
}
