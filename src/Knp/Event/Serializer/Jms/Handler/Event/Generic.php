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
            //[
            //    'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
            //    'format' => 'array',
            //    'type' => 'Knp\Event\Event\Generic',
            //    'method' => 'serialize',
            //],

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
        $attributes = [];
        foreach ($event->getAttributes() as $attribute) {
            $value = is_scalar($attribute) ? [
                '__value__' => $attribute,
                '__type__' => is_object($attribute) ? get_class($attribute) : gettype($attribute),
            ] : $attribute;
            $attributes[] = $visitor->getNavigator()->accept($value, null, $context);
        }

        $data = $visitor->getNavigator()->accept($event, null, $context);
        $data['attributes'] = $attributes;

        return $data;
    }

    public function deserialize(Visitor\ArrayDeserialize $visitor, $data, array $type, Context $context)
    {
        foreach ($data['attributes'] as &$attribute) {
            $type = ['name' => gettype($attribute), 'params' => []];
            $value = $attribute;
            if (isset($attribute['__type__'])) {
                $type = ['name' => $attribute['__type__'], 'params' => []];
                $value = isset($attribute['__value__']) ? $attribute['__value__'] : $attribute;
            }
            $attribute = $visitor->getNavigator()->accept($value, $type, $context);
        }

        $event = new Event\Generic($data['name'], $data['attributes']);
        $event->setProviderClass($data['provider_class']);
        $event->setProviderId($data['provider_id']);

        return $event;
    }
}
