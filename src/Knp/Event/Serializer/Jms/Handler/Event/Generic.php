<?php

namespace Knp\Event\Serializer\Jms\Handler\Event;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use Knp\Event\Serializer\Jms\Visitor;
use Knp\Event\Event;
use JMS\Serializer\Context;

class Generic implements SubscribingHandlerInterface
{
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
            'provider_class' => $event->getProviderClass(),
            'provider_id' => $visitor->getNavigator()->accept($event->getProviderId(), null, $context),
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
        $event->setProviderClass($data['provider_class']);
        $event->setProviderId($visitor->getNavigator()->accept($data['provider_id'], ['name' => 'Rhumsaa\Uuid\Uuid', 'params' =>[] ], $context));

        return $event;
    }
}
