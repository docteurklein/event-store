<?php

namespace Knp\Event\Serializer\Jms\Handler\Event;

use Knp\Event\Serializer\Jms\Visitor;
use Knp\Event\Event;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Context;
use Knp\Event\Reflection;

final class Generic implements SubscribingHandlerInterface
{
    private $reflect;

    public function __construct(Reflection $reflect = null)
    {
        $this->reflect = $reflect ?: new Reflection('Knp\Event\Event\Generic');
    }

    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'array',
                'type' => 'Knp\Event\Event\Generic',
                'method' => 'serialize',
            ],

            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'array',
                'type' => 'Knp\Event\Event\Generic',
                'method' => 'deserialize',
            ],
        ];
    }

    public function serialize(Visitor\ArraySerialize $visitor, Event\Generic $event, array $type, Context $context)
    {
        $data = [
            'name' => $event->getName(),
            'emitter_class' => $event->getEmitterClass(),
            'emitter_id' => $visitor->getNavigator()->accept($event->getEmitterId(), null, $context),
            'attributes' => array_map($closure = function($attribute) use(&$closure, $visitor, $context) {
                if (is_array($attribute)) {
                    return [
                        '__type__' => 'array',
                        '__value__' => array_map($closure, $attribute),
                    ];
                }
                return [
                    '__type__' => is_object($attribute) ? get_class($attribute) : gettype($attribute),
                    '__value__' => $visitor->getNavigator()->accept($attribute, null, $context),
                ];
            }, $event->getAttributes()),
        ];
        $visitor->setRoot($data);

        return $data;
    }

    public function deserialize(Visitor\ArrayDeserialize $visitor, $data, array $type, Context $context)
    {
        $attributes = array_map($closure = function($attribute) use(&$closure, $visitor, $context) {
            if (isset($attribute['__type__']) && 'array' === $attribute['__type__']) {
                return array_map($closure, $attribute['__value__']);
            }
            return $visitor->getNavigator()->accept($attribute['__value__'], [ 'name' => $attribute['__type__'], 'params' =>[] ], $context);
        }, $data['attributes']);
        $event = new Event\Generic($data['name'], $attributes);
        $reflect = new Reflection($event);
        $reflect->setPropertyValue('emitterClass', $data['emitter_class']);
        $reflect->setPropertyValue('emitterId', $visitor->getNavigator()->accept($data['emitter_id'], ['name' => 'Rhumsaa\Uuid\Uuid', 'params' =>[] ], $context));

        return $event;
    }
}
